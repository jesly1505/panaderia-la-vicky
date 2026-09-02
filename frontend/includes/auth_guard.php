<?php
/**
 * Guard centralizado de autenticación para el frontend.
 * Inicia sesión, carga permisos y verifica que el usuario esté autenticado.
 * Si no está autenticado, redirige a login.php.
 *
 * Uso: require_once __DIR__ . '/includes/auth_guard.php';
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/permisos.php';

if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Mantenimiento global del sistema: solo el administrador puede operar.
$maintenance = filter_var(getenv('APP_MAINTENANCE') ?: false, FILTER_VALIDATE_BOOLEAN);
if ($maintenance && !tiene_permiso('perfil.gestionar')) {
    header("Location: error.php?code=503");
    exit();
}
