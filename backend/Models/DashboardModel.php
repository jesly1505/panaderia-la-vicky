<?php
require_once __DIR__ . '/../../config/database.php';

class DashboardModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getVentasHoy() {
        $query = "SELECT SUM(total) as total_ventas FROM ventas WHERE DATE(fecha_venta) = CURDATE()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total_ventas'] ? (float)$row['total_ventas'] : 0.00;
    }

    public function getPedidosPendientes() {
        $query = "SELECT COUNT(*) as pendientes FROM pedidos WHERE estado = 'pendiente'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$row['pendientes'];
    }

    public function getProductosCatalogo() {
        $query = "SELECT COUNT(*) as total_productos FROM productos";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$row['total_productos'];
    }

    public function getClientesRegistrados() {
        $query = "SELECT COUNT(*) as total_clientes FROM clientes";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$row['total_clientes'];
    }

    public function getLastPedidos($limit = 10) {
        $query = "SELECT p.*, c.nombre as cliente_nombre 
                  FROM pedidos p 
                  LEFT JOIN clientes c ON p.cliente_id = c.id 
                  WHERE p.estado = 'pendiente'
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
        $qP = "SELECT 'producto' as tipo, nombre, stock_actual as stock FROM productos WHERE stock_actual <= stock_minimo";
        $sP = $this->conn->prepare($qP);
        $sP->execute();
        $alerts = array_merge($alerts, $sP->fetchAll(PDO::FETCH_ASSOC));
 
        // Insumos con bajo stock
        $qI = "SELECT 'insumo' as tipo, nombre, stock_actual as stock FROM insumos WHERE stock_actual <= stock_minimo AND visible = 1";
        $sI = $this->conn->prepare($qI);
        $sI->execute();
        $alerts = array_merge($alerts, $sI->fetchAll(PDO::FETCH_ASSOC));
 
        return $alerts;
    }

    public function getResumen() {
        return [
            'ventas_hoy' => $this->getVentasHoy(),
            'pedidos_pendientes' => $this->getPedidosPendientes(),
            'productos_catalogo' => $this->getProductosCatalogo(),
            'clientes_registrados' => $this->getClientesRegistrados(),
            'ultimos_pedidos' => $this->getLastPedidos(10),
            'alertas_stock' => $this->getStockAlerts()
        ];
    }
}
?>
