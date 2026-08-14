<?php
namespace App\Models;

use PDO;
use App\Core\Interfaces\InsumoRepositoryInterface;

class InsumoModel implements InsumoRepositoryInterface {
    private $conn;
    private $table_name = "insumos";

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    public function create($proveedor_id, $nombre, $unidad_medida, $stock_inicial, $stock_minimo, $precio_costo) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (proveedor_id, nombre, unidad_medida, stock_actual, stock_minimo, precio_costo) 
                  VALUES (:proveedor_id, :nombre, :unidad_medida, :stock_inicial, :stock_minimo, :precio_costo)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":proveedor_id", $proveedor_id);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":unidad_medida", $unidad_medida);
        $stmt->bindParam(":stock_inicial", $stock_inicial);
        $stmt->bindParam(":stock_minimo", $stock_minimo);
        $stmt->bindParam(":precio_costo", $precio_costo);
        
        return $stmt->execute();
    }

    public function updateStock($id, $cantidad) {
        // La cantidad puede ser positiva o negativa
        $query = "UPDATE " . $this->table_name . " SET stock_actual = stock_actual + :cantidad WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cantidad", $cantidad);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function readAll($onlyVisible = true) {
        $where = " i.deleted_at IS NULL ";
        if ($onlyVisible) $where .= " AND i.visible = 1 ";
        $query = "SELECT i.*, p.nombre as proveedor_nombre FROM " . $this->table_name . " i 
                  LEFT JOIN proveedores p ON i.proveedor_id = p.id
                  WHERE $where
                  ORDER BY i.nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id AND deleted_at IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function setVisibility($id, $visible) {
        $query = "UPDATE " . $this->table_name . " SET visible = :visible WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":visible", $visible);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function getLowStock() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE stock_actual <= stock_minimo AND visible = 1 AND deleted_at IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function delete($id) {
        $query = "UPDATE " . $this->table_name . " SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    public function registrarCompra($insumo_id, $proveedor_id, $cantidad, $precio_compra) {
        try {
            $this->conn->beginTransaction();

            // 1. Registrar la compra en la tabla compras_insumos
            $queryC = "INSERT INTO compras_insumos (insumo_id, proveedor_id, cantidad, precio_compra) 
                       VALUES (:insumo_id, :proveedor_id, :cantidad, :precio_compra)";
            $stmtC = $this->conn->prepare($queryC);
            $stmtC->bindParam(":insumo_id", $insumo_id);
            $stmtC->bindParam(":proveedor_id", $proveedor_id);
            $stmtC->bindParam(":cantidad", $cantidad);
            $stmtC->bindParam(":precio_compra", $precio_compra);
            $stmtC->execute();

            // 2. Actualizar el stock_actual y opcionalmente el precio_costo en la tabla insumos
            $queryI = "UPDATE " . $this->table_name . " 
                       SET stock_actual = stock_actual + :cantidad, 
                           precio_costo = :precio_compra,
                           proveedor_id = :proveedor_id
                       WHERE id = :id";
            $stmtI = $this->conn->prepare($queryI);
            $stmtI->bindParam(":cantidad", $cantidad);
            $stmtI->bindParam(":precio_compra", $precio_compra);
            $stmtI->bindParam(":proveedor_id", $proveedor_id);
            $stmtI->bindParam(":id", $insumo_id);
            $stmtI->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
}
?>
