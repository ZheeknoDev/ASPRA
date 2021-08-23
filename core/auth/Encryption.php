<?php

namespace App\Core\Auth;

use App\Core\Config;
use App\Core\Database\DB;
use PDOException;

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

    private function compare_string(string $string) : bool
    {
        $lists = $this->list_of_string();
        $compare = array_filter($lists, function($value) use ($string){
            return $value == base64_decode(hex2bin($string));
        });
        return (count($compare) > 0) ? true : false;
    }

    private function decrypt_data(string $data, string $key, string $iv)
    {
        $encrypt_data = base64_decode(hex2bin($data));
        $encrypt_key = base64_decode($key);
        $iv = hex2bin(base64_decode($iv));
        $decrypted_data = openssl_decrypt($encrypt_data, self::CIPHER, $encrypt_key, 0, $iv);
        return $decrypted_data;
    }

    private function decrypt_iv(string $data, string $key)
    {
        list($encrypt_data, $encrypt_iv) = explode('$', $data, 2);
        $iv = hex2bin(base64_decode($encrypt_iv));
        $encrypt_key = base64_decode($key);
        $decrypted_string = openssl_decrypt($encrypt_data, self::CIPHER, $encrypt_key, 0, $iv);
        return $decrypted_string;
    }

    private function encrypt_data(string $data, string $key)
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

    private function encrypt_iv(string $data, string $key)
    {
        $encrypt_data = base64_encode(bin2hex($data));
        $encrypt_key = base64_decode($key);
        $iv = $this->iv();
        $encrypt_iv = base64_encode(bin2hex($iv));
        $encrypted_string = openssl_encrypt($encrypt_data, self::CIPHER, $encrypt_key, 0, $iv);
        return (string) implode('$', [$encrypted_string, $encrypt_iv]);
    }

    final public static function generateToken(int $userId, string $userGroup)
    {
        $class = new self($userId, $userGroup);
        return $class->encrypt_data($class->random_string(), $class->key());
    }

    private function iv()
    {
        return openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::CIPHER));
    }

    private function iv_clent_token_key(string $userGroup)
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

    private function key()
    {
        return (!empty($this->key)) ? $this->key : null;
    }

    private function list_of_string() : array
    {
        $lists = array();
        $files = file(BASEPATH . '/config/private.key');
        if(!empty($files)) {
            foreach($files as $line) {
                $lists[] = (string) trim($line, "\n");
            }
        }
        return $lists;
    }

    private function random_string()
    {
        $lists = $this->list_of_string();
        $max = (count($lists) - 1);
        $n = rand(0, $max);
        return bin2hex(base64_encode($lists[$n]));
    }

    final public static function reverseToken(array $arguments, string $userGroup)
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


    /*
    public static function ciphering(int $i = null, string $name = 'des')
    {
        $ciphering = array_filter(openssl_get_cipher_methods(), function ($c) use ($name) {
            return stripos($c, $name) === FALSE;
        });
        return (!empty($i)) ? $ciphering[$i] : $ciphering;
    }

    private static function decrypt(string $string, string $key, $encrypt_iv, string $ciphering = null, $options = 0)
    {
        $iv = hex2bin($encrypt_iv);
        return openssl_decrypt($string, $ciphering, $key, $options, $iv);
    }

    public static function encrypt(string $string, string $key, string $ciphering = null, $options = 0)
    {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($ciphering));
        $arrEncrypt['string'] = openssl_encrypt($string, $ciphering, $key, $options, $iv);
        $arrEncrypt['iv'] = bin2hex($iv);
        return $arrEncrypt;
    }

    private static function encryptPattern(string $encryptString)
    {
        $sectorStr = explode('/;', 'Xq3YbA==/;7e78dd0f6427d9cce5d07bf5dabfbafc');
        $blowfish = Config::App('blowfish');
        $ciphering = (string) self::ciphering(34);
        $d = self::decrypt($sectorStr[0], $blowfish, $sectorStr[1], $ciphering);
        $patternStr = explode($d, $encryptString);
        return serialize([$d, $blowfish, $ciphering, bin2hex(self::decrypt($patternStr[0], $blowfish, $patternStr[1], $ciphering))]);
    }

    public static function encryptVerify(string $encryptString)
    {
        $e = unserialize(self::encryptPattern(Config::App('encryptPattern')));
        $b = hex2bin($e[3]);
        if (strpos($encryptString, $e[0], true)) {
            $encryptStr = explode($e[0], $encryptString);
            $a = self::decrypt($encryptStr[0], $e[1], $encryptStr[1], $e[2]);
            return ($a === $b);
        }
        return false;
    }

    public static function randomEncryptString()
    {
        $e = unserialize(self::encryptPattern(Config::App('encryptPattern')));
        return implode($e[0], self::encrypt(hex2bin($e[3]), $e[1], $e[2]));
    }*/
}
