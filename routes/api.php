<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Here is where you can register API routes for your application.
*/

$router->get('/', function () use ($router) {
    return $router->response->json([
        'StatusCode' => http_response_code(),
        'Application' => \App\Core\Config::App('app_name'),
        'Version' => \App\Core\Config::App('app_version'),
        'Message' => "Welcome to the ASPRA",
        'Response' => true,
    ]);
});

$router->group(['prefix' => '/api/v1/auth', 'middleware' => ['api']], function () use ($router) {
    # user register to get a new token
    $router->post('/register', 'AuthController:register');
    # user login to get token
    $router->post('/login', 'AuthController:login');
});

$router->group(['prefix' => '/api/v1', 'middleware' => ['api','auth']], function () use ($router) {
    # example for send reqeust
    $router->get('/', function () use ($router) {
        return $router->response->json([
            'StatusCode' => http_response_code(),
            'Message' => "Welcome to API",
            'Response' => true,
        ]);
    });
});
