<?php
$envPath = __DIR__ . '/.env';

if (!file_exists($envPath)) {
    die('Fichier .env introuvable. Chemin cherché : ' . $envPath);
}

$env = parse_ini_file($envPath);

define('DB_HOST', $env['DB_HOST']);
define('DB_PORT', $env['DB_PORT']);
define('DB_NAME', $env['DB_NAME']);
define('DB_USER', $env['DB_USER']);
define('DB_PASS', $env['DB_PASS']);

define('BASE_PATH', dirname(__DIR__));
define('BASE_URL',  'http://localhost:8888');
define('ASSETS_URL', 'http://localhost:8888/frontend');