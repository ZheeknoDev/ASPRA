<?php

/**
 * @category Class
 * @package  App/Core/Router
 * @author   ZheeknoDev <million8.me@gmail.com>
 * @license  https://opensource.org/licenses/MIT - MIT License 
 * @link     https://github.com/ZheeknoDev/aspra
 */

namespace App\Core\Router;

use \App\Core\Router\Request;
use \App\Core\Router\Response;
use Exception;

final class Router
{
    private $namespace;
    private $route_request;
    private $route_group_middleware;
    private $route_group_prefix;
    private $require_routes = [];

    public $middleware;
    public $request;
    public $response;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    final public function __debugInfo()
    {
        return;
    }

    /**
     * Method: DELETE
     * @param string $route
     * @param string|callable $callable
     */
    public function delete(string $route, $callable)
    {
        if ($this->request->requestMethod == 'DELETE') {
            return $this->route($route, $callable);
        }
    }

    /**
     * Dispatch the routes
     * @return void 
     */
    public function dispatch(): void
    {
        # load route files
        if (!empty($this->require_routes)) {
            foreach ($this->require_routes as $namespace => $route) {
                $this->namespace = $namespace;
                call_user_func_array($route, [$this]);
            }
        }
        # execute route's request
        if (!empty($this->route_request['callable'])) {
            # clear route's group prefix
            $this->route_group_prefix = null;
            # clear route's group middleware
            $this->route_group_middleware = null;

            # call the middleware
            $condition_call_middleware_1 = !empty($this->middleware) ? 1 : 0;
            $condition_call_middleware_2 = !empty($this->route_request['middleware']) && is_array($this->route_request['middleware']) ? 1 : 0;
            if ($condition_call_middleware_1 && $condition_call_middleware_2) {
                foreach ($this->route_request['middleware'] as $middleware) {
                    $this->middleware->call($middleware);
                }
            }

            # execute callable
            echo call_user_func($this->route_request['callable']);
        } else {
            # 404 the request not found
            echo $this->onHttpError(404);
        }
    }
    
    /**
     * Method: GET
     * @param string $route
     * @param string|callable $callable
     */
    public function get(string $route, $callable)
    {
        if ($this->request->requestMethod == 'GET') {
            return $this->route($route, $callable);
        }
    }

    /**
     * Grouping the routes
     * @param array $route ['prefix', 'middleware']
     * @param callable $callable
     */
    public function group(array $route, callable $callable)
    {
        if (!empty($route['prefix'])) {
            $this->route_group_prefix = $route['prefix'];
        }
        if (!empty($route['middleware'])) {
            $this->route_group_middleware = $route['middleware'];
        }
        if (is_callable($callable)) {
            echo call_user_func($callable, $route['prefix']);
        }
    }

    /**
     * Call the middlewares to execute on the routes
     * @param array $middleware - list of the middlewares
     * @return void
     */
    public function middleware(array $middleware): void
    {
        if (!empty($this->route_request['callable']) && empty($this->route_request['middleware'])) {
            $this->route_request['middleware'] = $middleware;
        }
    }

    /**
     * Define a namespace of routes
     * @param array $arguments
     * @param callable $callable
     */
    public function namespace(array $arguments, callable $callable)
    {
        if (!empty($arguments['namespace'])) {
            $namespace = $arguments['namespace'];
            if (!empty($callable) && is_callable($callable)) {
                $this->require_routes[$namespace] = $callable;
            }
        }
    }

    /**
     * Response when the requests have something went wrong
     * @param int $code - define the HTTP response code to response
     * @param string|callable $callable
     */
    public function onHttpError(int $code = 404)
    {
        if (!empty($code)) {
            return $this->response->json_form_response($this->response->getResponseMessage($code), false, $code);
        }
    }

    /**
     * Method: PATCH
     * @param string $route
     * @param string|callable $callable
     */
    public function patch(string $route, $callable)
    {
        if ($this->request->requestMethod == 'PATCH') {
            return $this->route($route, $callable);
        }
    }

