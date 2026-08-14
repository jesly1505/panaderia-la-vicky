<?php
require_once __DIR__ . '/../autoload.php';

// Configuración robusta de sesiones
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Depuración controlada por entorno (APP_DEBUG en .env)
$debug = filter_var($_ENV['APP_DEBUG'] ?? (getenv('APP_DEBUG') ?: 'true'), FILTER_VALIDATE_BOOLEAN);
error_reporting($debug ? E_ALL : 0);
ini_set('display_errors', $debug ? '1' : '0');

// Resolución de la ruta a través del router
list($router, $container) = require __DIR__ . '/bootstrap.php';

$router->dispatch($_GET['route'] ?? '', $container);
