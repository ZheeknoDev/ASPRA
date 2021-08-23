<?php

/**
 * @category Class
 * @package  App/Core/Middleware
 * @author   Marry Go Round <million8.me@gmail.com>
 * @license  https://opensource.org/licenses/MIT - MIT License 
 * @link     https://github.com/ZheeknoDev/aspra
 */

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
