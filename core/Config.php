<?php

/**
 * @category Class
 * @package  App/Config
 * @author   ZheeknoDev <million8.me@gmail.com>
 * @license  https://opensource.org/licenses/MIT - MIT License 
 * @link     https://github.com/ZheeknoDev/aspra
 */

namespace App\Core;

class Config
{
    private $instance = [];

    public function __construct(string $config)
    {
        $config = strtolower($config);
        $configPath = (string) implode('/', [BASEPATH, "config/{$config}" . EXT]);
        if (file_exists($configPath) && realpath($configPath) !== false) {
            $this->instance[$config] = include($configPath);
        }
    }

    public static function __callStatic($name, $arguments)
    {
        $name = strtolower($name);
        $element = strtoupper($arguments[0]);
        $config = new self($name);
        if($element == 'APP_KEY') {
            return base64_encode($config->$name->$element);
        }
        return $config->$name->$element;
    }

    public function __get($name)
    {
        return (object) $this->instance[$name];
    }

    final public function __debugInfo()
    {
        return;
    }
}
