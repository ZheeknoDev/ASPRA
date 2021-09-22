<?php

/**
 * @category Class
 * @package  App/Core/Controller
 * @author   ZheeknoDev <million8.me@gmail.com>
 * @license  https://opensource.org/licenses/MIT - MIT License 
 * @link     https://github.com/ZheeknoDev/aspra
 */

namespace App\Core;

use Zheeknodev\Roam\Router\Response;

class Controller
{
    private $app;

    protected $middleware;
    protected $request;
    protected $response;
    protected $validator;

    final public function __construct()
    {
        $this->app = new \App\Core\Application;
        $this->request = $this->app->router()->request();
        $this->response =  new Response($this->request);
        $this->middleware = $this->app->middleware();
        $this->validator = new \Rakit\Validation\Validator;
        $this->before();
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
     * !!! NOTICE !!!
     * The before function it will work like a construct function
     * you can coding here before execute the controller class
     */
    protected function before()
    {
        return;
    }

    /**
     * Call the middlewares before execute the controller class
     * @param array $middleware - list of middlewares
     * @return void
     */
    final protected function middleware(array $middleware): void
    {
        if (!empty($middleware) && !empty($this->middleware)) {
            foreach ($middleware as $name) {
                $this->middleware->call($name);
            }
        }
    }

    /**
     * Random the string
     * @param int $length 
     * @return string
     */
    final protected function random_string(int $length = 32) : string
    {
        return (string) base64_encode(openssl_random_pseudo_bytes($length));
    }
}
