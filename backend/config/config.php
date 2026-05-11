<?php

$envPath = dirname(__DIR__, 2) . '/.env';

if (!file_exists($envPath)) {
    die('Fichier .env introuvable');
}

$env = parse_ini_file($envPath);

// Base de données
define('DB_HOST', $env['DB_HOST']);
define('DB_PORT', $env['DB_PORT']);
define('DB_NAME', $env['DB_NAME']);
define('DB_USER', $env['DB_USER']);
define('DB_PASS', $env['DB_PASS']);

// Chemins
define('BASE_PATH', dirname(__DIR__));
define('BASE_URL',  'http://localhost/HeArt/backend/public');

// Logs
define('LOG_PATH', BASE_PATH . '/logs/app.log');