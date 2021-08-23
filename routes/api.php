<?php

/**
 * @category File
 * @package  Routes
 * @author   Marry Go Round <million8.me@gmail.com>
 * @license  https://opensource.org/licenses/MIT - MIT License 
 * @link     https://github.com/ZheeknoDev/aspra
 */

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Here is where you can register API routes for your application.
*/

# wellcome
$router->get('/', function () use ($router) {
    return $router->response->json([
        'StatusCode' => http_response_code(),
        'Application' => \App\Core\Config::App('app_name'),
        'Version' => \App\Core\Config::App('app_version'),
        'Message' => "Welcome to the ASPRA",
        'Response' => true,
    ]);
});

$router->group(['prefix' => '/api/v1/auth/users', 'middleware' => ['api']], function () use ($router) {
    # user register to get a new token
    $router->post('/register', 'AuthController:userRegister');
    # user logged in to renew a token
    $router->post('/get-token', 'AuthController:userGetToken');
});

$router->group(['prefix' => '/api/v1', 'middleware' => ['api','auth']], function () use ($router) {
    # example for testing passing request through the middlewares
    $router->get('/example', function () use ($router) {
        return $router->response->json([
            'StatusCode' => http_response_code(),
            'Message' => "Welcome to API",
            'Response' => true,
        ]);
    });
});