    /**
     * Method: POST
     * @param string $route
     * @param string|callable $callable
     */
    public function post(string $route, $callable)
    {
        if ($this->request->requestMethod == 'POST') {
            return $this->route($route, $callable);
        }
    }

    /**
     * Method: PUT
     * @param string $route
     * @param string|callable $callable
     */
    public function put(string $route, $callable)
    {
        if ($this->request->requestMethod == 'PUT') {
            return $this->route($route, $callable);
        };
    }

    /**
     * Execute the method of routes
     * @param string $route
     * @param string|callable $callable
     */
    private function route(string $route, $callable)
    {
        # if the route's file is not include
        if (empty($this->require_routes)) {
            throw new Exception('Missing collection of the route data.');
        }

        # if the namespace of route is not sets
        if (empty($this->namespace)) {
            throw new Exception('Missing the namespace of the route.');
        };

        /**
         * Closure : validate a callable 
         * @param callable|string $callable
         * @param array $arguments
         * @return callable
         */
        $getCallable = function ($callable, array $arguments = array()) {
            if (is_string($callable)) {
                if (preg_match("/([:])/", $callable)) {
                    $explode_callable = explode(':', $callable);
                    if (count($explode_callable) == 2) {
                        $className = implode('\\', [$this->namespace, ucwords($explode_callable[0])]);
                        $methodName = $explode_callable[1];
                        $classIsExist = class_exists($className) ? true : false;
                        $methodIsExist = method_exists($className, $methodName) ? true : false;
                        if (($classIsExist && $methodIsExist)) {
                            $namespace = $callable;
                            $callable = [new $className, $methodName];
                        }
                    }
                }
            }
            return function () use ($callable, $arguments) {
                return call_user_func_array($callable, $arguments);
            };
        };


        if (!empty($this->route_group_prefix)) {
            $route = implode('', [$this->route_group_prefix, $route]);
        }

        # valida the current request & route
        $current_request_uri = filter_var($this->request->requestUri, FILTER_SANITIZE_URL);
        $current_request_uri = rtrim($current_request_uri, '/');
        $current_request_uri = strtok($current_request_uri, '?');

        # the request is unauthorized
        if (is_numeric(ltrim($current_request_uri, '/'))) {
            $code = ltrim($current_request_uri, '/');
            if ($this->response->getResponseMessage($code) !== null) {
                return $this->onHttpError($code);
            }
        }

        # remove slash at the end
        if ($route != '/') {
            $route = rtrim($route, '/');
        }

        # explode current request & route
        $explode_route = explode('/', $route);
        $explode_current_request_uri = explode('/', $current_request_uri);
        array_shift($explode_route);
        array_shift($explode_current_request_uri);

        # default path '/'
        if ($explode_route[0] == '' && count($explode_current_request_uri) == 0) {
            $this->route_request['callable'] = $getCallable($callable, array());
        }

        # if section of route & current request are equal.
        if (count($explode_route) == count($explode_current_request_uri)) {
            $arguments = [];
            for ($i = 0; $i < count($explode_route); $i++) {
                $explode_route_part = $explode_route[$i];
                if (preg_match("/([\{$\}])/", $explode_route_part)) {
                    $explode_route_part = trim($explode_route_part, '{$}');
                    array_push($arguments, $explode_current_request_uri[$i]);
                } else if ($explode_route[$i] != $explode_current_request_uri[$i]) {
                    # if section of route & current request are equal
                    # but value of route's section & current request are not same
                    return $this;
                }
            }
            # set callable into route's request
            $this->route_request['callable'] = $getCallable($callable, $arguments);

            # set middleware of group into route's request
            if (!empty($this->route_group_prefix) && !empty($this->route_group_middleware)) {
                $this->route_request['middleware'] = $this->route_group_middleware;
            }
        }

        # return this class
        return $this;
    }
}
