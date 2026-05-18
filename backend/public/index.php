<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 🚀 Démarrage de session en priorité absolue pour Docker
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../controllers/AbstractController.php';
require_once __DIR__ . '/../models/AbstractModel.php';
require_once __DIR__ . '/../managers/AbstractManager.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

spl_autoload_register(function (string $class): void {
    $paths = [
        BASE_PATH . '/controllers/' . $class . '.php',
        BASE_PATH . '/models/'      . $class . '.php',
        BASE_PATH . '/managers/'     . $class . '.php',
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = '/' . trim($uri, '/');

AuthMiddleware::handle($uri, $_SERVER['REQUEST_METHOD']);

$router = new Router();
$router->get('/',           'AlbumController', 'index');
$router->get('/login',      'AuthController',  'loginForm');
$router->post('/login',     'AuthController',  'login');
$router->get('/register',   'AuthController',  'registerForm');
$router->post('/register',  'AuthController',  'register');
$router->get('/logout',     'AuthController',  'logout');
$router->get('/profile',    'UserController',  'profile');
$router->post('/profile/update', 'UserController', 'updateProfile');
$router->post('/profile/delete', 'UserController', 'deleteAccount');

$router->dispatch();