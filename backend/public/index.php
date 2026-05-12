<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../controllers/Controller.php';
require_once __DIR__ . '/../models/Model.php';

spl_autoload_register(function (string $class): void {
    $paths = [
        BASE_PATH . '/controllers/' . $class . '.php',
        BASE_PATH . '/models/'      . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

session_start();

$router = new Router();
$router->get('/',           'AlbumController', 'index');
$router->get('/login',      'AuthController',  'loginForm');
$router->post('/login',     'AuthController',  'login');
$router->get('/register',   'AuthController',  'registerForm');
$router->post('/register',  'AuthController',  'register');
$router->get('/logout',     'AuthController',  'logout');
$router->dispatch();