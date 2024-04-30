<?php

/**
 * ROUTES
 */
$router->map('GET', '/', 'App\Controllers\Home@index', 'home-page');
$router->map('GET', '/test', 'App\Controllers\Home@test', 'test-page');
