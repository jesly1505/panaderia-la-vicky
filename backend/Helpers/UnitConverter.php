<?php

class UnitConverter {
    // Definimos las unidades base y sus multiplicadores hacia una unidad estándar interna.
    // Para Masa, el estándar interno será el Gramo (g).
    // Para Volumen, el estándar interno será el Mililitro (ml).

    private static $massUnits = [
        'Kg' => 1000,
        'Gramos' => 1,
        'Libras' => 453.592,    // 1 lb = 453.592 g
        'Onzas' => 28.3495      // 1 oz = 28.3495 g
    ];

    private static $volumeUnits = [
        'Litros' => 1000,
        'Mililitros' => 1
    ];

    private static $unitTypeMapping = [
        'Kg' => 'mass',
        'Gramos' => 'mass',
        'Libras' => 'mass',
        'Onzas' => 'mass',
        'Litros' => 'volume',
        'Mililitros' => 'volume',
        'Unidades' => 'units'
    ];

    /**
     * Convierte una cantidad de su unidad de origen a una unidad base.
     * 
     * @param float  $amount      La cantidad ingresada.
     * @param string $sourceUnit  Unidad de origen (Ej. 'Gramos').
     * @param string $targetUnit  Unidad base del insumo en el inventario (Ej. 'Kg').
     * 
     * @return float Cantidad convertida.
     * @throws Exception Si las unidades son incompatibles de convertir.
     */
    public static function convert($amount, $sourceUnit, $targetUnit) {
        $sourceUnit = self::normalizeUnit($sourceUnit);
        $targetUnit = self::normalizeUnit($targetUnit);

        // Si son iguales, no hay conversión
        if ($sourceUnit === $targetUnit) {
            return (float)$amount;
        }

        // Obtener la familia a la que pertenecen
        $sourceType = self::$unitTypeMapping[$sourceUnit] ?? null;
        $targetType = self::$unitTypeMapping[$targetUnit] ?? null;

        if (!$sourceType || !$targetType) {
            throw new Exception("Unidad desconocida: '$sourceUnit' o '$targetUnit'.");
        }

        if ($sourceType !== $targetType) {
            throw new Exception("Conversión incompatible entre '$sourceUnit' ($sourceType) y '$targetUnit' ($targetType).");
        }

        // Conversión
        if ($sourceType === 'mass') {
            $valueInGrams = $amount * self::$massUnits[$sourceUnit];
            $finalValue = $valueInGrams / self::$massUnits[$targetUnit];
            return round($finalValue, 4); // Redondear a 4 decimales
        }

        if ($sourceType === 'volume') {
            $valueInMl = $amount * self::$volumeUnits[$sourceUnit];
            $finalValue = $valueInMl / self::$volumeUnits[$targetUnit];
            return round($finalValue, 4);
        }

        if ($sourceType === 'units') {
            return (float)$amount; // 'Unidades' a 'Unidades' siempre es lo mismo
        }

        throw new Exception("Error interno de conversión.");
    }

    private static function normalizeUnit($unit) {
        // Mapeo flexible para errores de usuario
        $map = [
            'kg' => 'Kg',
            'kilo' => 'Kg',
            'kilos' => 'Kg',
            'kilogramo' => 'Kg',
            'kilogramos' => 'Kg',
            'g' => 'Gramos',
            'gramo' => 'Gramos',
            'gramos' => 'Gramos',
            'lb' => 'Libras',
            'libra' => 'Libras',
            'libras' => 'Libras',
            'oz' => 'Onzas',
            'onza' => 'Onzas',
            'onzas' => 'Onzas',
            'l' => 'Litros',
            'litro' => 'Litros',
            'litros' => 'Litros',
            'ml' => 'Mililitros',
            'mililitro' => 'Mililitros',
            'mililitros' => 'Mililitros',
            'und' => 'Unidades',
            'unidad' => 'Unidades',
            'unidades' => 'Unidades'
        ];

        $lowerUnit = strtolower(trim($unit));
        return $map[$lowerUnit] ?? $unit;
    }
}
?>
