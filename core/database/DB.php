<?php

namespace App\Core\Database;

use App\Core\Application;

abstract class DB
{   
    public static function __callStatic($name, $arguments)
    { 
        $app = new \App\Core\Application;
        $method = strtolower($name);
        return call_user_func_array([$app->database, $method], $arguments);
    }
}