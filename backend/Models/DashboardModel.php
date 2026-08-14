<?php
namespace App\Models;

use PDO;

class DashboardModel {
    private $conn;

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    /**
     * Indicadores en una sola consulta (evita N+1).
     * Las ventas canceladas se excluyen de "ventas de hoy".
     */
    public function getStats() {
        $query = "SELECT
                    COALESCE(SUM(CASE WHEN DATE(v.fecha_venta) = CURDATE() AND v.estado != 'cancelado' THEN v.total END), 0) AS ventas_hoy,
                    (SELECT COUNT(*) FROM pedidos WHERE estado = 'pendiente' AND deleted_at IS NULL) AS pedidos_pendientes,
                    (SELECT COUNT(*) FROM productos WHERE deleted_at IS NULL) AS productos_catalogo,
                    (SELECT COUNT(*) FROM clientes WHERE deleted_at IS NULL) AS clientes_registrados
                  FROM ventas v";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            'ventas_hoy'          => (float)$row['ventas_hoy'],
            'pedidos_pendientes'  => (int)$row['pedidos_pendientes'],
            'productos_catalogo'  => (int)$row['productos_catalogo'],
            'clientes_registrados'=> (int)$row['clientes_registrados'],
        ];
    }

    public function getLastPedidos($limit = 10) {
        $query = "SELECT p.*, c.nombre as cliente_nombre 
                  FROM pedidos p 
                  LEFT JOIN clientes c ON p.cliente_id = c.id 
                  WHERE p.estado = 'pendiente' AND p.deleted_at IS NULL
                  ORDER BY p.fecha_pedido DESC 
                  LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStockAlerts() {
        $alerts = [];

        // Productos con bajo stock
        $qP = "SELECT 'producto' as tipo, nombre, stock_actual as stock FROM productos WHERE stock_actual <= stock_minimo AND deleted_at IS NULL";
        $sP = $this->conn->prepare($qP);
        $sP->execute();
        $alerts = array_merge($alerts, $sP->fetchAll(PDO::FETCH_ASSOC));

        // Insumos con bajo stock
        $qI = "SELECT 'insumo' as tipo, nombre, stock_actual as stock FROM insumos WHERE stock_actual <= stock_minimo AND visible = 1 AND deleted_at IS NULL";
        $sI = $this->conn->prepare($qI);
        $sI->execute();
        $alerts = array_merge($alerts, $sI->fetchAll(PDO::FETCH_ASSOC));

        return $alerts;
    }

    public function getResumen() {
        return array_merge($this->getStats(), [
            'ultimos_pedidos' => $this->getLastPedidos(10),
            'alertas_stock'   => $this->getStockAlerts(),
        ]);
    }
}
