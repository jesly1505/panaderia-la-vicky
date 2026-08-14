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
        $query = "INSERT INTO " . $this->table_name . " (nombre, contacto, telefono, email) 
                  VALUES (:nombre, :contacto, :telefono, :email)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":contacto", $contacto);
        $stmt->bindParam(":telefono", $telefono);
        $stmt->bindParam(":email", $email);
        return $stmt->execute();
    }

    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE deleted_at IS NULL 
                  ORDER BY nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($id, $nombre, $contacto, $telefono, $email) {
        $query = "UPDATE " . $this->table_name . " 
                  SET nombre = :nombre, contacto = :contacto, telefono = :telefono, email = :email 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":contacto", $contacto);
        $stmt->bindParam(":telefono", $telefono);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $query = "UPDATE " . $this->table_name . " SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id AND deleted_at IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
