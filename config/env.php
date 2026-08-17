<?php
/**
 * Cargador de variables de entorno (.env).
 * Lee .env en la raíz del proyecto (o .env.dev como respaldo) y
 * expone las variables en $_ENV y getenv(). No sobrescribe variables ya definidas.
 */

function load_env_file($file) {
    if (!is_readable($file)) {
        return;
    }

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || str_starts_with($line, ';')) {
            continue;
        }

        $pos = strpos($line, '=');
        if ($pos === false) {
            continue;
        }

        $key = trim(substr($line, 0, $pos));
        $value = trim(substr($line, $pos + 1));

        if ($key === '') {
            continue;
        }

        // Quitar comillas simples o dobles envolventes
        if (strlen($value) >= 2 && (($value[0] === '"' && $value[strlen($value) - 1] === '"')
            || ($value[0] === "'" && $value[strlen($value) - 1] === "'"))) {
            $value = substr($value, 1, -1);
        }

        // No sobrescribir variables de entorno reales del sistema
        if (getenv($key) !== false) {
            continue;
        }

        $_ENV[$key] = $value;
        putenv($key . '=' . $value);
    }
}

$projectRoot = dirname(__DIR__);

load_env_file($projectRoot . '/.env');
load_env_file($projectRoot . '/.env.dev');
