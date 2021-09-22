<?php

/**
 * @category Class
 * @package  App/Core/Database
 * @author   ZheeknoDev <million8.me@gmail.com>
 * @license  https://opensource.org/licenses/MIT - MIT License 
 * @link     https://github.com/ZheeknoDev/aspra
 */

namespace App\Core\Database;

abstract class DB
{
    public static function __callStatic($name, $arguments)
    {
        $app = new \App\Core\Application;
        $method = strtolower($name);
        return call_user_func_array([$app->database(), $method], $arguments);
    }
}
