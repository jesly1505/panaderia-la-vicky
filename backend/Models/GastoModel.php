<?php
namespace App\Models;

use PDO;

class GastoModel {
    private $conn;
    private $table = "gastos";

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    /** Gastos de un día específico (YYYY-MM-DD) */
    public function getByDate($fecha) {
        $query = "SELECT * FROM {$this->table} WHERE DATE(fecha) = :fecha AND deleted_at IS NULL ORDER BY fecha DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Registrar un nuevo gasto */
    public function create($descripcion, $monto, $fecha) {
        $query = "INSERT INTO {$this->table} (descripcion, monto, fecha) VALUES (:descripcion, :monto, :fecha)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':monto', $monto);
        $stmt->bindParam(':fecha', $fecha);
        return $stmt->execute();
    }

    /** Eliminar un gasto (borrado lógico) */
    public function delete($id) {
        $query = "UPDATE {$this->table} SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute() && $stmt->rowCount() > 0;
    }
}
?>
