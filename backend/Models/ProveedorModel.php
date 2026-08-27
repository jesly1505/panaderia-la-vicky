<?php
namespace App\Models;

use PDO;

class ProveedorModel
{
    /** @var PDO Conexión a la base de datos. */
    private $conn;
    /** @var string Nombre de la tabla principal. */
    private $table_name = "proveedores";

    /**
     * @param PDO $db Conexión PDO activa.
     */
    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    /**
     * Crea un nuevo proveedor.
     *
     * @param  string $nombre   Nombre del proveedor.
     * @param  string $contacto Nombre de contacto.
     * @param  string $telefono Teléfono.
     * @param  string $email    Correo electrónico.
     * @return bool
     */
    public function create($nombre, $contacto, $telefono, $email)
    {
        $query = "INSERT INTO " . $this->table_name . " (nombre, contacto, telefono, email, eliminado) 
                  VALUES (:nombre, :contacto, :telefono, :email, 0)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":contacto", $contacto);
        $stmt->bindParam(":telefono", $telefono);
        $stmt->bindParam(":email", $email);
        return $stmt->execute();
    }

    /**
     * Lista todos los proveedores activos.
     *
     * @return array Lista de proveedores.
     */
    // Existing method unchanged
    public function readAll()
    {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE eliminado = 0 
                  ORDER BY nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get total count of non-deleted providers with optional search.
     */
    public function countAll($search = '')
    {
        $where = "eliminado = 0";
        if (!empty($search)) {
            $where .= " AND (nombre LIKE :search OR contacto LIKE :search2 OR telefono LIKE :search3 OR email LIKE :search4)";
        }
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE " . $where;
        $stmt = $this->conn->prepare($query);
        if (!empty($search)) {
            $term = "%" . $search . "%";
            $stmt->bindValue(':search', $term);
            $stmt->bindValue(':search2', $term);
            $stmt->bindValue(':search3', $term);
            $stmt->bindValue(':search4', $term);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }

    /**
     * Get paginated providers with optional search.
     * @param int $limit Number of records per page.
     * @param int $offset Starting offset.
     * @param string $search Search term.
     */
    public function readPaginated($limit, $offset, $search = '')
    {
        $where = "eliminado = 0";
        if (!empty($search)) {
            $where .= " AND (nombre LIKE :search OR contacto LIKE :search2 OR telefono LIKE :search3 OR email LIKE :search4)";
        }
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE " . $where . " 
                  ORDER BY nombre ASC 
                  LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        if (!empty($search)) {
            $term = "%" . $search . "%";
            $stmt->bindValue(':search', $term);
            $stmt->bindValue(':search2', $term);
            $stmt->bindValue(':search3', $term);
            $stmt->bindValue(':search4', $term);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Actualiza los datos de un proveedor.
     *
     * @param  int    $id       ID del proveedor.
     * @param  string $nombre   Nuevo nombre.
     * @param  string $contacto Nuevo contacto.
     * @param  string $telefono Nuevo teléfono.
     * @param  string $email    Nuevo email.
     * @return bool
     */
    public function update($id, $nombre, $contacto, $telefono, $email)
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET nombre = :nombre, contacto = :contacto, telefono = :telefono, email = :email 
                  WHERE id = :id AND eliminado = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":contacto", $contacto);
        $stmt->bindParam(":telefono", $telefono);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    /**
     * Elimina un proveedor (borrado lógico).
     *
     * @param  int  $id ID del proveedor.
     * @return bool
     */
    public function delete($id)
    {
        $query = "UPDATE " . $this->table_name . " SET eliminado = 1, deleted_at = NOW() WHERE id = :id AND eliminado = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    /**
     * Obtiene un proveedor por su ID.
     *
     * @param  int         $id ID del proveedor.
     * @return array|false Fila del proveedor o false si no existe.
     */
    public function getById($id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id AND eliminado = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
