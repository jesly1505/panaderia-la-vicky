<?php
namespace App\Models;

use PDO;
use App\Models\ProductoModel;

class ReporteModel {
    private $conn;

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    /**
     * Subconsulta de costo de venta, centralizada en ProductoModel.
     */
    private static function costoVenta(): string {
        return ProductoModel::COSTO_VENTA_SUBQUERY;
    }

    /** Total de ventas + ganancias de la semana actual (ISO week) */
    public function getVentasSemanales() {
        $query = "SELECT 
                    COALESCE(SUM(v.total), 0) AS total_ventas,
                    COALESCE(SUM(v.total - COALESCE(v.descuento,0) - COALESCE(" . self::costoVenta() . ",0)), 0) AS total_ganancias
                  FROM ventas v
                  WHERE YEARWEEK(v.fecha_venta, 1) = YEARWEEK(CURDATE(), 1)
                    AND v.estado = 'completado'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            'total_ventas'    => round((float)$row['total_ventas'], 2),
            'total_ganancias' => round((float)$row['total_ganancias'], 2),
        ];
    }

    /** Total de ventas + ganancias del mes actual */
    public function getVentasMensuales() {
        $query = "SELECT 
                    COALESCE(SUM(v.total), 0) AS total_ventas,
                    COALESCE(SUM(v.total - COALESCE(v.descuento,0) - COALESCE(" . self::costoVenta() . ",0)), 0) AS total_ganancias
                  FROM ventas v
                  WHERE MONTH(v.fecha_venta) = MONTH(CURDATE())
                    AND YEAR(v.fecha_venta) = YEAR(CURDATE())
                    AND v.estado = 'completado'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            'total_ventas'    => round((float)$row['total_ventas'], 2),
            'total_ganancias' => round((float)$row['total_ganancias'], 2),
        ];
    }
}
?>
