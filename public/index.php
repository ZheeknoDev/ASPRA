<?php

/**
 * @category File
 * @package  zheeknodev/aspra
 * @author   ZheeknoDev <million8.me@gmail.com>
 * @license  https://opensource.org/licenses/MIT - MIT License 
 * @link     https://github.com/ZheeknoDev/Aspra
 * 
 * 
 * 
 * Welcome to A Simple PHP RESTFul API or ASPRA.
 * 
 * Test to make sure that ASPRA is running on PHP 7.0 or newer. Once you are
 * to check the PHP version quickly.
 */

# Check PHP version
$minPHPVersion = 7.0;
if (phpversion() < $minPHPVersion) {
    die("Your PHP version must be {$minPHPVersion} or higher to run Current version: " . phpversion());
}

/**
 * Set the error reporting level. Unless you have a special need, E_ALL is a
 * good level for error reporting.
 */
error_reporting(E_ALL & ~E_STRICT);

# Define the base path
define('BASEPATH', dirname(__DIR__));
# Define the extension file.
define('EXT', '.php');

# Autoload
require_once(__DIR__ . '/../vendor/autoload.php');

# session
//session_start();

# Start Application
$app = new App\Core\Application();
/**
 * -----------------------------------------------
 * Middlewares
 * -----------------------------------------------
 * Register the middleware
 * You can register owned the middlewares here.
 * @example ['alias of middleware' => 'path of middlware']
 */
$app->set_middleware([
    'api' => \Zheeknodev\Roma\Middleware\Api\RequestWithApi::class,
    'auth' => \App\Middleware\RequestWithAuth::class
]);
# Register the routes
$app->set_route(function ($router) {
    require_once(BASEPATH . '/routes/api' . EXT);
});
# Run Application
$app->run();
