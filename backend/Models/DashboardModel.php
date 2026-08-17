<?php
namespace App\Models;

use App\Helpers\DateFilterHelper;
use PDO;

class DashboardModel {
    private PDO $conn;

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    public function getVentasHoy(string $filterType = 'today', string $startDate = '', string $endDate = ''): float {
        $dateCondition = DateFilterHelper::getSqlCondition('fecha_venta', $filterType, $startDate, $endDate);
        $query = "SELECT SUM(total) as total_ventas FROM ventas WHERE $dateCondition AND estado != 'cancelado'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row && $row['total_ventas'] ? (float)$row['total_ventas'] : 0.0;
    }

    public function getGananciasHoy(string $filterType = 'today', string $startDate = '', string $endDate = ''): float {
        $dateCondition = DateFilterHelper::getSqlCondition('fecha_venta', $filterType, $startDate, $endDate);
        $query = "SELECT SUM(ganancias) as total_ganas FROM ventas WHERE $dateCondition AND estado != 'cancelado'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row && $row['total_ganas'] ? (float)$row['total_ganas'] : 0.0;
    }

    public function getPedidosPendientes(string $filterType = 'all', string $startDate = '', string $endDate = ''): int {
        $dateCondition = DateFilterHelper::getSqlCondition('fecha_pedido', $filterType, $startDate, $endDate);
        $query = "SELECT COUNT(*) as pendientes FROM pedidos WHERE estado = 'pendiente' AND eliminado = false AND $dateCondition";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['pendientes'] ?? 0);
    }

    public function getProductosCatalogo(): int {
        $query = "SELECT COUNT(*) as total_productos FROM productos WHERE eliminado = false";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total_productos'] ?? 0);
    }

    public function getClientesRegistrados(): int {
        $query = "SELECT COUNT(*) as total_clientes FROM clientes WHERE eliminado = false";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total_clientes'] ?? 0);
    }

    public function getLastPedidos(int $limit = 10, string $filterType = 'all', string $startDate = '', string $endDate = ''): array {
        $dateCondition = DateFilterHelper::getSqlCondition('p.fecha_pedido', $filterType, $startDate, $endDate);
        $query = "SELECT p.*, c.nombre as cliente_nombre 
                  FROM pedidos p 
                  LEFT JOIN clientes c ON p.cliente_id = c.id 
                  WHERE p.estado = 'pendiente' AND p.eliminado = false AND $dateCondition
                  ORDER BY p.fecha_pedido DESC 
                  LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStockAlerts(): array {
        $alerts = [];

        // Productos con bajo stock
        $qP = "SELECT 'producto' as tipo, nombre, stock_actual as stock FROM productos WHERE stock_actual <= stock_minimo AND eliminado = false";
        $sP = $this->conn->prepare($qP);
        $sP->execute();
        $alerts = array_merge($alerts, $sP->fetchAll(PDO::FETCH_ASSOC));

        // Insumos con bajo stock
        $qI = "SELECT 'insumo' as tipo, nombre, stock_actual as stock FROM insumos WHERE stock_actual <= stock_minimo AND visible = 1 AND eliminado = false";
        $sI = $this->conn->prepare($qI);
        $sI->execute();
        $alerts = array_merge($alerts, $sI->fetchAll(PDO::FETCH_ASSOC));

        return $alerts;
    }

    public function getKpisDinamicos(string $filterType = 'today', string $startDate = '', string $endDate = ''): array {
        $condBitacora = DateFilterHelper::getSqlCondition('fecha_hora', $filterType, $startDate, $endDate);
        $condIncidencias = DateFilterHelper::getSqlCondition('fecha_reporte', $filterType, $startDate, $endDate);
        $condProduccion = DateFilterHelper::getSqlCondition('fecha', $filterType, $startDate, $endDate);
        $condVentas = DateFilterHelper::getSqlCondition('fecha_venta', $filterType, $startDate, $endDate);

        $kpis = [
            'eventos' => 0,
            'usuarios_activos' => 0,
            'errores' => 0,
            'ventas_realizadas' => 0,
            'produccion_registrada' => 0
        ];

        try {
            // 1. Cantidad de eventos en bitácora
            $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM bitacora_sistema WHERE $condBitacora");
            $stmt->execute();
            $kpis['eventos'] = (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

            // 2. Usuarios activos
            $stmt = $this->conn->prepare("SELECT COUNT(DISTINCT usuario_id) as total FROM bitacora_sistema WHERE $condBitacora AND usuario_id IS NOT NULL");
            $stmt->execute();
            $kpis['usuarios_activos'] = (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

            // 3. Incidencias
            $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM incidencias WHERE $condIncidencias");
            $stmt->execute();
            $kpis['errores'] = (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

            // 4. Ventas realizadas
            $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM ventas WHERE $condVentas AND estado != 'cancelado'");
            $stmt->execute();
            $kpis['ventas_realizadas'] = (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

            // 5. Producción registrada
            $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM producciones WHERE $condProduccion");
            $stmt->execute();
            $kpis['produccion_registrada'] = (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
        } catch (\Throwable $e) {
            // Silencioso si alguna tabla auxiliar aún no está disponible
        }

        return $kpis;
    }

    public function getResumen(string $filterType = 'today', string $startDate = '', string $endDate = ''): array {
        return [
            'ventas_hoy'           => $this->getVentasHoy($filterType, $startDate, $endDate),
            'ganancias_hoy'        => $this->getGananciasHoy($filterType, $startDate, $endDate),
            'pedidos_pendientes'   => $this->getPedidosPendientes($filterType, $startDate, $endDate),
            'productos_catalogo'   => $this->getProductosCatalogo(),
            'clientes_registrados' => $this->getClientesRegistrados(),
            'ultimos_pedidos'      => $this->getLastPedidos(10, $filterType, $startDate, $endDate),
            'alertas_stock'        => $this->getStockAlerts(),
            'kpis_dinamicos'       => $this->getKpisDinamicos($filterType, $startDate, $endDate),
        ];
    }
}
