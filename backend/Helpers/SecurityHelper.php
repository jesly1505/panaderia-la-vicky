<?php
namespace App\Helpers;

class SecurityHelper {
    
    /**
     * Verifica que el usuario tenga acceso a un módulo o permiso
     */
    public static function requireAccess($permiso) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario'])) {
            header("Location: ../../frontend/login.php?error=sesion");
            exit();
        }

        $rol = strtolower($_SESSION['rol'] ?? '');
        if ($rol === 'administrador' || $rol === 'admin') {
            return true;
        }

        $permisos = $_SESSION['permisos'] ?? [];
        if (!empty($permiso) && !in_array($permiso, $permisos, true)) {
            header("Location: ../../frontend/index.php?error=acceso_denegado");
            exit();
        }

        return true;
    }
}

// Alias global para compatibilidad
if (!class_exists('SecurityHelper')) {
    class_alias('App\Helpers\SecurityHelper', 'SecurityHelper');
}
