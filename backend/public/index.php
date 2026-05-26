<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
$router->get('/albums/create',  'AlbumController', 'create');
$router->post('/albums/create', 'AlbumController', 'create');
$router->get('/albums/edit',    'AlbumController', 'edit');
$router->get('/albums/my-albums', 'AlbumController', 'myAlbums');
$router->post('/albums/upload-photos', 'AlbumController', 'uploadPhotos');
$router->post('/albums/update-visibility', 'AlbumController', 'updateVisibility');
$router->post('/photos/toggle-visibility', 'PhotoController', 'toggleVisibility');
$router->post('/photos/toggle-tag', 'PhotoController', 'toggleTag');
$router->post('/albums/update', 'AlbumController', 'update');
$router->post('/albums/delete', 'AlbumController', 'delete');
$router->post('/photos/delete', 'PhotoController', 'delete');
$router->get('/albums/show', 'AlbumController', 'show');
$router->get('/photos/show', 'PhotoController', 'show');

try {
    $router->dispatch();
} catch (Throwable $e) {
    echo "<h1>ERREUR CRITIQUE DANS LE ROUTER :</h1>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    die();
}