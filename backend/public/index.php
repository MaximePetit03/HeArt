<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../controllers/Controller.php';
require_once __DIR__ . '/../models/Model.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

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

// Récupère l'URI courante
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/HeArt/backend/public', '', $uri);
$uri = '/' . trim($uri, '/');

AuthMiddleware::handle($uri, $_SERVER['REQUEST_METHOD']);

$router = new Router();
$router->get('/',           'AlbumController', 'index');
$router->get('/login',      'AuthController',  'loginForm');
$router->post('/login',     'AuthController',  'login');
$router->get('/register',   'AuthController',  'registerForm');
$router->post('/register',  'AuthController',  'register');
$router->get('/logout',     'AuthController',  'logout');
$router->dispatch();