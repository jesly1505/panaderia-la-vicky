<?php
namespace App\Models;
use PDO;
use PDOException;
use App\Utils\Logger;

class ClienteModel
{
    /** @var PDO Conexión a la base de datos. */
    private $conn;
    /** @var string Nombre de la tabla principal. */
    private $table_name = "clientes";

    /**
     * @param PDO $db Conexión PDO activa.
     */
    public function __construct(PDO $db)
    {
        $this->conn = $db;
        // Future: inject Logger via DI if available

    }

    /**
     * Crea un nuevo cliente. Verifica duplicados de email y DNI.
     *
     * @param  string $nombre    Nombre completo.
     * @param  string $email     Correo electrónico.
     * @param  string $telefono  Teléfono.
     * @param  string $direccion Dirección.
     * @param  string $dni       Documento de identidad.
     * @return bool
     */
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

    /**
     * Verifica si ya existe un cliente con el email dado.
     *
     * @param  string $email Correo a verificar.
     * @return bool  true si ya existe.
     */
    // Check if email already exists
    public function existsEmail($email)
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

    /**
     * Verifica si ya existe un cliente con el DNI dado.
     *
     * @param  string $dni Documento a verificar.
     * @return bool  true si ya existe.
     */
    // Check if DNI already exists
    public function existsDNI($dni)
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

    /**
     * Verifica si ya existe un email en otro cliente distinto del indicado.
     */
    public function existsEmailExcluding($email, $excludeId)
    {
        try {
            $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE email = :email AND id != :id AND eliminado = false";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":id", $excludeId);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            Logger::error("Error checking duplicate email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si ya existe un DNI en otro cliente distinto del indicado.
     */
    public function existsDNIExcluding($dni, $excludeId)
    {
        try {
            $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE dni = :dni AND id != :id AND eliminado = false";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":dni", $dni);
            $stmt->bindParam(":id", $excludeId);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            Logger::error("Error checking duplicate DNI: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lista clientes con paginación y búsqueda opcional.
     *
     * @param  int|null    $limit  Registros por página (null = sin límite).
     * @param  int|null    $offset Offset de inicio.
     * @param  string      $search Término de búsqueda.
     * @return array       Lista de clientes.
     */
    public function readAll($limit = null, $offset = null, string $search = '')
    {
        try {
            $query = "SELECT id, nombre, dni, telefono, email, direccion FROM " . $this->table_name . " 
                  WHERE eliminado = false";
            if (!empty($search)) {
                $query .= " AND (nombre LIKE :search OR dni LIKE :search2 OR telefono LIKE :search3 OR email LIKE :search4)";
            }
            $query .= " ORDER BY nombre ASC";
            if ($limit !== null && $offset !== null) {
                $query .= " LIMIT :limit OFFSET :offset";
            }
            $stmt = $this->conn->prepare($query);
            if (!empty($search)) {
                $term = "%" . $search . "%";
                $stmt->bindValue(':search', $term);
                $stmt->bindValue(':search2', $term);
                $stmt->bindValue(':search3', $term);
                $stmt->bindValue(':search4', $term);
            }
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

    /**
     * Cuenta el total de clientes activos, opcionalmente filtrados por búsqueda.
     *
     * @param  string $search Término de búsqueda.
     * @return int    Total de clientes.
     */
    // Count total non‑deleted clients
    public function countAll(string $search = ''): int
    {
        try {
            $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE eliminado = false";
            if (!empty($search)) {
                $query .= " AND (nombre LIKE :search OR dni LIKE :search2 OR telefono LIKE :search3 OR email LIKE :search4)";
            }
            $stmt = $this->conn->prepare($query);
            if (!empty($search)) {
                $term = "%" . $search . "%";
                $stmt->bindValue(':search', $term);
                $stmt->bindValue(':search2', $term);
                $stmt->bindValue(':search3', $term);
                $stmt->bindValue(':search4', $term);
            }
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            Logger::error("Error counting clientes: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtiene el historial de compras (ventas) de un cliente.
     *
     * @param  int   $cliente_id ID del cliente.
     * @return array Lista de ventas asociadas al cliente.
     */
    public function getPurchaseHistory($cliente_id)
    {
        // No duplicate check needed here
        try {
            $query = "SELECT v.*, COALESCE(p.fecha_pedido, v.fecha_venta) as fecha_pedido 
                  FROM ventas v 
                  LEFT JOIN pedidos p ON v.pedido_id = p.id 
                  WHERE (p.cliente_id = :cliente_id OR v.cliente_id = :cliente_id2)
                  ORDER BY v.fecha_venta DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":cliente_id", $cliente_id);
            $stmt->bindParam(":cliente_id2", $cliente_id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Logger::error("Error fetching purchase history for cliente {$cliente_id}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualiza los datos de un cliente. Verifica duplicados de email y DNI.
     *
     * @param  int    $id        ID del cliente.
     * @param  string $nombre    Nuevo nombre.
     * @param  string $email     Nuevo email.
     * @param  string $telefono  Nuevo teléfono.
     * @param  string $direccion Nueva dirección.
     * @param  string $dni       Nuevo DNI.
     * @return bool
     */
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

    /**
     * Elimina un cliente (borrado lógico).
     *
     * @param  int  $id ID del cliente.
     * @return bool
     */
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
