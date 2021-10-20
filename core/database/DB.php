<?php

/**
 * @category Class
 * @package  zheeknodev/aspra
 * @author   ZheeknoDev <million8.me@gmail.com>
 * @license  https://opensource.org/licenses/MIT - MIT License 
 * @link     https://github.com/ZheeknoDev/Aspra
 */

namespace App\Core\Database;

abstract class DB
{
    private static $_queryTable;
    private static $_queryStatements;

    protected static $_table;

    public static function __callStatic($name, $arguments)
    {
        $method = strtolower($name);
        $isChild = (static::class != self::class);
        $hasParent = (get_parent_class(static::class) !== false);

        # if called from child class
        if ($isChild && $hasParent) {
            if (empty(static::$_table)) {
                return false;
            }
            static::$_table = strtolower(static::$_table);
            self::$_queryTable = call_user_func_array([\App\Core\Application::$_queryBuilder, 'table'], [static::$_table]);
        }
        $objectClass = (!empty(self::$_queryTable) ? self::$_queryTable : \App\Core\Application::$_queryBuilder);
        self::$_queryStatements = call_user_func_array([$objectClass, $method], $arguments);
        return self::$_queryStatements;
    }
}
