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
        $query = "SELECT u.id, u.rol_id, u.nombre, u.email, u.password_hash, u.estado, u.intentos_fallidos, u.bloqueado_hasta, u.ultimo_acceso, r.nombre as rol_nombre 
                  FROM " . $this->table_name . " u 
                  JOIN roles r ON u.rol_id = r.id 
                  WHERE u.email = :email AND u.eliminado = false LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /** Incrementa los intentos fallidos; al alcanzar el máximo bloquea la cuenta 15 minutos. */
    public function registerFailedAttempt($email, int $max = 5): void {
        $stmt = $this->conn->prepare(
            "UPDATE " . $this->table_name . " SET intentos_fallidos = intentos_fallidos + 1 WHERE email = :email AND eliminado = false"
        );
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $stmt2 = $this->conn->prepare(
            "UPDATE " . $this->table_name . " 
             SET bloqueado_hasta = DATE_ADD(NOW(), INTERVAL 15 MINUTE) 
             WHERE email = :email AND intentos_fallidos >= :max AND eliminado = false"
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
        $query = "SELECT u.id, u.nombre, u.email, u.estado, u.rol_id, r.nombre as rol_nombre 
                  FROM " . $this->table_name . " u 
                  JOIN roles r ON u.rol_id = r.id
                  WHERE u.eliminado = false
                  ORDER BY u.nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($nombre, $email, $password, $rol_id = 2) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (nombre, email, password_hash, rol_id, estado, eliminado) 
                  VALUES (:nombre, :email, :password, :rol_id, 'activo', false)";
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
        $query = "UPDATE " . $this->table_name . " SET eliminado = true, deleted_at = NOW() WHERE id = :id AND eliminado = false";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    public function getProfitsByUser() {
        $query = "SELECT u.nombre, SUM(v.ganancias) as total_ganado 
                  FROM ventas v 
                  JOIN usuarios u ON v.usuario_id = u.id 
                  WHERE v.estado != 'cancelado' AND u.eliminado = false
                  GROUP BY u.id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Asigna un token de recuperación de contraseña con fecha de caducidad. */
    public function setResetToken($email, $token, $expires) {
        $query = "UPDATE " . $this->table_name . " SET reset_token = :token, reset_token_expira = :expires WHERE email = :email AND eliminado = false";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':expires', $expires);
        $stmt->bindParam(':email', $email);
        return $stmt->execute();
    }

    /** Busca un usuario por token de recuperación vigente. */
    public function findByResetToken($token) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE reset_token = :token AND reset_token_expira > NOW() AND eliminado = false LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /** Actualiza la contraseña y limpia el token de recuperación. */
    public function updatePassword($id, $newPassword) {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $query = "UPDATE " . $this->table_name . " SET password_hash = :hash, reset_token = NULL, reset_token_expira = NULL WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':hash', $hash);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
