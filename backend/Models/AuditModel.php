<?php
namespace App\Models;

use PDO;

/**
 * Consultas de auditoría para la consola de administración.
 */
class AuditModel {
    private $conn;

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    /** Últimos registros de la bitácora del sistema. */
    public function getBitacora(int $limit = 100) {
        $query = "SELECT b.id, b.usuario_id, COALESCE(u.nombre, 'Sistema') AS usuario,
                         b.modulo, b.accion, b.detalles, b.ip_address, b.fecha_hora
                  FROM bitacora_sistema b
                  LEFT JOIN usuarios u ON u.id = b.usuario_id
                  ORDER BY b.id DESC
                  LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Intentos de acceso denegado por rol insuficiente. */
    public function getDeniedAccess(int $limit = 100) {
        $query = "SELECT d.id, d.usuario_id, COALESCE(u.nombre, 'Sin sesión') AS usuario,
                         d.modulo_intentado, d.ip_address, d.fecha_hora
                  FROM accesos_denegados d
                  LEFT JOIN usuarios u ON u.id = d.usuario_id
                  ORDER BY d.id DESC
                  LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
