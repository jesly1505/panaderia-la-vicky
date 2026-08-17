<?php
/**
 * Autoloader PSR-4 del proyecto.
 * Prefiere el autoloader generado por Composer (vendor/autoload.php).
 * Si Composer no está disponible, registra un autoloader manual equivalente.
 * Carga las variables de entorno (.env) y expone la clase legacy `Database`.
 */

$composerAutoload = __DIR__ . '/vendor/autoload.php';
$usingComposer = is_file($composerAutoload);

if ($usingComposer) {
    // Composer se encarga de config/env.php (declarado en "autoload.files")
    require_once $composerAutoload;
} else {
    spl_autoload_register(function (string $class): void {
        $prefix = 'App\\';
        if (str_starts_with($class, $prefix)) {
            $relative = substr($class, strlen($prefix));
            $file = __DIR__ . '/backend/' . str_replace('\\', '/', $relative) . '.php';
            if (is_file($file)) {
                require $file;
            }
        }
    });
    require_once __DIR__ . '/config/env.php';
}

// Retrocompatibilidad: permite seguir usando `new Database()` (global)
// mientras se migran los modelos a inyección de dependencias.
spl_autoload_register(function (string $class): void {
    if ($class === 'Database' && !class_exists('Database', false)) {
        class_alias(\App\Core\Database::class, 'Database');
    }
});
