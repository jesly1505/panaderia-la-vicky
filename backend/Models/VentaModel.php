<?php
namespace App\Models;

use PDO;
use App\Core\Money;
use App\Utils\InventoryLogic;

class VentaModel {
    private $conn;
    private $inventoryLogic;
    private $productoModel;

    public function __construct(PDO $db, InventoryLogic $inventoryLogic, ProductoModel $productoModel) {
        $this->conn = $db;
        $this->inventoryLogic = $inventoryLogic;
        $this->productoModel = $productoModel;
    }

    public function readAll(string $filterType = 'all', string $startDate = '', string $endDate = '') {
        $dateCondition = \App\Helpers\DateFilterHelper::getSqlCondition('v.fecha_venta', $filterType, $startDate, $endDate);
        $query = "SELECT v.*, p.estado as estado_pedido, u.nombre as vendedor 
                  FROM ventas v 
                  LEFT JOIN pedidos p ON v.pedido_id = p.id
                  LEFT JOIN usuarios u ON v.usuario_id = u.id
                  WHERE $dateCondition
                  ORDER BY v.fecha_venta DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createDirecta($data, ?int $usuario_id = null) {
        try {
            $this->conn->beginTransaction();

            $subtotal = Money::round($data['subtotal'] ?? 0);
            $impuestos = Money::round($data['impuestos'] ?? 0);
            $descuento = Money::round($data['descuento'] ?? 0);
            $total = Money::round($data['total'] ?? 0);
            $detalles = $data['detalles'] ?? [];
            $pagos = $data['pagos'] ?? [];

            // Calcular ganancias reales (ventas - costos de insumos)
            $costoTotal = 0;
            foreach ($detalles as $detalle) {
                $costoTotal += $this->productoModel->getCost($detalle['producto_id']) * $detalle['cantidad'];
            }
            $ganancias = Money::round($total - $costoTotal);

            // 1. Insertar Venta
            $query = "INSERT INTO ventas (pedido_id, subtotal, impuestos, descuento, total, ganancias, estado, usuario_id) 
                      VALUES (NULL, :subtotal, :impuestos, :descuento, :total, :ganancias, 'completado', :usuario_id)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":subtotal", $subtotal);
            $stmt->bindParam(":impuestos", $impuestos);
            $stmt->bindParam(":descuento", $descuento);
            $stmt->bindParam(":total", $total);
            $stmt->bindParam(":ganancias", $ganancias);
            $stmt->bindParam(":usuario_id", $usuario_id);
            $stmt->execute();
            
            $venta_id = $this->conn->lastInsertId();

            // 2. Insertar Detalle Venta
            foreach ($detalles as $detalle) {
                $qd = "INSERT INTO detalle_venta (venta_id, producto_id, cantidad, precio_unitario, descuento, subtotal)
                       VALUES (:venta_id, :prod_id, :cant, :precio, :desc, :subtotal)";
                $stmtD = $this->conn->prepare($qd);
                $d_precio = Money::round($detalle['precio_unitario'] ?? $detalle['precio'] ?? 0);
                $d_desc = Money::round($detalle['descuento'] ?? 0);
                $d_subtotal = Money::round($detalle['cantidad'] * $d_precio - $d_desc);

                $stmtD->bindParam(":venta_id", $venta_id);
                $stmtD->bindParam(":prod_id", $detalle['producto_id']);
                $stmtD->bindParam(":cant", $detalle['cantidad']);
                $stmtD->bindParam(":precio", $d_precio);
                $stmtD->bindParam(":desc", $d_desc);
                $stmtD->bindParam(":subtotal", $d_subtotal);
                $stmtD->execute();
            }

            // 3. Insertar Pagos (Múltiples métodos)
            foreach ($pagos as $pago) {
                $qp = "INSERT INTO pagos (venta_id, monto, metodo_pago, estado, referencia) 
                       VALUES (:venta_id, :monto, :metodo, 'completado', :ref)";
                $stmtP = $this->conn->prepare($qp);
                $p_monto = Money::round($pago['monto']);
                $stmtP->bindParam(":venta_id", $venta_id);
                $stmtP->bindParam(":monto", $p_monto);
                $stmtP->bindParam(":metodo", $pago['metodo']);
                $stmtP->bindValue(":ref", $pago['referencia'] ?? null);
                $stmtP->execute();
            }

            // 4. Descontar inventario
            $this->inventoryLogic->descontarVarios($detalles);

            $this->conn->commit();
            return $venta_id;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            error_log("Error en createDirecta: " . $e->getMessage());
            return false;
        }
    }

