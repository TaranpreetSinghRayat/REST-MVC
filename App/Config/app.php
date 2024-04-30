<?php

//Db Params
define('DB_HOST', getenv('HOST'));
define('DB_DB', getenv('DB')); // enter database name
define('DB_USER', getenv('USER')); // enter your db user.
define('DB_PASS', 'TjTs1EAYKonC7u95ZOx2'); //enter your database password.

define('URL_ROOT', getenv('URL_ROOT'));
define('SITE_NAME', getenv('SITE_NAME'));
define('USE_SSL', (bool)getenv('USE_SSL'));

define('DEFAULT_CONTROLLER', getenv('DEFAULT_CONTROLLER'));
define('DEFAULT_METHOD', getenv('DEFAULT_METHOD'));

//Encryption Key
define('ENC_KEY', getenv('ENC_KEY'));
define('TIME_ZONE', getenv('TIME_ZONE'));
define('DEBUG', (bool)getenv('DEBUG'));

define('APP_VER', getenv('VERSION'));

$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
define('CURRENT_LINK', $actual_link);
