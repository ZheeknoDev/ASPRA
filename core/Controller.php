<?php

/**
 * @category Class
 * @package  zheeknodev/aspra
 * @author   ZheeknoDev <million8.me@gmail.com>
 * @license  https://opensource.org/licenses/MIT - MIT License 
 * @link     https://github.com/ZheeknoDev/Aspra
 */

namespace App\Core;

use Zheeknodev\Roma\Router\Response;

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

    final protected function invalid(object $validation)
    {
        # handling errors
        $errors = $validation->errors();
        $errorKeys = [];
        foreach ($errors->firstOfAll() as $key => $value) {
            $errorKeys[] = $key;
        }

        # return validation errors
        $listOfInputFields = implode(', ', $errorKeys);
        $message = "The input field ({$listOfInputFields}) " . (count($errorKeys) > 1 ? 'are' : 'is') . " required";
        $this->response->returnJsonPattern->status = false;
        $this->response->returnJsonPattern->response = [
            'warning' => $message,
        ];
        $response = $this->response->returnJsonPattern;
        return $this->response->json($response, 400);
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

    final protected function json(bool $status, array $response, int $httpResponseCode = 200)
    {
        $this->response->returnJsonPattern->status = $status;
        $this->response->returnJsonPattern->response = $response;
        return $this->response->json($this->response->returnJsonPattern, $httpResponseCode);
    }
}