    public function cancelarVenta($id) {
        try {
            $this->conn->beginTransaction();

            // 1. Obtener datos de la venta
            $qV = "SELECT estado FROM ventas WHERE id = :id";
            $stmtV = $this->conn->prepare($qV);
            $stmtV->bindParam(":id", $id);
            $stmtV->execute();
            $venta = $stmtV->fetch(PDO::FETCH_ASSOC);

            if (!$venta || $venta['estado'] === 'cancelado') {
                throw new Exception("La venta no existe o ya está cancelada.");
            }

            // 2. Obtener detalles para revertir stock
            $qD = "SELECT producto_id, cantidad FROM detalle_venta WHERE venta_id = :id";
            $stmtD = $this->conn->prepare($qD);
            $stmtD->bindParam(":id", $id);
            $stmtD->execute();
            $detalles = $stmtD->fetchAll(PDO::FETCH_ASSOC);

            // 3. Revertir Inventario
            $this->inventoryLogic->revertirVarios($detalles);

            // 4. Actualizar estado de la venta
            $qU = "UPDATE ventas SET estado = 'cancelado' WHERE id = :id";
            $stmtU = $this->conn->prepare($qU);
            $stmtU->bindParam(":id", $id);
            $stmtU->execute();

            // 5. Opcional: Actualizar estado de los pagos asociados
            $qP = "UPDATE pagos SET estado = 'fallido' WHERE venta_id = :id";
            $stmtP = $this->conn->prepare($qP);
            $stmtP->bindParam(":id", $id);
            $stmtP->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            error_log("Error al cancelar venta: " . $e->getMessage());
            return false;
        }
    }

