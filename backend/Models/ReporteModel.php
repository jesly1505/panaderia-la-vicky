<?php
require_once __DIR__ . '/../../config/database.php';

class ReporteModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /** Total de ventas + ganancias de la semana actual (ISO week) */
    public function getVentasSemanales() {
        $query = "SELECT 
                    COALESCE(SUM(v.total), 0) AS total_ventas,
                    COALESCE(SUM(v.total - COALESCE(v.descuento,0) - COALESCE(costos.costo_productos,0)), 0) AS total_ganancias
                  FROM ventas v
                  LEFT JOIN (
                      SELECT dv.venta_id, SUM(dv.cantidad * p.precio_costo) AS costo_productos
                      FROM detalle_venta dv
                      JOIN productos p ON dv.producto_id = p.id
                      GROUP BY dv.venta_id
                  ) costos ON costos.venta_id = v.id
                  WHERE YEARWEEK(v.fecha_venta, 1) = YEARWEEK(CURDATE(), 1)
                    AND v.estado = 'completado'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /** Total de ventas + ganancias del mes actual */
    public function getVentasMensuales() {
        $query = "SELECT 
                    COALESCE(SUM(v.total), 0) AS total_ventas,
                    COALESCE(SUM(v.total - COALESCE(v.descuento,0) - COALESCE(costos.costo_productos,0)), 0) AS total_ganancias
                  FROM ventas v
                  LEFT JOIN (
                      SELECT dv.venta_id, SUM(dv.cantidad * p.precio_costo) AS costo_productos
                      FROM detalle_venta dv
                      JOIN productos p ON dv.producto_id = p.id
                      GROUP BY dv.venta_id
                  ) costos ON costos.venta_id = v.id
                  WHERE MONTH(v.fecha_venta) = MONTH(CURDATE())
                    AND YEAR(v.fecha_venta) = YEAR(CURDATE())
                    AND v.estado = 'completado'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
