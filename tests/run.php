<?php
/**
 * Punto de entrada de "composer test".
 * Delega en PHPUnit (vendor) y propaga su código de salida.
 * Si PHPUnit no está instalado, avisa cómo instalarlo.
 */

$phpunit = __DIR__ . '/../vendor/phpunit/phpunit/phpunit';
if (is_file($phpunit)) {
    $cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($phpunit) . ' --colors=never';
    passthru($cmd, $ret);
    exit($ret);
}

fwrite(STDERR, "PHPUnit no está instalado. Ejecuta: composer install\n");
exit(2);
