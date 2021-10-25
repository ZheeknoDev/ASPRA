<?php

/**
 * @category Class
 * @package  zheeknodev/aspra
 * @author   ZheeknoDev <million8.me@gmail.com>
 * @license  https://opensource.org/licenses/MIT - MIT License 
 * @link     https://github.com/ZheeknoDev/Aspra
 */

namespace App\Core;

use App\Core\Database\PtcDb;
use Zheeknodev\Roma\Router;
use Zheeknodev\Roma\Router\Request;

final class Application
{
    private $credentials;
    private $queryBuilder;
    private $middleware;
    private $router;

    public static $_queryBuilder;

    final public function __construct()
    {
        $this->set_timezone();
        $this->set_debug_mode(new Request);
        $this->set_connect_database();
        $this->set_credentials();
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
        if ($cond_1 && $cond_2) {
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
     * Setup default credentials
     * @return void
     */
    private function set_credentials()
    {
        # set default credentials
        $this->credentials = [
            'origin_key' => base64_decode(Config::App('app_key')),
            'groups' => array()
        ];
        # get client's group
        $clientTokens = $this->queryBuilder->table('client_tokens')
            ->select(['group', 'somewords'])
            ->run();

        if (count($clientTokens) > 0) {
            foreach ($clientTokens as $client) {
                $this->credentials['groups'][$client->group] = $client->somewords;
            }
        }
    }

    /**
     * Connedt the database
     * @return void
     */
    private function set_connect_database(): void
    {
        $data = array_filter(Config::App('database'), function ($details) {
            return ($details !== null);
        });
        if (!empty($data)) {
            # initializing a pdo object to run queries with the query builder
            $pdo_connection = (string) "{$data['CONNECTION']}:host={$data['HOST']};dbname={$data['DATABASE']};charset:{$data['CHARSET']};";
            $pdo = new \PDO($pdo_connection, $data['USERNAME'], $data['PASSWORD'], [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_WARNING, \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ]);
            # initializing the query builder object with PDO support
            self::$_queryBuilder = $this->queryBuilder = new \App\Core\Database\PtcQueryBuilder($pdo);
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
