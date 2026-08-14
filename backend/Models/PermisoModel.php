<?php
namespace App\Models;

use PDO;

class PermisoModel {
    private $conn;

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    /** Catálogo completo de permisos, agrupado por módulo. */
    public function getAll(): array {
        $query = "SELECT id, codigo, modulo, nombre, descripcion
                  FROM permisos
                  ORDER BY modulo ASC, id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Códigos de permiso asignados a un rol. */
    public function getPermisosByRol($rol_id): array {
        $query = "SELECT p.codigo
                  FROM permisos p
                  JOIN rol_permiso rp ON rp.permiso_id = p.id
                  WHERE rp.rol_id = :rol_id
                  ORDER BY p.codigo ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':rol_id', $rol_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /** Asigna/reemplaza los permisos de un rol. Devuelve true si todo fue OK. */
    public function setPermisosRol($rol_id, array $codigos): bool {
        try {
            $this->conn->beginTransaction();

            $del = $this->conn->prepare("DELETE FROM rol_permiso WHERE rol_id = :rol_id");
            $del->bindParam(':rol_id', $rol_id, PDO::PARAM_INT);
            $del->execute();

            $ins = $this->conn->prepare(
                "INSERT INTO rol_permiso (rol_id, permiso_id)
                 SELECT :rol_id, id FROM permisos WHERE codigo = :codigo"
            );
            foreach ($codigos as $codigo) {
                $ins->bindParam(':rol_id', $rol_id, PDO::PARAM_INT);
                $ins->bindParam(':codigo', $codigo);
                $ins->execute();
            }

            $this->conn->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            error_log('PermisoModel::setPermisosRol error: ' . $e->getMessage());
            return false;
        }
    }

    /** Roles existentes con la cantidad de permisos asignados. */
    public function getRoles(): array {
        $query = "SELECT r.id, r.nombre, r.descripcion, COUNT(rp.permiso_id) AS permisos_count
                  FROM roles r
                  LEFT JOIN rol_permiso rp ON rp.rol_id = r.id
                  GROUP BY r.id, r.nombre, r.descripcion
                  ORDER BY r.id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Cantidad de usuarios activos asignados a un rol. */
    public function getUserCountByRol($rol_id): int {
        $query = "SELECT COUNT(*) FROM usuarios WHERE rol_id = :rol_id AND deleted_at IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':rol_id', $rol_id, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /** Indica si ya existe un rol con ese nombre (opcionalmente excluyendo uno). */
    public function rolNombreExiste($nombre, $excludeId = null): bool {
        if ($excludeId !== null) {
            $query = "SELECT COUNT(*) FROM roles WHERE nombre = :nombre AND id != :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $excludeId, PDO::PARAM_INT);
        } else {
            $query = "SELECT COUNT(*) FROM roles WHERE nombre = :nombre";
            $stmt = $this->conn->prepare($query);
        }
        $stmt->bindParam(':nombre', $nombre);
        $stmt->execute();
        return (int)$stmt->fetchColumn() > 0;
    }

    /** Crea un rol. Devuelve el id creado. */
    public function createRol($nombre, $descripcion): int {
        $query = "INSERT INTO roles (nombre, descripcion) VALUES (:nombre, :descripcion)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->execute();
        return (int)$this->conn->lastInsertId();
    }

    /** Actualiza nombre y descripción de un rol. */
    public function updateRol($id, $nombre, $descripcion): bool {
        $query = "UPDATE roles SET nombre = :nombre, descripcion = :descripcion WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        return $stmt->execute();
    }

    /** Elimina un rol. Los permisos asociados se limpian vía ON DELETE CASCADE. */
    public function deleteRol($id): bool {
        $query = "DELETE FROM roles WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
