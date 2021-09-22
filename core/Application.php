<?php

/**
 * @category Class
 * @package  App/Core/
 * @author   ZheeknoDev <million8.me@gmail.com>
 * @license  https://opensource.org/licenses/MIT - MIT License 
 * @link     https://github.com/ZheeknoDev/aspra
 */

namespace App\Core;

use PDO;
use Zheeknodev\Roam\Router;
use Zheeknodev\Roam\Router\Request;

final class Application
{
    private $database;
    private $middleware;
    private $router;

    final public function __construct()
    {
        $this->set_timezone();
        $this->set_debug_mode(new Request);
        $this->set_connect_database();
        $this->router = new Router;
        $this->middleware = $this->router->middleware;
    }

    final public function __call($method, $arguments)
    {
        $name = strtolower($method);
        $class_methods = get_class_methods($this);
        $class_variables = array_keys(get_class_vars(get_class($this)));
        $cond_1 = !in_array($name, $class_methods);
        $cond_2 = in_array($name, $class_variables);
        if($cond_1 && $cond_2) {
            return $this->{$name};
        }
    }

    final public function __destruct()
    {
        return;
    }

    final public function __debugInfo()
    {
        return;
    }

    /**
     * Run the application
     * @return void
     */
    final public function run(): void
    {
        $this->router->dispatch();
    }

    /**
     * Connedt the database
     * @return void
     */
    private function set_connect_database(): void
    {
        $db = array_filter(Config::App('database'), function ($details) {
            return ($details !== null);
        });
        if (!empty($db)) {
            $connection = $db['CONNECTION'];
            $host = $db['HOST'];
            $port = $db['PORT'];
            $username = $db['USERNAME'];
            $password = $db['PASSWORD'];
            $database = $db['DATABASE'];
            $charset = $db['CHARSET'];

            $connect = "{$connection}:host={$host};dbname={$database};charset:{$charset}";
            $this->database = new \App\Core\Database\Database(new PDO($connect, $username, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING)));
        }
    }

    /**
     * Debug code mode
     * @return void
     */
    private function set_debug_mode(Request $request): void
    {
        if (Config::App('app_debug')) {
            /**
             * I use the flip/Whoops project to be the exception handler, 
             * display the debugging, it's so pretty, easy to view, 
             * and very awesome.
             * @package filp/whoops
             * @link https://github.com/filp/whoops
             * @author Filipe Dobreira
             */
            $phpException = new \Whoops\Run;
            if ($request->viaRequest('/', 'application/json')) {
                $phpException->pushHandler(new \Whoops\Handler\JsonResponseHandler);
            } else {
                $phpException->pushHandler(new \Whoops\Handler\PrettyPageHandler);
            }
            $phpException->register();
        }
    }

    final public function set_middleware(array $middleware)
    {
        return $this->middleware->register($middleware);
    }

    final public function set_route(callable $routes)
    {
        return call_user_func($routes, $this->router);
    }

    /**
     * Timezone
     * @return void
     */
    private function set_timezone(): void
    {
        if (!empty(Config::App('app_timezone'))) {
            date_default_timezone_set("Asia/Bangkok");
        }
    }
}
