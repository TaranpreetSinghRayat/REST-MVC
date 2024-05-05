<?php
session_start();
header("Access-Control-Allow-Origin: *");

//APP Config
define('APP_ROOT', (dirname(__FILE__)));
define('ROOT', (dirname(dirname(__FILE__))));
define('PUBLIC_ROOT', (dirname(dirname(__FILE__))) . "/public");

//load composer
require_once ROOT . '/vendor/autoload.php';

//load env var
$env = Dotenv\Dotenv::createImmutable(APP_ROOT);
$env->load();

// Initialize Request and Response objects
$request = new \App\Core\Request($_SERVER, $_COOKIE, file_get_contents('php://input'), $_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
$response = new \App\Core\Response();

// Make these objects globally accessible
$GLOBALS['request'] = $request;
$GLOBALS['response'] = $response;

//Load Configs
require_once '../App/Config/app.php';

//Load Functions
require_once '../App/Functions/functions.php';

//Init Core
$app = new \App\Core\Core($request, $response);
// Add middleware to the application
$jwtHandler = new \App\Core\JwtHandler($_ENV['JWT_SECRET_KEY'], $_ENV['URL_ROOT'], ['api']);
$app->addMiddleware(new \App\Middleware\JWTAuthMiddleware($jwtHandler));
// Handle the request
$app->handleRequest();
