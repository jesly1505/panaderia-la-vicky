<?php
namespace App\Core;

/**
 * Validaciones de entrada del lado del servidor.
 * Cada método devuelve un mensaje de error (string) o null si la validación pasa.
 * Con firstError() se obtiene el primer error de un conjunto.
 */
class Validator {

    /** Lee un campo de $_POST o del body JSON. */
    public static function input(string $key, $default = '') {
        static $json = null;
        if ($json === null) {
            $raw = file_get_contents('php://input');
            $json = json_decode($raw, true) ?: [];
        }
        if (array_key_exists($key, $json)) return $json[$key];
        if (array_key_exists($key, $_POST)) return $_POST[$key];
        return $default;
    }

    public static function required($value, string $field): ?string {
        return empty(trim((string)$value)) ? "El campo {$field} es obligatorio." : null;
    }

    public static function numeric($value, string $field): ?string {
        return is_numeric($value) ? null : "El campo {$field} debe ser numérico.";
    }

    public static function integer($value, string $field): ?string {
        $ok = filter_var($value, FILTER_VALIDATE_INT) !== false
            || (is_numeric($value) && (float)$value == (int)$value);
        return $ok ? null : "El campo {$field} debe ser un número entero.";
    }

    /** Mayor estricto que $min. */
    public static function greaterThan($value, float $min, string $field): ?string {
        if (!is_numeric($value) || (float)$value <= $min) {
            return "El campo {$field} debe ser mayor que {$min}.";
        }
        return null;
    }

    /** Mayor o igual que $min. */
    public static function min($value, float $min, string $field): ?string {
        if (!is_numeric($value) || (float)$value < $min) {
            return "El campo {$field} debe ser mayor o igual que {$min}.";
        }
        return null;
    }

    /** Menor o igual que $max. */
    public static function max($value, float $max, string $field): ?string {
        if (!is_numeric($value) || (float)$value > $max) {
            return "El campo {$field} debe ser menor o igual que {$max}.";
        }
        return null;
    }

    public static function email($value, string $field): ?string {
        if ($value === null || $value === '') {
            return null;
        }
        return filter_var($value, FILTER_VALIDATE_EMAIL) ? null : "El campo {$field} no es un correo válido.";
    }

    public static function length($value, int $max, string $field, int $min = 1): ?string {
        $len = mb_strlen((string)$value);
        if ($len < $min || $len > $max) {
            return "El campo {$field} debe tener entre {$min} y {$max} caracteres.";
        }
        return null;
    }

    public static function inList($value, array $allowed, string $field): ?string {
        return in_array($value, $allowed, true) ? null : "El campo {$field} contiene un valor no permitido.";
    }

    /** Fecha con formato YYYY-MM-DD o YYYY-MM-DD HH:MM:SS (vacío permitido). */
    public static function date($value, string $field): ?string {
        if ($value === null || $value === '') {
            return null;
        }
        $d = \DateTime::createFromFormat('Y-m-d', $value);
        if ($d && $d->format('Y-m-d') === $value) return null;
        $d2 = \DateTime::createFromFormat('Y-m-d H:i:s', $value);
        if ($d2 && $d2->format('Y-m-d H:i:s') === $value) return null;
        return "El campo {$field} debe tener formato YYYY-MM-DD o YYYY-MM-DD HH:MM:SS.";
    }

    /** Devuelve el primer error no nulo, o null si todas las validaciones pasan. */
    public static function firstError(array $errors): ?string {
        foreach ($errors as $error) {
            if ($error !== null) {
                return $error;
            }
        }
        return null;
    }
}
