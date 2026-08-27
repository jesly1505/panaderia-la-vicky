<?php
namespace App\Models;

use PDO;
use Exception;

/**
 * Modelo de acceso a datos para la entidad Pedido.
 *
 * Encargado de ejecutar todas las consultas SQL relacionadas con pedidos,
 * incluyendo creación, lectura, actualización de estado, eliminación lógica
 * y obtención de detalles de productos asociados.
 */
class PedidoModel {
    private $conn;
    private $table_name = "pedidos";
    private ?VentaModel $ventaModel = null;

    /**
     * Inyección de dependencias del modelo.
     *
     * @param PDO         $db         Conexión PDO a la base de datos.
     * @param VentaModel|null $ventaModel Modelo de ventas opcional, usado para crear una venta al entregar un pedido.
     */
    public function __construct(PDO $db, ?VentaModel $ventaModel = null) {
        $this->conn = $db;
        $this->ventaModel = $ventaModel;
    }

    /**
     * Crea un nuevo pedido con su detalle de productos dentro de una transacción.
     *
     * @param int                  $cliente_id   ID del cliente que realiza el pedido.
     * @param int                  $usuario_id   ID del usuario vendedor que toma el pedido.
     * @param string               $fecha_entrega Fecha de entrega acordada (formato YYYY-MM-DD).
     * @param string               $hora_entrega  Hora de entrega acordada (formato HH:MM).
     * @param array<int, array{producto_id: int, cantidad: int, precio_unitario?: float}> $productos
     *                                            Array de productos con cantidad y precio unitario.
     * @param float                $total        Monto total del pedido.
     * @return bool true si el pedido se creó correctamente, false si ocurrió un error.
     */
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

    /**
     * Retorna la lista de pedidos no eliminados con el nombre del cliente,
     * del vendedor y un resumen de productos, con soporte de paginación y búsqueda.
     *
     * @param int|null    $limit  Cantidad máxima de registros por página, o null para traer todos.
     * @param int|null    $offset Desplazamiento para paginación, o null para traer todos.
     * @param string      $search Término de búsqueda por nombre de cliente o ID del pedido.
     * @return array<int, array<string, mixed>> Array de pedidos con datos asociados.
     */
    public function readAll($limit = null, $offset = null, $search = '') {
        $where = " p.eliminado = false ";
        if (!empty($search)) {
            $where .= " AND (c.nombre LIKE :search OR p.id LIKE :search2) ";
        }
        $query = "SELECT p.*, c.nombre as cliente_nombre, u.nombre as vendedor,
                    (SELECT GROUP_CONCAT(CONCAT(pr.nombre, ' x', dp.cantidad) SEPARATOR ', ')
                     FROM detalle_pedido dp
                     JOIN productos pr ON dp.producto_id = pr.id
                     WHERE dp.pedido_id = p.id) as productos_resumen
                  FROM pedidos p 
                  LEFT JOIN clientes c ON p.cliente_id = c.id
                  LEFT JOIN usuarios u ON p.usuario_id = u.id
                  WHERE $where
                  ORDER BY p.fecha_pedido DESC";
        if ($limit !== null && $offset !== null) {
            $query .= " LIMIT :limit OFFSET :offset";
        }
        $stmt = $this->conn->prepare($query);
        if (!empty($search)) {
            $term = "%" . $search . "%";
            $stmt->bindValue(':search', $term);
            $stmt->bindValue(':search2', $term);
        }
        if ($limit !== null && $offset !== null) {
            $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Cuenta el total de pedidos no eliminados que coinciden con el término de búsqueda.
     *
     * @param string $search Término de búsqueda por nombre de cliente o ID del pedido.
     * @return int Cantidad total de pedidos que coinciden.
     */
    public function countAll($search = '') {
        try {
            $where = " p.eliminado = false ";
            if (!empty($search)) {
                $where .= " AND (c.nombre LIKE :search OR p.id LIKE :search2) ";
            }
            $query = "SELECT COUNT(*) FROM " . $this->table_name . " p 
                      LEFT JOIN clientes c ON p.cliente_id = c.id
                      WHERE $where";
            $stmt = $this->conn->prepare($query);
            if (!empty($search)) {
                $term = "%" . $search . "%";
                $stmt->bindValue(':search', $term);
                $stmt->bindValue(':search2', $term);
            }
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Actualiza el estado de un pedido y, si se marca como 'entregado', crea
     * la venta asociada automáticamente.
     *
     * @param int         $pedido_id  Identificador del pedido a actualizar.
     * @param string      $estado     Nuevo estado del pedido (pendiente, en_preparacion, entregado, cancelado).
     * @param string|null $hora_real  Hora real de entrega (solo requerida cuando estado es 'entregado').
     * @param int|null    $usuario_id ID del usuario vendedor para la venta generada al entregar.
     * @return bool true si la actualización fue exitosa, false si ocurrió un error.
     */
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

    /**
     * Elimina lógicamente un pedido (soft delete) marcándolo como eliminado
     * y registrando la fecha de eliminación.
     *
     * @param int $id Identificador del pedido a eliminar.
     * @return bool true si el pedido fue eliminado, false si no existía o ya estaba eliminado.
     */
    public function delete($id) {
        $query = "UPDATE pedidos SET eliminado = true, deleted_at = NOW() WHERE id = :id AND eliminado = false";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    /**
     * Actualiza el cliente, la fecha de entrega y la hora de entrega de un pedido existente.
     *
     * @param int    $id           Identificador del pedido a actualizar.
     * @param int    $cliente_id   Nuevo ID del cliente.
     * @param string $fecha_entrega Nueva fecha de entrega (formato YYYY-MM-DD).
     * @param string $hora_entrega  Nueva hora de entrega (formato HH:MM).
     * @return bool true si el pedido fue actualizado, false si no existía o ya estaba eliminado.
     */
    public function update($id, $cliente_id, $fecha_entrega, $hora_entrega) {
        $query = "UPDATE {$this->table_name} SET cliente_id = :cliente_id, fecha_entrega = :fecha_entrega, hora_entrega = :hora_entrega WHERE id = :id AND eliminado = false";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":cliente_id", $cliente_id);
        $stmt->bindParam(":fecha_entrega", $fecha_entrega);
        $stmt->bindParam(":hora_entrega", $hora_entrega);
        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    /**
     * Obtiene los detalles (productos) asociados a un pedido con el nombre de cada producto.
     *
     * @param int $pedido_id Identificador del pedido.
     * @return array<int, array<string, mixed>> Array de detalles con cantidad, precio y nombre del producto.
     */
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
