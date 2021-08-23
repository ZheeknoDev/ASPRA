<?php

namespace App\Core\Middlewares;

use App\Core\Middlewares\InterfaceMiddleware;
use App\Core\Router\Request;

class Middleware
{
    private $callable;
    private $collect = [];
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->callable = function ($request) {
            return $request;
        };
    }


    /**
     * @param string $name
     * @param object $middleware
     * @return void
     */
    final public function add($name, InterfaceMiddleware $middleware): void
    {
        $collect = $this->callable;
        $this->collect[$name] = function ($request) use ($middleware, $collect) {
            return $middleware->handle($request, $collect);
        };
    }

    /**
     * @param string $middleware
     * @return void
     */
    final public function call(string $middleware): void
    {
        if (!empty($this->collect[$middleware]) && is_callable($this->collect[$middleware])) {
            $callable = call_user_func($this->collect[$middleware], $this->request);
            if($callable !== $this->request) {
                exit();
            }
        }
    }

    /**
     * @param array $middleware
     * @return void
     */
    final public function register(array $middleware = array()): void
    {
        if (!empty($middleware)) {
            foreach ($middleware as $name => $class) {
                $this->add($name, new $class);
            }
        }
    }
}
