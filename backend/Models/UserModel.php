<?php
namespace App\Models;

use PDO;

class UserModel {
    private $conn;
    private $table_name = "usuarios";

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    public function findByEmail($email) {
        $query = "SELECT u.id, u.rol_id, u.nombre, u.email, u.password_hash, u.estado, u.intentos_fallidos, u.bloqueado_hasta, r.nombre as rol_nombre 
                  FROM " . $this->table_name . " u 
                  JOIN roles r ON u.rol_id = r.id 
                  WHERE u.email = :email AND u.deleted_at IS NULL LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /** Incrementa los intentos fallidos; al alcanzar el máximo bloquea la cuenta 15 minutos. */
    public function registerFailedAttempt($email, int $max = 5): void {
        $stmt = $this->conn->prepare(
            "UPDATE " . $this->table_name . " SET intentos_fallidos = intentos_fallidos + 1 WHERE email = :email"
        );
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $stmt2 = $this->conn->prepare(
            "UPDATE " . $this->table_name . " 
             SET bloqueado_hasta = DATE_ADD(NOW(), INTERVAL 15 MINUTE) 
             WHERE email = :email AND intentos_fallidos >= :max"
        );
        $stmt2->bindParam(':email', $email);
        $stmt2->bindParam(':max', $max, PDO::PARAM_INT);
        $stmt2->execute();
    }

    /** Reinicia el contador de intentos tras un login exitoso. */
    public function resetFailedAttempts($id): void {
        $stmt = $this->conn->prepare(
            "UPDATE " . $this->table_name . " SET intentos_fallidos = 0, bloqueado_hasta = NULL WHERE id = :id"
        );
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    /** Actualiza la marca de último acceso. */
    public function updateLastAccess($id): void {
        $stmt = $this->conn->prepare(
            "UPDATE " . $this->table_name . " SET ultimo_acceso = NOW() WHERE id = :id"
        );
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    public function getAll() {
        $query = "SELECT u.id, u.nombre, u.email, u.estado, r.nombre as rol_nombre 
                  FROM " . $this->table_name . " u 
                  JOIN roles r ON u.rol_id = r.id
                  WHERE u.deleted_at IS NULL
                  ORDER BY u.nombre ASC";
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
        $query = "UPDATE " . $this->table_name . " SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute() && $stmt->rowCount() > 0;
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
