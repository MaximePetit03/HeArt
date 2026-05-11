<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../controllers/Controller.php';
require_once __DIR__ . '/../models/Model.php';

session_start();

$router = new Router();

$router->get('/', 'HomeController', 'index');
$router->dispatch();