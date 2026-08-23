<?php
namespace App\Models;

use PDO;
use Exception;

class PedidoModel {
    private $conn;
    private $table_name = "pedidos";
    private ?VentaModel $ventaModel = null;

    public function __construct(PDO $db, ?VentaModel $ventaModel = null) {
        $this->conn = $db;
        $this->ventaModel = $ventaModel;
    }

    public function create($cliente_id, $usuario_id, $fecha_entrega, $hora_entrega, $productos, $total) {
        try {
            $this->conn->beginTransaction();

            $query = "INSERT INTO " . $this->table_name . " 
                      (cliente_id, usuario_id, estado, total, fecha_pedido, fecha_entrega, hora_entrega, eliminado) 
                      VALUES (:cliente_id, :usuario_id, 'pendiente', :total, NOW(), :fecha_entrega, :hora_entrega, false)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":cliente_id", $cliente_id);
            $stmt->bindParam(":usuario_id", $usuario_id);
            $stmt->bindParam(":total", $total);
            $stmt->bindParam(":fecha_entrega", $fecha_entrega);
            $stmt->bindParam(":hora_entrega", $hora_entrega);
            $stmt->execute();

            $pedido_id = $this->conn->lastInsertId();

            // Insertar detalles del pedido
            $queryDetalle = "INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio_unitario, subtotal) 
                             VALUES (:pedido_id, :producto_id, :cantidad, :precio_unitario, :subtotal)";
            $stmtDetalle = $this->conn->prepare($queryDetalle);

            foreach ($productos as $prod) {
                $subtotal = $prod['cantidad'] * ($prod['precio_unitario'] ?? $prod['precio'] ?? 0);
                $stmtDetalle->bindParam(":pedido_id", $pedido_id);
                $d_pid = $prod['producto_id'] ?? $prod['id'];
                $d_precio = $prod['precio_unitario'] ?? $prod['precio'];
                $stmtDetalle->bindParam(":producto_id", $d_pid);
                $stmtDetalle->bindParam(":cantidad", $prod['cantidad']);
                $stmtDetalle->bindParam(":precio_unitario", $d_precio);
                $stmtDetalle->bindParam(":subtotal", $subtotal);
                $stmtDetalle->execute();
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            return false;
        }
    }

    public function readAll() {
        $query = "SELECT p.*, c.nombre as cliente_nombre, u.nombre as vendedor,
                    (SELECT GROUP_CONCAT(CONCAT(pr.nombre, ' x', dp.cantidad) SEPARATOR ', ')
                     FROM detalle_pedido dp
                     JOIN productos pr ON dp.producto_id = pr.id
                     WHERE dp.pedido_id = p.id) as productos_resumen
                  FROM pedidos p 
                  LEFT JOIN clientes c ON p.cliente_id = c.id
                  LEFT JOIN usuarios u ON p.usuario_id = u.id
                  WHERE p.eliminado = false
                  ORDER BY p.fecha_pedido DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateEstado($pedido_id, $estado, $hora_real = null, ?int $usuario_id = null) {
        try {
            $this->conn->beginTransaction();

            if ($estado === 'entregado' && $hora_real) {
                $query = "UPDATE pedidos SET estado = :estado, hora_entrega_real = :hora_real WHERE id = :id AND eliminado = false";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":hora_real", $hora_real);
            } else {
                $query = "UPDATE pedidos SET estado = :estado WHERE id = :id AND eliminado = false";
                $stmt = $this->conn->prepare($query);
            }
            $stmt->bindParam(":estado", $estado);
            $stmt->bindParam(":id", $pedido_id);
            $stmt->execute();

            if ($estado === 'entregado') {
                if ($this->ventaModel) {
                    $res = $this->ventaModel->createFromPedido($pedido_id, [], $usuario_id);
                    if (!$res) throw new Exception("Error al registrar la venta desde el pedido.");
                }
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            return false;
        }
    }

    public function delete($id) {
        $query = "UPDATE pedidos SET eliminado = true, deleted_at = NOW() WHERE id = :id AND eliminado = false";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    public function update($id, $cliente_id, $fecha_entrega, $hora_entrega) {
        $query = "UPDATE {$this->table_name} SET cliente_id = :cliente_id, fecha_entrega = :fecha_entrega, hora_entrega = :hora_entrega WHERE id = :id AND eliminado = false";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":cliente_id", $cliente_id);
        $stmt->bindParam(":fecha_entrega", $fecha_entrega);
        $stmt->bindParam(":hora_entrega", $hora_entrega);
        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    public function getDetalles($pedido_id) {
        $query = "SELECT dp.*, p.nombre as producto_nombre 
                  FROM detalle_pedido dp 
                  JOIN productos p ON dp.producto_id = p.id 
                  WHERE dp.pedido_id = :pedido_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":pedido_id", $pedido_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
