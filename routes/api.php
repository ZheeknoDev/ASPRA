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
    return Response::instance()->respond([
        'StatusCode' => http_response_code(),
        'Application' => \App\Core\Config::App('app_name'),
        'Version' => \App\Core\Config::App('app_version'),
        'Message' => "Welcome to the ASPRA",
        'Response' => true,
        'Sipher' => Sipher::randomString(64)
    ]);
});

$router->group(['prefix' => '/api', 'middleware' => ['api']], function () use ($router) {
    # version 1
    $router->group(['prefix' => '/v1'], function () use ($router) {
        # authenicate 
        $router->group(['prefix' => '/auth'], function () use ($router) {
            # register users
            $router->post('/user/register', 'App\Controller\AuthController:postUserRegister');
            # renew user's token
            $router->post('/user/renew-token', 'App\Controller\AuthController:postUserRenewToken');
            # group user
            $router->group(['prefix' => '/user', 'middleware' => ['auth']], function () use ($router) {
                # get user profile
                $router->get('/profile', 'App\Controller\AuthController:getUserProfile');
                # update user profile
                $router->post('/update-profile', 'App\Controller\AuthController:postUpdateUserProfile');
            });
        });
    });
});