    public function createFromPedido($pedido_id, $data = [], ?int $usuario_id = null) {
        try {
            $this->conn->beginTransaction();

            // 1. Obtener datos del pedido
            $qP = "SELECT total FROM pedidos WHERE id = :id";
            $stmtP = $this->conn->prepare($qP);
            $stmtP->bindParam(":id", $pedido_id);
            $stmtP->execute();
            $pedido = $stmtP->fetch();

            // 2. Obtener detalles del pedido
            $qD = "SELECT producto_id, cantidad, precio_unitario FROM detalle_pedido WHERE pedido_id = :id";
            $stmtD = $this->conn->prepare($qD);
            $stmtD->bindParam(":id", $pedido_id);
            $stmtD->execute();
            $detalles = $stmtD->fetchAll();

            $costoTotal = 0;
            foreach ($detalles as $detalle) {
                $costoTotal += $this->productoModel->getCost($detalle['producto_id']) * $detalle['cantidad'];
            }
            $pedido_total = Money::round($pedido['total']);
            $ganancias = Money::round($pedido_total - $costoTotal);

            // 3. Registrar Venta (simplificada para pedidos previa implementación total)
            $query = "INSERT INTO ventas (pedido_id, subtotal, impuestos, descuento, total, ganancias, estado, usuario_id) 
                      VALUES (:pedido_id, :total, 0, 0, :total, :ganancias, 'completado', :usuario_id)";
            $stmtV = $this->conn->prepare($query);
            $stmtV->bindParam(":pedido_id", $pedido_id);
            $stmtV->bindParam(":total", $pedido_total);
            $stmtV->bindParam(":ganancias", $ganancias);
            $stmtV->bindParam(":usuario_id", $usuario_id);
            $stmtV->execute();
            $venta_id = $this->conn->lastInsertId();

            // Detalle venta desde pedido
            foreach ($detalles as $detalle) {
                $qd = "INSERT INTO detalle_venta (venta_id, producto_id, cantidad, precio_unitario, subtotal)
                       VALUES (:venta_id, :prod_id, :cant, :precio, :sub)";
                $sd = $this->conn->prepare($qd);
                $precio = Money::round($detalle['precio_unitario']);
                $sub = Money::round($detalle['cantidad'] * $precio);
                $sd->bindParam(":venta_id", $venta_id);
                $sd->bindParam(":prod_id", $detalle['producto_id']);
                $sd->bindParam(":cant", $detalle['cantidad']);
                $sd->bindParam(":precio", $precio);
                $sd->bindParam(":sub", $sub);
                $sd->execute();
            }

            // 4. Pago único por el total
            $metodo = $data['metodo_pago'] ?? 'efectivo';
            $qp = "INSERT INTO pagos (venta_id, monto, metodo_pago, estado) 
                   VALUES (:venta_id, :monto, :metodo, 'completado')";
            $sp = $this->conn->prepare($qp);
            $sp->bindParam(":venta_id", $venta_id);
            $sp->bindParam(":monto", $pedido_total);
            $sp->bindParam(":metodo", $metodo);
            $sp->execute();

            // 5. Descontar Inventario
            $this->inventoryLogic->descontarVarios($detalles);

            $this->conn->commit();
            return $venta_id;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            error_log("Error en createFromPedido: " . $e->getMessage());
            return false;
        }
    }

    public function getSalesHistory($limit = 50) {
        $query = "SELECT v.*, u.nombre as vendedor 
                  FROM ventas v 
                  LEFT JOIN usuarios u ON v.usuario_id = u.id 
                  ORDER BY v.fecha_venta DESC LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopProducts($limit = 5) {
        $query = "SELECT p.nombre, SUM(dv.cantidad) as total_vendido 
                  FROM detalle_venta dv 
                  JOIN productos p ON dv.producto_id = p.id 
                  WHERE (SELECT estado FROM ventas WHERE id = dv.venta_id) != 'cancelado'
                  GROUP BY dv.producto_id 
                  ORDER BY total_vendido DESC LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRevenueChartData() {
        $query = "SELECT DATE(fecha_venta) as fecha, SUM(total) as total_dia 
                  FROM ventas 
                  WHERE estado != 'cancelado' AND fecha_venta >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                  GROUP BY DATE(fecha_venta) 
                  ORDER BY fecha ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVentaConDetalles($id) {
        $query = "SELECT v.*, u.nombre as vendedor, c.nombre as cliente_nombre, c.email as cliente_email, c.direccion as cliente_direccion 
                  FROM ventas v 
                  LEFT JOIN usuarios u ON v.usuario_id = u.id 
                  LEFT JOIN pedidos p ON v.pedido_id = p.id 
                  LEFT JOIN clientes c ON p.cliente_id = c.id 
                  WHERE v.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $venta = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($venta) {
            $qd = "SELECT dv.*, p.nombre as producto_nombre 
                   FROM detalle_venta dv 
                   JOIN productos p ON dv.producto_id = p.id 
                   WHERE dv.venta_id = :id";
            $stmtD = $this->conn->prepare($qd);
            $stmtD->bindParam(":id", $id);
            $stmtD->execute();
            $venta['detalles'] = $stmtD->fetchAll(PDO::FETCH_ASSOC);

            $qp = "SELECT * FROM pagos WHERE venta_id = :id";
            $stmtP = $this->conn->prepare($qp);
            $stmtP->bindParam(":id", $id);
            $stmtP->execute();
            $venta['pagos'] = $stmtP->fetchAll(PDO::FETCH_ASSOC);
        }
        return $venta;
    }
}
?>
