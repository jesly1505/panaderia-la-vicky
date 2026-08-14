<?php
/**
 * Lint de sintaxis para todo el proyecto (composer lint).
 * Ejecuta "php -l" sobre cada archivo .php de backend/, frontend/, config/ y tests/.
 *
 * @return int Código de salida (0 = OK).
 */

$root = dirname(__DIR__);
$dirs = ['backend', 'frontend', 'config', 'tests'];
$php  = PHP_BINARY;
$errors = [];
$total = 0;

foreach ($dirs as $dir) {
    $base = $root . DIRECTORY_SEPARATOR . $dir;
    if (!is_dir($base)) {
        continue;
    }
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($it as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }
        $total++;
        $cmd = escapeshellarg($php) . ' -l ' . escapeshellarg($file->getPathname()) . ' 2>&1';
        $out = shell_exec($cmd);
        if (stripos($out, 'No syntax errors') === false) {
            $errors[] = $file->getPathname() . PHP_EOL . '    ' . trim($out);
        }
    }
}

echo "Lint: {$total} archivo(s) revisado(s)." . PHP_EOL;
if ($errors) {
    echo PHP_EOL . "Errores de sintaxis:" . PHP_EOL . implode(PHP_EOL, $errors) . PHP_EOL;
    exit(1);
}
echo "Sin errores de sintaxis." . PHP_EOL;
exit(0);
