<?php
namespace App\Models;

use PDO;

class CatalogoModel {
    private $conn;
    private $table = 'catalogos';

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    public function getByTipo(string $tipo): array {
        $query = "SELECT id, valor, etiqueta, estado
                  FROM {$this->table}
                  WHERE tipo = :tipo AND eliminado = 0 AND estado = 1
                  ORDER BY etiqueta ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll(string $tipo): array {
        $query = "SELECT id, tipo, valor, etiqueta, estado, creado_en
                  FROM {$this->table}
                  WHERE tipo = :tipo AND eliminado = 0
                  ORDER BY etiqueta ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(string $tipo, string $valor, string $etiqueta): int|false {
        $query = "INSERT INTO {$this->table} (tipo, valor, etiqueta) VALUES (:tipo, :valor, :etiqueta)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':valor', $valor);
        $stmt->bindParam(':etiqueta', $etiqueta);
        $stmt->execute();
        return $this->conn->lastInsertId();
    }

    public function update(int $id, string $etiqueta, int $estado): bool {
        $query = "UPDATE {$this->table} SET etiqueta = :etiqueta, estado = :estado WHERE id = :id AND eliminado = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':etiqueta', $etiqueta);
        $stmt->bindParam(':estado', $estado);
        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool {
        $query = "UPDATE {$this->table} SET eliminado = 1, deleted_at = NOW() WHERE id = :id AND eliminado = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute() && $stmt->rowCount() > 0;
    }
}
