<?php
/**
 * Helper compartido de permisos (RBAC) para el frontend.
 * Requiere la sesión iniciada (los permisos se cargan en el login).
 */

if (!function_exists('tiene_permiso')) {
    /**
     * True si el usuario actual posee al menos uno de los permisos indicados.
     * Los administradores siempre tienen todos los permisos.
     *
     * @param string ...$necesarios
     */
    function tiene_permiso(...$necesarios): bool {
        // Administrador por rol o ID siempre tiene acceso total
        $rol = $_SESSION['rol'] ?? $_SESSION['rol_nombre'] ?? '';
        $rolId = (int)($_SESSION['rol_id'] ?? 0);
        if ($rol === 'Administrador' || $rolId === 1) {
            return true;
        }

        $permisos = $_SESSION['permisos'] ?? [];
        if (!is_array($permisos) || empty($permisos)) {
            // Si el usuario está autenticado pero no tiene permisos cargados en sesión
            return false;
        }

        foreach ($necesarios as $p) {
            if (in_array($p, $permisos, true)) {
                return true;
            }
        }
        return false;
    }
}
