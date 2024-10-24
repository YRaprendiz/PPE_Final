<?php
session_start();
require_once '../config/config.php';
require_once '../core/Router.php';

$router = new Router();

// Define routes
$router->add('GET', '/', 'HomeController', 'index');
$router->add('GET', '/rooms', 'RoomController', 'index');
$router->add('GET', '/rooms/:id', 'RoomController', 'show');
$router->add('GET', '/cars', 'CarController', 'index');
$router->add('GET', '/cars/:id', 'CarController', 'show');
$router->add('GET', '/login', 'AuthController', 'loginForm');
$router->add('POST', '/login', 'AuthController', 'login');
$router->add('GET', '/register', 'AuthController', 'registerForm');
$router->add('POST', '/register', 'AuthController', 'register');
$router->add('GET', '/profile', 'UserController', 'profile');
$router->add('POST', '/profile', 'UserController', 'updateProfile');

try {
    $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
} catch (Exception $e) {
    // Handle 404 or other errors
    header("HTTP/1.0 404 Not Found");
    require '../views/errors/404.php';
}
