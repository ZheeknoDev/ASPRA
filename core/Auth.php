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
use Zheeknodev\Roma\BasicAuth;
use Zheeknodev\Roma\Router\Response;

final class Auth
{
    public function __construct()
    {
        $app = new Application();
        BasicAuth::setup($app->authorization());
    }

    final public static function check_password(string $password, string $password_hash): bool
    {
        new self();
        return BasicAuth::via()->get_password_verify($password, $password_hash);
    }

    private static function client_token(string $group): object
    {
        $clientToken = DB::table('client_tokens')
            ->where('group', '=', strtolower($group))
            ->first();
        return (!empty($clientToken->id) ? $clientToken : false);
    }

    final public static function getUserApiToken(int $user_id, int $expire_days = null, string $group = null)
    {
        new self();
        $expire_days = (string) "+" . (!empty($expire_days) ? $expire_days : 30) . " days";
        $expire_at = date('Y-m-d H:i:s', strtotime($expire_days));
        $group = (!empty($group) ? strtolower($group) : 'users');
        $clientToken = self::client_token($group);
        $result = BasicAuth::instance()->getApiToken($group);
        $userToken = Token::where('user_id', '=', $user_id)->first();

        if ($clientToken !== false && $result !== false) {
            # expire date
            $data = array();
            $data['expire_at'] = $expire_at;
            $data['token'] = $result->check_hash;
            if (!empty($userToken->id)) {
                # update user's token
                $revoked = $userToken->revoked;
                $data['revoked'] = ($revoked > 0) ? ($revoked + 1) : 1;
                $update = Token::where('user_id', '=', $user_id)
                    ->update($data)
                    ->run();
                # update revoke token
                self::revoke_token($group);
            } else {
                # create user's token
                $date['user_id'] = $user_id;
                $data['client_at'] = $clientToken->id;
                Token::insert($data)->run();
            }
            return (object) [
                'status' => true,
                'token' => $result->token,
                'expire_at' => $expire_at
            ];
        }

        return (object) [
            'status' => false,
            'errors' => 'Unable to generate the API\'s token.'
        ];
    }

    final public static function hash_password(string $password): string
    {
        new self();
        return BasicAuth::via()->get_password_hash($password);
    }


    private static function revoke_token(string $group): void
    {
        $clientToken = self::client_token($group);
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
