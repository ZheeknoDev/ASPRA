<?php
namespace App\Core\Middlewares;

interface InterfaceMiddleware
{
    /**
     * @param request $request
     * @param callable $next
     * @return void
     */
    public function handle($request, callable $next);
}