<?php
namespace App\Models;

use PDO;

class GastoModel {
    private $conn;
    private $table = "gastos";

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    /** Gastos filtrados por rango de fechas (opcional) */
    public function getByDate($startDate = '', $endDate = '') {
        $where = "WHERE eliminado = 0";
        $params = [];
        if (!empty($startDate) && !empty($endDate)) {
            $where .= " AND DATE(fecha) BETWEEN :start AND :end";
            $params[':start'] = $startDate;
            $params[':end'] = $endDate;
        } elseif (!empty($startDate)) {
            $where .= " AND DATE(fecha) >= :start";
            $params[':start'] = $startDate;
        } elseif (!empty($endDate)) {
            $where .= " AND DATE(fecha) <= :end";
            $params[':end'] = $endDate;
        }
        $query = "SELECT * FROM {$this->table} $where ORDER BY fecha DESC";
        $stmt = $this->conn->prepare($query);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Registrar un nuevo gasto */
    public function create($descripcion, $monto, $fecha, $categoria = 'General') {
        $query = "INSERT INTO {$this->table} (descripcion, monto, fecha, categoria, eliminado) VALUES (:descripcion, :monto, :fecha, :categoria, false)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':monto', $monto);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':categoria', $categoria);
        return $stmt->execute();
    }

    /** Eliminar un gasto (borrado lógico) */
    public function delete($id) {
        $query = "UPDATE {$this->table} SET eliminado = true, deleted_at = NOW() WHERE id = :id AND eliminado = false";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    /** Actualizar un gasto existente */
    public function update($id, $descripcion, $monto, $fecha, $categoria) {
        $query = "UPDATE {$this->table} SET descripcion = :descripcion, monto = :monto, fecha = :fecha, categoria = :categoria WHERE id = :id AND eliminado = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':monto', $monto);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':categoria', $categoria);
        return $stmt->execute() && $stmt->rowCount() > 0;
    }
}
