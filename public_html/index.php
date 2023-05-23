<?php
ini_set('session.cookie_lifetime', '864000');

require '../vendor/autoload.php';

error_reporting(E_ALL);
set_error_handler('Core\Error::errorHandler');
set_exception_handler('Core\Error::exceptionHandler');

session_start();

$router = new Core\Router();

$router -> add('', ['controller' => 'Home', 'action' => 'index']);
$router -> add('login', ['controller' => 'Login', 'action' => 'new']);
$router -> add('logout', ['controller' => 'Login', 'action' => 'destroy']);
$router -> add('password/reset/{token:[\da-f]+}', ['controller' => 'Password', 'action' => 'reset']);
$router -> add('signup/activate/{token:[\da-f]+}', ['controller' => 'Signup', 'action' => 'activate']);
$router -> add('{controller}/{action}');
$router -> add('{controller}/{id:\d+}/{action}');
$router -> add('{controller}/{date:\d\d\d\d-\d\d-\d\d}/{action}');
//$router -> add('admin/{controller}/{action}', ['namespace' => 'Admin']);

$router -> dispatch($_SERVER['QUERY_STRING']);