<?php
namespace App\Core;

/**
 * Utilidades monetarias: redondeo a 2 decimales en los puntos de entrada
 * para evitar acumulación de errores de punto flotante.
 */
class Money {
    public static function round($value): float {
        return round((float)$value, 2);
    }
}
