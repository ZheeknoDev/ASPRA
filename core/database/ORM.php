<?php

/**
 * @category Class
 * @package  zheeknodev/aspra
 * @author   ZheeknoDev <million8.me@gmail.com>
 * @license  https://opensource.org/licenses/MIT - MIT License 
 * @link     https://github.com/ZheeknoDev/Aspra
 */

namespace App\Core\Database;

abstract class ORM extends DB
{
    public static function __callStatic($name, $arguments)
    {
        if (static::class != self::class) {
            return parent::__callStatic($name, $arguments);
        }
    }

    final public static function factory(string $name)
    {
        $model = ucwords($name);
        $class = "\\App\\Model\\{$model}";
        if (class_exists($class) && !empty($class::$_table)) {
            self::$_table = $class::$_table;
            return parent::table(self::$_table);
        }
    }
}
