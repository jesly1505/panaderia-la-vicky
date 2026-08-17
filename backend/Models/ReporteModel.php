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
                    COALESCE(SUM(v.total), false) AS total_ventas,
                    COALESCE(SUM(v.total - COALESCE(v.descuento, false) - COALESCE(" . self::costoVenta() . ", false)), false) AS total_ganancias
                  FROM ventas v
                  WHERE YEARWEEK(v.fecha_venta, 1) = YEARWEEK(CURDATE(), 1)
                    AND v.estado != 'cancelado'";
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
                    COALESCE(SUM(v.total), false) AS total_ventas,
                    COALESCE(SUM(v.total - COALESCE(v.descuento, false) - COALESCE(" . self::costoVenta() . ", false)), false) AS total_ganancias
                  FROM ventas v
                  WHERE MONTH(v.fecha_venta) = MONTH(CURDATE())
                    AND YEAR(v.fecha_venta) = YEAR(CURDATE())
                    AND v.estado != 'cancelado'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            'total_ventas'    => round((float)$row['total_ventas'], 2),
            'total_ganancias' => round((float)$row['total_ganancias'], 2),
        ];
    }

    /**
     * Estadísticas consolidadas para el módulo de reportes (KPIs, Ventas por día, Categorías, Top Productos)
     */
    public function getVentasStats($startDate, $endDate) {
        $where = "WHERE v.estado != 'cancelado'";
        $params = [];

        if (!empty($startDate) && !empty($endDate)) {
            $where .= " AND DATE(v.fecha_venta) BETWEEN :start AND :end";
            $params[':start'] = $startDate;
            $params[':end'] = $endDate;
        }

        // 1. Resumen General
        $qGeneral = "SELECT 
                        COALESCE(SUM(v.total), false) as total_ventas,
                        COALESCE(SUM(" . self::costoVenta() . "), false) as total_costos,
                        COALESCE(SUM(v.total - COALESCE(v.descuento, false) - COALESCE(" . self::costoVenta() . ", false)), false) as utilidad_neta
                     FROM ventas v $where";
        $stmt = $this->conn->prepare($qGeneral);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        $general = $stmt->fetch(PDO::FETCH_ASSOC);

        $totalVentas = (float)$general['total_ventas'];
        $totalCostos = (float)$general['total_costos'];
        $utilidadNeta = (float)$general['utilidad_neta'];
        $roi = $totalCostos > 0 ? (($utilidadNeta / $totalCostos) * 100) : 0;

        // 2. Ventas por Día (Gráfico)
        $qDias = "SELECT DATE(v.fecha_venta) as fecha, SUM(v.total) as total 
                  FROM ventas v $where 
                  GROUP BY DATE(v.fecha_venta) 
                  ORDER BY fecha ASC";
        $stmtDias = $this->conn->prepare($qDias);
        foreach ($params as $k => $v) $stmtDias->bindValue($k, $v);
        $stmtDias->execute();
        $ventasPorDia = $stmtDias->fetchAll(PDO::FETCH_ASSOC);

        // 3. Top Productos Vendidos
        $qTop = "SELECT p.nombre, SUM(dv.cantidad) as total_cantidad, SUM(dv.subtotal) as total_recaudado
                 FROM detalle_venta dv
                 JOIN productos p ON dv.producto_id = p.id
                 JOIN ventas v ON dv.venta_id = v.id
                 $where
                 GROUP BY p.id
                 ORDER BY total_cantidad DESC
                 LIMIT 5";
        $stmtTop = $this->conn->prepare($qTop);
        foreach ($params as $k => $v) $stmtTop->bindValue($k, $v);
        $stmtTop->execute();
        $topProductos = $stmtTop->fetchAll(PDO::FETCH_ASSOC);

        // 4. Ventas por Categoría
        $qCat = "SELECT p.categoria, SUM(dv.subtotal) as total
                 FROM detalle_venta dv
                 JOIN productos p ON dv.producto_id = p.id
                 JOIN ventas v ON dv.venta_id = v.id
                 $where
                 GROUP BY p.categoria";
        $stmtCat = $this->conn->prepare($qCat);
        foreach ($params as $k => $v) $stmtCat->bindValue($k, $v);
        $stmtCat->execute();
        $categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

        return [
            'total_ventas' => $totalVentas,
            'total_costos' => $totalCostos,
            'utilidad_neta' => $utilidadNeta,
            'roi' => round($roi, 2),
            'ventas_por_dia' => $ventasPorDia,
            'top_productos' => $topProductos,
            'categorias' => $categorias
        ];
    }

    // --- Exportaciones CSV ---
    public function getExportVentas($startDate, $endDate) {
        $where = "WHERE v.estado != 'cancelado'";
        $params = [];
        if (!empty($startDate) && !empty($endDate)) {
            $where .= " AND DATE(v.fecha_venta) BETWEEN :start AND :end";
            $params[':start'] = $startDate;
            $params[':end'] = $endDate;
        }

        $query = "SELECT v.id, v.fecha_venta, c.nombre as cliente, u.nombre as vendedor, 
                         v.tipo_pago, v.total, v.ganancias, v.estado 
                  FROM ventas v
                  LEFT JOIN clientes c ON v.cliente_id = c.id
                  LEFT JOIN usuarios u ON v.usuario_id = u.id
                  $where ORDER BY v.fecha_venta DESC";
        $stmt = $this->conn->prepare($query);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getExportInsumos() {
        $query = "SELECT id, nombre, unidad_medida, stock_actual, stock_minimo, precio_costo 
                  FROM insumos WHERE visible = 1 AND eliminado = false ORDER BY nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getExportProductos() {
        $query = "SELECT id, nombre, categoria, precio_venta, costo_produccion, stock_actual, stock_minimo 
                  FROM productos WHERE eliminado = false ORDER BY nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getExportGastos($startDate, $endDate) {
        $where = "WHERE eliminado = 0";
        $params = [];
        if (!empty($startDate) && !empty($endDate)) {
            $where .= " AND DATE(fecha) BETWEEN :start AND :end";
            $params[':start'] = $startDate;
            $params[':end'] = $endDate;
        }

        $query = "SELECT id, fecha, categoria, monto, descripcion FROM gastos $where ORDER BY fecha DESC";
        $stmt = $this->conn->prepare($query);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
