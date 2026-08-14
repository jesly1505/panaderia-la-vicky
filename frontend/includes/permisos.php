<?php
/**
 * Helper compartido de permisos (RBAC) para el frontend.
 * Requiere la sesión iniciada (los permisos se cargan en el login).
 */

if (!function_exists('tiene_permiso')) {
    /**
     * True si el usuario actual posee al menos uno de los permisos indicados.
     * @param string ...$necesarios
     */
    function tiene_permiso(...$necesarios): bool {
        $permisos = $_SESSION['permisos'] ?? [];
        foreach ($necesarios as $p) {
            if (in_array($p, $permisos, true)) return true;
        }
        return false;
    }
}
