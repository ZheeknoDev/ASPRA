<?php

/**
 * @category File
 * @package  zheeknodev/aspra
 * @author   ZheeknoDev <million8.me@gmail.com>
 * @license  https://opensource.org/licenses/MIT - MIT License 
 * @link     https://github.com/ZheeknoDev/Aspra
 */


use Zheeknodev\Roma\Router\Response;
use Zheeknodev\Sipher\Sipher;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Here is where you can register API routes for your application.
*/

# wellcome
$router->get('/', function () {
    return Response::instance()->json([
        'StatusCode' => http_response_code(),
        'Application' => \App\Core\Config::App('app_name'),
        'Version' => \App\Core\Config::App('app_version'),
        'Message' => "Welcome to the ASPRA",
        'Response' => true,
        'Sipher' => Sipher::randomString(64)
    ]);
});

$router->group(['prefix' => '/api/v1', 'middleware' => ['api']], function() use ($router) {
    $router->post('/user/register', 'App\Controller\UserController:postUserRegister');
    $router->post('/user/renew-token', 'App\Controller\UserController:postUserRenewToken');
});


$router->group(['prefix' => '/api/v1', 'middleware' => ['api','auth']], function () use ($router) {
    # example for testing passing request through the middlewares
    $router->get('/user/example-auth', function () use ($router) {
        return Response::instance()->json([
            'StatusCode' => http_response_code(),
            'Message' => "Welcome to API",
            'Response' => true,
        ]);
    });
});
