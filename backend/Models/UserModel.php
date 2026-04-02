<?php
require_once __DIR__ . '/../../config/database.php';

class UserModel {
    private $conn;
    private $table_name = "usuarios";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function findByEmail($email) {
        $query = "SELECT u.id, u.nombre, u.email, u.password_hash, u.estado, r.nombre as rol_nombre 
                  FROM " . $this->table_name . " u 
                  JOIN roles r ON u.rol_id = r.id 
                  WHERE u.email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAll() {
        $query = "SELECT u.id, u.nombre, u.email, u.estado, r.nombre as rol_nombre 
                  FROM " . $this->table_name . " u 
                  JOIN roles r ON u.rol_id = r.id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($nombre, $email, $password, $rol_id = 2) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (nombre, email, password_hash, rol_id, estado) 
                  VALUES (:nombre, :email, :password, :rol_id, 'activo')";
        $stmt = $this->conn->prepare($query);
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hash);
        $stmt->bindParam(':rol_id', $rol_id);
        return $stmt->execute();
    }

    public function delete($id) {
        if ($id == 1) return false; // Prevent deleting main admin
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function getProfitsByUser() {
        $query = "SELECT u.nombre, SUM(v.ganancias) as total_ganado 
                  FROM ventas v 
                  JOIN usuarios u ON v.usuario_id = u.id 
                  GROUP BY u.id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
