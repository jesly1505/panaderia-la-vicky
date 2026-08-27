<?php
namespace App\Core;

/**
 * Utilidad para protección CSRF basada en sesiones.
 * Genera tokens aleatorios y los valida en requests que modifican datos.
 */
class CsrfToken {
    private const TOKEN_LENGTH = 32;
    private const SESSION_KEY = 'csrf_token';

    /**
     * Genera un token CSRF y lo almacena en la sesión.
     * @return string Token generado (hex).
     */
    public static function generate(): string {
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        $_SESSION[self::SESSION_KEY] = $token;
        return $token;
    }

    /**
     * Retorna el token CSRF actual de la sesión (lo genera si no existe).
     * @return string Token actual.
     */
    public static function get(): string {
        if (empty($_SESSION[self::SESSION_KEY])) {
            return self::generate();
        }
        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Valida un token CSRF contra el almacenado en sesión.
     * @param string $token Token a validar.
     * @return bool true si es válido.
     */
    public static function validate(?string $token): bool {
        if ($token === null || $token === '') {
            return false;
        }
        $stored = $_SESSION[self::SESSION_KEY] ?? '';
        if ($stored === '') {
            return false;
        }
        return hash_equals($stored, $token);
    }
}
