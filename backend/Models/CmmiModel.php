<?php
namespace App\Models;

use App\Helpers\DateFilterHelper;
use PDO;

class CmmiModel {
    private $conn;

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    public function getBitacora($limite = 100, $filterType = 'all', $startDate = '', $endDate = '') {
        $dateCondition = DateFilterHelper::getSqlCondition('b.fecha_hora', $filterType, $startDate, $endDate);
        $query = "SELECT b.*, u.nombre as usuario 
                  FROM bitacora_sistema b 
                  LEFT JOIN usuarios u ON b.usuario_id = u.id 
                  WHERE $dateCondition
                  ORDER BY b.fecha_hora DESC LIMIT :limite";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAuditoria($limite = 100, $filterType = 'all', $startDate = '', $endDate = '') {
        $dateCondition = DateFilterHelper::getSqlCondition('a.fecha_hora', $filterType, $startDate, $endDate);
        $query = "SELECT a.*, u.nombre as usuario 
                  FROM auditoria_cambios a 
                  LEFT JOIN usuarios u ON a.usuario_id = u.id 
                  WHERE $dateCondition
                  ORDER BY a.fecha_hora DESC LIMIT :limite";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getIncidencias($filterType = 'all', $startDate = '', $endDate = '') {
        $dateCondition = DateFilterHelper::getSqlCondition('i.fecha_reporte', $filterType, $startDate, $endDate);
        $query = "SELECT i.*, u.nombre as usuario_reporta_nombre 
                  FROM incidencias i 
                  LEFT JOIN usuarios u ON i.usuario_reporta = u.id 
                  WHERE $dateCondition
                  ORDER BY i.fecha_reporte DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrarIncidencia($modulo, $descripcion, $usuario_id) {
        $query = "INSERT INTO incidencias (modulo, descripcion, usuario_reporta) VALUES (:modulo, :descripcion, :usuario_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':modulo', $modulo);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':usuario_id', $usuario_id);
        return $stmt->execute();
    }

    public function resolverIncidencia($id) {
        $query = "UPDATE incidencias SET estado = 'resuelta', fecha_resolucion = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getAlertasActivas($filterType = 'all', $startDate = '', $endDate = '') {
        $dateCondition = DateFilterHelper::getSqlCondition('fecha_creacion', $filterType, $startDate, $endDate);
        $query = "SELECT * FROM alertas_sistema WHERE estado = 'activa' AND $dateCondition ORDER BY fecha_creacion DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getKpiVentasHoy() {
        $query = "SELECT COUNT(*) as cantidad, COALESCE(SUM(total), false) as total FROM ventas WHERE DATE(fecha_venta) = CURDATE() AND estado != 'cancelado'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getKpiErroresActivos() {
        $query = "SELECT COUNT(*) as total FROM incidencias WHERE estado != 'resuelta'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }

    public function getKpiBajoInventario() {
        $query = "SELECT COUNT(*) as total FROM insumos WHERE stock_actual <= stock_minimo AND visible = 1 AND eliminado = false";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }
}

// Alias global para compatibilidad de vistas
if (!class_exists('CmmiModel')) {
    class_alias('App\Models\CmmiModel', 'CmmiModel');
}
