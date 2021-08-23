<?php
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