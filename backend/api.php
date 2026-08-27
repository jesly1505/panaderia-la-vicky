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

// CSRF: validar en requests que modifican datos (POST/PUT/DELETE)
// Excluir rutas públicas que no tienen sesión (login, forgot, reset)
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$route = $_GET['route'] ?? '';
$publicRoutes = ['login', 'login_redirect', 'forgot_password', 'reset_password', 'check_session'];

if (in_array($requestMethod, ['POST', 'PUT', 'DELETE'], true) && !in_array($route, $publicRoutes, true)) {
    $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN']
        ?? $_POST['_csrf_token']
        ?? null;

    if (!\App\Core\CsrfToken::validate($csrfToken)) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Token CSRF inválido. Recargue la página e intente de nuevo.']);
        exit;
    }
}

// Resolución de la ruta a través del router
list($router, $container) = require __DIR__ . '/bootstrap.php';

$router->dispatch($_GET['route'] ?? '', $container);
