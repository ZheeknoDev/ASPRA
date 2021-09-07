<?php

/**
 * REQUEST API MIDDLEWARE
 * the filtering the request that from API
 * @category Class
 * @package  App/Middleware
 * @author   ZheeknoDev <million8.me@gmail.com>
 * @license  https://opensource.org/licenses/MIT - MIT License 
 * @link     https://github.com/ZheeknoDev/aspra
 */

namespace App\Middleware;

use App\Core\Middlewares\InterfaceMiddleware;
use App\Core\Router\Response;

class RequestApi implements InterfaceMiddleware
{
    public function handle($request, callable $next)
    {
        if ($request->requestApi()) {
            return $next($request);
        }
        return Response::instance()->redirect('/401');
    }
}
