<?php

/**
 * @category Class
 * @package  zheeknodev/aspra
 * @author   ZheeknoDev <million8.me@gmail.com>
 * @license  https://opensource.org/licenses/MIT - MIT License 
 * @link     https://github.com/ZheeknoDev/Aspra
 */

namespace App\Middleware;

use App\Core\Auth;
use Zheeknodev\Roma\Middleware\InterfaceMiddleware;
use Zheeknodev\Roma\Router\Response;

class RequestWithAuth implements InterfaceMiddleware
{
    public function handle($request, callable $next)
    {
        # if the request that from API and JSON object.
        if (Auth::hasAuthorized('bearer')) {
            return $next($request);
        }
        return Response::instance()->redirect('/404');
    }
}
