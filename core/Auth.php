<?php

/**
 * @category Class
 * @package  zheeknodev/aspra
 * @author   ZheeknoDev <million8.me@gmail.com>
 * @license  https://opensource.org/licenses/MIT - MIT License 
 * @link     https://github.com/ZheeknoDev/Aspra
 */

namespace App\Core;

use App\Core\Database\DB;
use App\Model\Token;
use App\Model\User;
use DateTime;
use Zheeknodev\Roma\BasicAuth;
use Zheeknodev\Roma\Router\Response;

final class Auth
{
    private const SAPARATOR = '$';
    private static $_request;
    private static $_userInfo;

    public function __construct()
    {
        $app = new Application();
        BasicAuth::setup($app->credentials());
        self::$_request = $app->router()->request();
    }

    final public static function check_password(string $password, string $password_hash): bool
    {
        new self();
        return BasicAuth::sipher()->get_password_verify($password, $password_hash);
    }

    final public static function getUserInfo() //: object
    {
        if (!empty(self::$_userInfo)) {
            $userInfo = self::$_userInfo;
            unset($userInfo->password, $userInfo->remember, $userInfo->created_at, $userInfo->updated_at, $userInfo->verified_at);
            return $userInfo;
        }
        return null;
    }

    private static function getClientToken(string $group): object
    {
        $clientToken = DB::table('client_tokens')
            ->where('group', '=', strtolower($group))
            ->first();
        return (!empty($clientToken->id) ? $clientToken : false);
    }

    final public static function getUserApiToken(int $user_id, int $expire_days = null, string $group = null): object
    {
        new self();
        $string_date = (string) "+" . (!empty($expire_days) ? $expire_days : 30) . " days";
        $expire_days = date('Y-m-d H:i:s', strtotime($string_date));
        $group = (!empty($group) ? strtolower($group) : 'users');
        $clientToken = self::getClientToken($group);
        $result = BasicAuth::instance()->getApiToken($group);
        $user = User::where('id', '=', $user_id)->first();
        $userToken = Token::where('user_id', '=', $user_id)->first();

        if ($clientToken !== false && $result !== false && !empty($user->id)) {
            # expire date
            $data = array();
            $expire_at = $data['expire_at'] = $expire_days;
            $data['token'] = $result->check_hash;
            if (!empty($userToken->id)) {
                # update user's token
                $revoked = $userToken->revoked;
                $data['revoked'] = ($revoked > 0) ? ($revoked + 1) : 1;
                $update = Token::where('user_id', '=', $user->id)
                    ->update($data)
                    ->run();
                # update revoke token
                self::updateRovokedToken($group);
            } else {
                # create user's token
                $date['user_id'] = $user->id;
                $data['client_at'] = $clientToken->id;
                Token::insert($data)->run();
            }

            return (object) [
                'status' => true,
                'token' => base64_encode(implode(self::SAPARATOR, [$result->token, bin2hex($user->remember)])),
                'expire_at' => $expire_at
            ];
        }

        return (object) [
            'status' => false,
            'errors' => 'Unable to generate the API\'s token.'
        ];
    }

    final public static function hasAuthorized(string $typeOfAuth): bool
    {
        # closure - validate token's expire date
        $hasExpired = function (string $expire) {
            $expire_at = new DateTime($expire);
            $today = new DateTime('now');
            $remain = $today->diff($expire_at);
            return ($remain->days == 0) ? true : false;
        };

        new self();
        $request = self::$_request;
        $hasAuthorized = $request->hasAuthorized($typeOfAuth);
        $token = $request->getAuthorizedToken();
        if ($hasAuthorized && !empty($token)) {
            $token = base64_decode($token);
            $token = explode(self::SAPARATOR, $token);
            if (count($token) == 2) {
                $user = User::where('remember', '=', hex2bin($token[1]))->first();
                if (!empty($user->id)) {
                    $userToken = Token::where('user_id', '=', $user->id)->first();
                    if (!empty($userToken->id) && !empty($userToken->token)) {

                        # when the token is expire
                        if ($hasExpired($userToken->expire_at)) {
                            $response = Response::instance();
                            return $response->failUnauthorized([
                                'message' => $response->getResponseMessage(401) . ", Your token ware expired, please renew your token."
                            ]);
                        }

                        $clientToken = DB::table('client_tokens')->run();
                        foreach ($clientToken as $client) {
                            $data = [
                                'authorized' => $typeOfAuth,
                                'group' => $client->group,
                                'token' => $token[0],
                                'check_hash' => $userToken->token
                            ];
                            # if verify success
                            $verifyApiToken = BasicAuth::instance()->verifyApiToken($data);
                            if ($verifyApiToken) {
                                # user's authorization information
                                $user->group = $client->group;
                                self::$_userInfo = $user;
                                return true;
                            }
                        }
                    }
                }
            }
        }
        return false;
    }

    final public static function hash_password(string $password): string
    {
        new self();
        return BasicAuth::sipher()->get_password_hash($password);
    }

    private static function updateRovokedToken(string $group): void
    {
        $clientToken = self::getClientToken($group);
        if ($clientToken !== false) {
            $revoked = ($clientToken->revoked + 1);
            try {
                DB::table('client_tokens')
                    ->where('id', '=', $clientToken->id)
                    ->update(['revoked' => $revoked])
                    ->run();
            } catch (\Exception $e) {
                # log
                die($e->getMessage());
            }
        }
    }
}
