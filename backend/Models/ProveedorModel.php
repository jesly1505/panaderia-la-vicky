<?php
namespace App\Models;

use PDO;

class ProveedorModel {
    private $conn;
    private $table_name = "proveedores";

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    public function create($nombre, $contacto, $telefono, $email) {
        $query = "INSERT INTO " . $this->table_name . " (nombre, contacto, telefono, email, eliminado) 
                  VALUES (:nombre, :contacto, :telefono, :email, false)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":contacto", $contacto);
        $stmt->bindParam(":telefono", $telefono);
        $stmt->bindParam(":email", $email);
        return $stmt->execute();
    }

    // Existing method unchanged
    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE eliminado = false 
                  ORDER BY nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get total count of non-deleted providers.
     */
    public function countAll() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE eliminado = false";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] ?? 0;
    }

    /**
     * Get paginated providers.
     * @param int $limit Number of records per page.
     * @param int $offset Starting offset.
     */
    public function readPaginated($limit, $offset) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE eliminado = false 
                  ORDER BY nombre ASC 
                  LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($id, $nombre, $contacto, $telefono, $email) {
        $query = "UPDATE " . $this->table_name . " 
                  SET nombre = :nombre, contacto = :contacto, telefono = :telefono, email = :email 
                  WHERE id = :id AND eliminado = false";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":contacto", $contacto);
        $stmt->bindParam(":telefono", $telefono);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $query = "UPDATE " . $this->table_name . " SET eliminado = true, deleted_at = NOW() WHERE id = :id AND eliminado = false";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id AND eliminado = false";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
