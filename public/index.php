<?php

use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

require_once '../App/bootstrap.php';

if ($_ENV['APP_ENV'] === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    $whoops = new Run();
    $whoops->pushHandler(new PrettyPageHandler());
    $whoops->register();
} else {
    //For production handling
}
