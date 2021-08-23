<?php

/**
 * @category Class
 * @package  App/Core/Auth
 * @author   Marry Go Round <million8.me@gmail.com>
 * @license  https://opensource.org/licenses/MIT - MIT License 
 * @link     https://github.com/ZheeknoDev/aspra
 */

namespace App\Core\Auth;

use App\Core\Config;
use App\Core\Database\DB;

final class Encryption
{
    private const CIPHER = 'aes-256-cbc';
    private $key;
    private $user_group;
    private $user_id;

    public function __construct(int $userId, string $userGroup)
    {
        $this->key = Config::App('app_key');
        $this->user_group = $userGroup;
        $this->user_id = $userId;
    }

    final public function __debugInfo()
    {
        return;
    }

    /**
     * step to compare a secret string
     * @param string $string
     * @return bool
     */
    private function compare_string(string $string): bool
    {
        $lists = $this->list_of_string();
        $compare = array_filter($lists, function ($value) use ($string) {
            return $value == base64_decode(hex2bin($string));
        });
        return (count($compare) > 0) ? true : false;
    }

    /**
     * step to decryption the data
     * @param string $data
     * @param string $key
     * @param string $iv
     * @return string
     */
    private function decrypt_data(string $data, string $key, string $iv) : string
    {
        $encrypt_data = base64_decode(hex2bin($data));
        $encrypt_key = base64_decode($key);
        $iv = hex2bin(base64_decode($iv));
        $decrypted_data = openssl_decrypt($encrypt_data, self::CIPHER, $encrypt_key, 0, $iv);
        return $decrypted_data;
    }

    /**
     * step to decrpytion the iv
     * @param string $data
     * @param string $key
     * @return string
     */
    private function decrypt_iv(string $data, string $key) : string
    {
        list($encrypt_data, $encrypt_iv) = explode('$', $data, 2);
        $iv = hex2bin(base64_decode($encrypt_iv));
        $encrypt_key = base64_decode($key);
        $decrypted_string = openssl_decrypt($encrypt_data, self::CIPHER, $encrypt_key, 0, $iv);
        return $decrypted_string;
    }

    /**
     * step to encryption the data
     * @param string $data
     * @param string $key
     * @return array
     */
    private function encrypt_data(string $data, string $key) : array
    {
        # encrypt the data
        $encrypt_key = base64_decode($key);
        $iv = $this->iv();
        $encrypted_data = openssl_encrypt($data, self::CIPHER, $encrypt_key, 0, $iv);
        $result = bin2hex(base64_encode($encrypted_data));

        # encrypt the iv
        $encrypted_iv = $this->encrypt_iv($iv, $this->iv_clent_token_key($this->user_group));
        return ['token' => $result, 'remember' => $encrypted_iv];
    }

    /**
     * step to encryption the iv
     * @param string $data
     * @param string $key
     * @return string
     */
    private function encrypt_iv(string $data, string $key) : string
    {
        $encrypt_data = base64_encode(bin2hex($data));
        $encrypt_key = base64_decode($key);
        $iv = $this->iv();
        $encrypt_iv = base64_encode(bin2hex($iv));
        $encrypted_string = openssl_encrypt($encrypt_data, self::CIPHER, $encrypt_key, 0, $iv);
        return (string) implode('$', [$encrypted_string, $encrypt_iv]);
    }

    /**
     * static functio to return a token
     * @param int $userId
     * @param string $userGroup
     * @return array
     */
    final public static function generateToken(int $userId, string $userGroup)
    {
        $class = new self($userId, $userGroup);
        return $class->encrypt_data($class->random_string(), $class->key());
    }

    /**
     * generate the iv
     * @return string
     */
    private function iv() : string
    {
        return openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::CIPHER));
    }

    /**
     * get the iv of user's group
     * @return string
     */
    private function iv_clent_token_key(string $userGroup) : string
    {
        if (!empty($userGroup)) {
            $client_token = DB::table('client_tokens')
                ->select('somewords')
                ->where('group', '=', strtolower($userGroup))
                ->first();
            return (!empty($client_token->somewords)) ? $client_token->somewords : null;
        }
        return null;
    }

    /**
     * generate the secret key
     * @return string
     */
    private function key() : string
    {
        return (!empty($this->key)) ? $this->key : null;
    }

    /**
     * get list of the secreat string
     * @return array
     */
    private function list_of_string(): array
    {
        $lists = array();
        $files = file(BASEPATH . '/config/private.key');
        if (!empty($files)) {
            foreach ($files as $line) {
                $lists[] = (string) trim($line, "\n");
            }
        }
        return $lists;
    }

    /**
     * get the randon of secret string
     * @return string
     */
    private function random_string() : string
    {
        $lists = $this->list_of_string();
        $max = (count($lists) - 1);
        $n = rand(0, $max);
        return bin2hex(base64_encode($lists[$n]));
    }

    /**
     * verify a token that attached with the request
     * @param array $argument
     * @param string $userGroup
     * @return bool
     */
    final public static function reverseToken(array $arguments, string $userGroup) : bool
    {
        $arrayKeyExists = array_filter(array_keys($arguments), function ($key) {
            return in_array($key, ['userId', 'userRemember', 'token']);
        });

        if (count($arrayKeyExists) == 3) {
            $class = new self($arguments['userId'], $userGroup);
            $iv = $class->decrypt_iv($arguments['userRemember'], $class->iv_clent_token_key($class->user_group));
            if (!empty($iv)) {
                $decryptedToken = $class->decrypt_data($arguments['token'], $class->key(), $iv);
                return $class->compare_string($decryptedToken);
            }
        }
        return false;
    }
}
