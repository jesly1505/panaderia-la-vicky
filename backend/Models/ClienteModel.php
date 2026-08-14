<?php
namespace App\Models;

use PDO;

class ClienteModel {
    private $conn;
    private $table_name = "clientes";

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    public function create($nombre, $email, $telefono, $direccion) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (nombre, email, telefono, direccion) 
                  VALUES (:nombre, :email, :telefono, :direccion)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":telefono", $telefono);
        $stmt->bindParam(":direccion", $direccion);
        return $stmt->execute();
    }

    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE deleted_at IS NULL 
                  ORDER BY nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getPurchaseHistory($cliente_id) {
        $query = "SELECT v.*, p.fecha_pedido 
                  FROM ventas v 
                  JOIN pedidos p ON v.pedido_id = p.id 
                  WHERE p.cliente_id = :cliente_id 
                  ORDER BY v.fecha_venta DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cliente_id", $cliente_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($id, $nombre, $email, $telefono, $direccion) {
        $query = "UPDATE " . $this->table_name . " 
                  SET nombre = :nombre, email = :email, telefono = :telefono, direccion = :direccion 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":telefono", $telefono);
        $stmt->bindParam(":direccion", $direccion);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $query = "UPDATE " . $this->table_name . " SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute() && $stmt->rowCount() > 0;
    }
}
?>
