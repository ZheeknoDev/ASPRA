<?php

/**
 * AUTHENTICATE MIDDLEWARE
 * the filtering the request that gets authorized
 * @category Class
 * @package  App/Middleware
 * @author   Marry Go Round <million8.me@gmail.com>
 * @license  https://opensource.org/licenses/MIT - MIT License 
 * @link     https://github.com/ZheeknoDev/aspra
 */

namespace App\Middleware;

use App\Core\Auth\Auth;
use App\Core\Middlewares\InterfaceMiddleware;
use App\Core\Router\Response;

class Authenticate implements InterfaceMiddleware
{
    public function handle($request, callable $next)
    {
        if(Auth::via()->requestApi()) {
            return $next($request);
        }
        return Response::instance()->redirect('/401');
    }
}