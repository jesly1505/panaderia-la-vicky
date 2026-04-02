<?php
require_once __DIR__ . '/../../config/database.php';

class PedidoModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create($cliente_id, $usuario_id, $total, $detalles, $fecha_entrega = null, $hora_entrega = null) {
        try {
            $this->conn->beginTransaction();

            $query = "INSERT INTO pedidos (cliente_id, usuario_id, estado, total, fecha_entrega, hora_entrega) 
                      VALUES (:cliente_id, :usuario_id, 'pendiente', :total, :fecha_entrega, :hora_entrega)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":cliente_id", $cliente_id);
            $stmt->bindParam(":usuario_id", $usuario_id);
            $stmt->bindParam(":total", $total);
            $stmt->bindParam(":fecha_entrega", $fecha_entrega);
            $stmt->bindParam(":hora_entrega", $hora_entrega);
            $stmt->execute();
            
            $pedido_id = $this->conn->lastInsertId();

            foreach ($detalles as $detalle) {
                $qd = "INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio_unitario, subtotal)
                       VALUES (:pedido_id, :prod_id, :cant, :precio, :subtotal)";
                $stmtD = $this->conn->prepare($qd);
                $subtotal = $detalle['cantidad'] * $detalle['precio'];
                $stmtD->bindParam(":pedido_id", $pedido_id);
                $stmtD->bindParam(":prod_id", $detalle['producto_id']);
                $stmtD->bindParam(":cant", $detalle['cantidad']);
                $stmtD->bindParam(":precio", $detalle['precio']);
                $stmtD->bindParam(":subtotal", $subtotal);
                $stmtD->execute();
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function readAll() {
        $query = "SELECT p.*, c.nombre as cliente_nombre, u.nombre as vendedor 
                  FROM pedidos p 
                  LEFT JOIN clientes c ON p.cliente_id = c.id
                  LEFT JOIN usuarios u ON p.usuario_id = u.id
                  ORDER BY p.fecha_pedido DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateEstado($pedido_id, $estado, $hora_real = null) {
        try {
            $this->conn->beginTransaction();

            if ($estado === 'entregado' && $hora_real) {
                $query = "UPDATE pedidos SET estado = :estado, hora_entrega_real = :hora_real WHERE id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":hora_real", $hora_real);
            } else {
                $query = "UPDATE pedidos SET estado = :estado WHERE id = :id";
                $stmt = $this->conn->prepare($query);
            }
            $stmt->bindParam(":estado", $estado);
            $stmt->bindParam(":id", $pedido_id);
            $stmt->execute();

            if ($estado === 'entregado') {
                require_once __DIR__ . '/VentaModel.php';
                $ventaModel = new VentaModel();
                $res = $ventaModel->createFromPedido($pedido_id);
                if (!$res) throw new Exception("Error al registrar la venta desde el pedido.");
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            return false;
        }
    }

    public function delete($id) {
        $query = "DELETE FROM pedidos WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function getDetalles($pedido_id) {
        $query = "SELECT dp.*, p.nombre as producto_nombre 
                  FROM detalle_pedido dp 
                  JOIN productos p ON dp.producto_id = p.id 
                  WHERE dp.pedido_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $pedido_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>
