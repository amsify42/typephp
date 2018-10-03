<?php

require __DIR__.'/../vendor/autoload.php';

define('DS', DIRECTORY_SEPARATOR);
define('ROOT_PATH', __DIR__.'/../');

$typePHP 	= new TypePHP\TypePHP();

$app 		= new App\Init($typePHP->request());

$typePHP->render($app->response());