<?php
require_once __DIR__ . '/../../config/database.php';

class GastoModel {
    private $conn;
    private $table = "gastos";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /** Gastos de un día específico (YYYY-MM-DD) */
    public function getByDate($fecha) {
        $query = "SELECT * FROM {$this->table} WHERE DATE(fecha) = :fecha ORDER BY fecha DESC";
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

    /** Eliminar un gasto */
    public function delete($id) {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>
