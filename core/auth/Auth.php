<?php

namespace App\Core\Auth;

use App\Core\Config;
use App\Core\Database\DB;
use App\Core\Router\Request;
use App\Core\Router\Response;
use Exception;
use PDOException;

final class Auth
{
    private static $_authenticated;
    private static $_instance;
    private static $_request;
    private static $_user_group;

    public function __construct(Request $request, string $userGroupName = null)
    {
        self::$_request = $request;
        if (!empty($userGroupName)) {
            self::$_user_group = $this->getUserGroup($userGroupName);
        }
    }

    public static function __callStatic($name, $arguments)
    {
        $groupName = strtolower($name);
        if ($groupName == self::$_user_group) {
            return new static(self::$_request, self::$_user_group);
        }
    }

    final public function __get($name)
    {
        return self::$_instance[$name];
    }

    final public function access(string $username, string $password)
    {
        if (!empty(self::$_authenticated) && self::$_authenticated == session_id()) {
            return true;
        }

        /**
         * Closure : update user's verified date
         * @param int $userId
         * @return void
         */
        $updateUserVerifiedDate = function (int $userId) {
            try {
                DB::table('users')
                    ->where('id', '=', $userId)
                    ->update(['verified_at' => date('Y-m-d H:i:s')])
                    ->run();
            } catch (\PDOException $e) {
                throw  new PDOException($e->getMessage());
            }
        };

        $user = DB::table('users')
            ->where('username', '=', $username)
            ->first();
        if (!empty($user->password) && (password_verify($password, $user->password))) {
            # if login at first time.
            if ($user->verified_at == null) {
                $updateUserVerifiedDate($user->id);
            }
            # hide some column of users
            $user->userId = $user->id;
            unset($user->id, $user->password, $user->remember);
            self::$_authenticated = session_id();
            self::$_instance = (array) $user;
            return true;
        }
        return false;
    }

    final public function getToken()
    {
        if (self::$_authenticated == session_id()) {
            $userToken = DB::table('user_tokens')
                ->where('user_id', '=', $this->userId)
                ->first();
            $getUserToken = Encryption::generateToken($this->userId, self::$_user_group);
            $expire_at = date('Y-m-d H:i:s', strtotime('+30 days'));

            if (count($getUserToken) == 2) {
                if (!empty($userToken)) {
                    try {
                        # update user's token
                        DB::table('user_tokens')
                            ->where('id', '=', $userToken->id)
                            ->update([
                                'token' => $getUserToken['token'],
                                'revoked' => ($userToken->revoked + 1),
                                'expire_at' => $expire_at
                            ])->run();
                    } catch (\PDOException $e) {
                        throw new PDOException($e->getMessage());
                    }
                } else {
                    # get client's token ID
                    $clientToken = DB::table('client_tokens')
                        ->select('id')
                        ->where('group', '=', self::$_user_group)
                        ->first();
                    # Has the client's token ID ?
                    if (!empty($getUserToken) && !empty($clientToken)) {
                        try {
                            # insert new user's token
                            DB::table('user_tokens')
                                ->insert([
                                    'token' => $getUserToken['token'],
                                    'user_id' => $this->userId,
                                    'client_id' => $clientToken->id,
                                    'expire_at' => $expire_at
                                ])->run();
                        } catch (\PDOException $e) {
                            throw new PDOException($e->getMessage());
                        }
                    }
                }

                try {
                    # update user's remember field
                    DB::table('users')
                        ->where('id', '=', $this->userId)
                        ->update(['remember' => $getUserToken['remember']])
                        ->run();
                } catch (\PDOException $e) {
                    throw new PDOException($e->getMessage());
                }
            }

            return [
                'token' => $getUserToken['token'],
                'expire_at' => $expire_at
            ];
        }
        return null;
    }

    private function getUserGroup(string $userGroupName = null)
    {
        if (!empty($userGroupName)) {
            $userGroup = strtolower($userGroupName);
            $client_token = DB::table('client_tokens')
                ->select('id')
                ->where('group', '=', $userGroup)
                ->first();
            return (!empty($client_token->id)) ? $userGroup : null;
        }
        return null;
    }

    final public static function hash_password(string $password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    final public function requestApi(): bool
    {
        if (!empty(self::$_request->hasAuthorized('bearer'))) {
            return $this->verifyToken(self::$_request->hasAuthorized('bearer'));
        }
        return false;
    }

    final public static function via(string $userGroupName = null)
    {
        return new self(new \App\Core\Router\Request, $userGroupName);
    }

    private function verifyToken(string $token): bool
    {
        $userToken = DB::table('user_tokens')
            ->where('token', '=', $token)
            ->first();
        if (!empty($userToken->id)) {
            # if a token has expired
            if (strtotime($userToken->expire_at) < strtotime('now')) {
                return Response::instance()->json_form_response("Your token has expired", false, 401);
            }

            # find user's details
            $user = DB::table('users')->find($userToken->user_id);
            # find user's group
            $clientToken = DB::table('client_tokens')->find($userToken->client_id);

            if(!empty($user->id) && !empty($clientToken->id)) {
                # set user's group
                $userGroup = $this->getUserGroup($clientToken->group); 

                # set user's details
                $user->userId = $user->id;
                $userRemember = $user->remember;
                unset($user->id, $user->password, $user->remember);
                
                # has authorized or not ?
                $reverseToken = Encryption::reverseToken([
                    'userId' => $user->userId,
                    'userRemember' => $userRemember,
                    'token' => $userToken->token,
                ], $userGroup);
                if($reverseToken) {
                    self::$_user_group = $userGroup;
                    self::$_instance = (array) $user;
                    self::$_authenticated = session_id();
                    return true;
                }
            }
        }
        return false;
    }
}
