<?php
namespace App\Models;

use PDO;
use App\Utils\Logger;

class ClienteModel
{
    private $conn;
    private $table_name = "clientes";

    public function __construct(PDO $db)
    {
        $this->conn = $db;
        // Future: inject Logger via DI if available

    }

    public function create($nombre, $email, $telefono, $direccion, $dni)
    {
        // Check for duplicate email
        if ($this->existsEmail($email)) {
            Logger::error("Attempt to create duplicate cliente with email: $email");
            return false;
        }
        // Check for duplicate DNI
        if ($this->existsDNI($dni)) {
            Logger::error("Attempt to create duplicate cliente with DNI: $dni");
            return false;
        }
        // Start transaction for atomic insert
        $this->conn->beginTransaction();
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                  (nombre, email, telefono, direccion, dni, eliminado) 
                  VALUES (:nombre, :email, :telefono, :direccion, :dni, false)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":nombre", $nombre);
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":telefono", $telefono);
            $stmt->bindParam(":direccion", $direccion);
            $stmt->bindParam(":dni", $dni);
            $success = $stmt->execute();
            if ($success) {
                $this->conn->commit();
                return true;
            } else {
                $this->conn->rollBack();
                return false;
            }
        } catch (PDOException $e) {
            $this->conn->rollBack();
            Logger::error("Error creating cliente: " . $e->getMessage());
            return false;
        }
    }

    // Check if email already exists
    private function existsEmail($email)
    {
        try {
            $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE email = :email AND eliminado = false";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":email", $email);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            Logger::error("Error checking duplicate email: " . $e->getMessage());
            return false;
        }
    }

    // Check if DNI already exists
    private function existsDNI($dni)
    {
        try {
            $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE dni = :dni AND eliminado = false";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":dni", $dni);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            Logger::error("Error checking duplicate DNI: " . $e->getMessage());
            return false;
        }
    }

    public function readAll($limit = null, $offset = null)
    {
        try {
            $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE eliminado = false 
                  ORDER BY nombre ASC";
            if ($limit !== null && $offset !== null) {
                $query .= " LIMIT :limit OFFSET :offset";
            }
            $stmt = $this->conn->prepare($query);
            if ($limit !== null && $offset !== null) {
                $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
                $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Logger::error("Error reading clientes: " . $e->getMessage());
            return [];
        }
    }

    public function getPurchaseHistory($cliente_id)
    {
        // No duplicate check needed here
        try {
            $query = "SELECT v.*, p.fecha_pedido 
                  FROM ventas v 
                  JOIN pedidos p ON v.pedido_id = p.id 
                  WHERE p.cliente_id = :cliente_id 
                  ORDER BY v.fecha_venta DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":cliente_id", $cliente_id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Logger::error("Error fetching purchase history for cliente {$cliente_id}: " . $e->getMessage());
            return [];
        }
    }

    public function update($id, $nombre, $email, $telefono, $direccion, $dni)
    {
        // Prevent duplicate email on other records
        $queryDupEmail = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE email = :email AND id != :id AND eliminado = false";
        $stmtDupEmail = $this->conn->prepare($queryDupEmail);
        $stmtDupEmail->bindParam(":email", $email);
        $stmtDupEmail->bindParam(":id", $id);
        $stmtDupEmail->execute();
        if ($stmtDupEmail->fetchColumn() > 0) {
            Logger::error("Attempt to update cliente {$id} with duplicate email: $email");
            return false;
        }
        // Prevent duplicate DNI on other records
        $queryDupDni = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE dni = :dni AND id != :id AND eliminado = false";
        $stmtDupDni = $this->conn->prepare($queryDupDni);
        $stmtDupDni->bindParam(":dni", $dni);
        $stmtDupDni->bindParam(":id", $id);
        $stmtDupDni->execute();
        if ($stmtDupDni->fetchColumn() > 0) {
            Logger::error("Attempt to update cliente {$id} with duplicate DNI: $dni");
            return false;
        }
        // Start transaction for atomic update
        $this->conn->beginTransaction();
        try {
            $query = "UPDATE " . $this->table_name . " 
                  SET nombre = :nombre, email = :email, telefono = :telefono, direccion = :direccion, dni = :dni 
                  WHERE id = :id AND eliminado = false";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":nombre", $nombre);
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":telefono", $telefono);
            $stmt->bindParam(":direccion", $direccion);
            $stmt->bindParam(":dni", $dni);
            $stmt->bindParam(":id", $id);
            $success = $stmt->execute();
            if ($success) {
                $this->conn->commit();
                return true;
            } else {
                $this->conn->rollBack();
                return false;
            }
        } catch (PDOException $e) {
            $this->conn->rollBack();
            Logger::error("Error updating cliente {$id}: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id)
    {
        // Start transaction for atomic soft‑delete
        $this->conn->beginTransaction();
        try {
            $query = "UPDATE " . $this->table_name . " SET eliminado = true WHERE id = :id AND eliminado = false";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            $success = $stmt->execute() && $stmt->rowCount() > 0;
            if ($success) {
                $this->conn->commit();
                return true;
            } else {
                $this->conn->rollBack();
                return false;
            }
        } catch (PDOException $e) {
            $this->conn->rollBack();
            Logger::error("Error deleting cliente {$id}: " . $e->getMessage());
            return false;
        }
    }
}
