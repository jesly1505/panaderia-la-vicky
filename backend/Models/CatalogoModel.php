<?php
namespace App\Models;

use PDO;

class CatalogoModel {
    /** @var PDO Conexión a la base de datos. */
    private $conn;
    /** @var string Nombre de la tabla. */
    private $table = 'catalogos';

    /**
     * @param PDO $db Conexión PDO activa.
     */
    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    /**
     * Obtiene ítems activos de un tipo de catálogo (para selects).
     *
     * @param  string $tipo Tipo de catálogo.
     * @return array  Lista de ítems con id, valor, etiqueta, estado.
     */
    public function getByTipo(string $tipo): array {
        $query = "SELECT id, valor, etiqueta, estado
                  FROM {$this->table}
                  WHERE tipo = :tipo AND eliminado = 0 AND estado = 1
                  ORDER BY etiqueta ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene todos los ítems (incluyendo inactivos) de un tipo.
     *
     * @param  string $tipo Tipo de catálogo.
     * @return array  Lista de ítems.
     */
    public function getAll(string $tipo): array {
        $query = "SELECT id, tipo, valor, etiqueta, estado, creado_en
                  FROM {$this->table}
                  WHERE tipo = :tipo AND eliminado = 0
                  ORDER BY etiqueta ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crea un nuevo ítem de catálogo.
     *
     * @param  string $tipo     Tipo de catálogo.
     * @param  string $valor    Valor interno.
     * @param  string $etiqueta Etiqueta visible.
     * @return int|false ID del ítem creado o false en caso de error.
     */
    public function create(string $tipo, string $valor, string $etiqueta): int|false {
        $query = "INSERT INTO {$this->table} (tipo, valor, etiqueta) VALUES (:tipo, :valor, :etiqueta)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':valor', $valor);
        $stmt->bindParam(':etiqueta', $etiqueta);
        $stmt->execute();
        return $this->conn->lastInsertId();
    }

    /**
     * Actualiza la etiqueta y estado de un ítem de catálogo.
     *
     * @param  int    $id       ID del ítem.
     * @param  string $etiqueta Nueva etiqueta.
     * @param  int    $estado   Nuevo estado (1 = activo, 0 = inactivo).
     * @return bool
     */
    public function update(int $id, string $etiqueta, int $estado): bool {
        $query = "UPDATE {$this->table} SET etiqueta = :etiqueta, estado = :estado WHERE id = :id AND eliminado = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':etiqueta', $etiqueta);
        $stmt->bindParam(':estado', $estado);
        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    /**
     * Elimina un ítem de catálogo (borrado lógico).
     *
     * @param  int  $id ID del ítem.
     * @return bool
     */
    public function delete(int $id): bool {
        $query = "UPDATE {$this->table} SET eliminado = 1, deleted_at = NOW() WHERE id = :id AND eliminado = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute() && $stmt->rowCount() > 0;
    }
}
