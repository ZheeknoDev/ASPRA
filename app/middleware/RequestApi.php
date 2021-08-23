<?php

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
