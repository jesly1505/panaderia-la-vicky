<?php
namespace App\Core;

use PDO;

/**
 * Registro de auditoría del sistema.
 * - bitacora_sistema: acciones relevantes (login/logout y operaciones de escritura).
 * - accesos_denegados: intentos de acceso a módulos sin el rol requerido.
 */
class AuditService {
    private $conn;

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    /** Registra una acción en la bitácora del sistema. */
    public function log(string $modulo, string $accion, string $detalles = ''): void {
        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO bitacora_sistema (usuario_id, modulo, accion, detalles, ip_address)
                 VALUES (:usuario_id, :modulo, :accion, :detalles, :ip)"
            );
            $usuario_id = $_SESSION['usuario_id'] ?? null;
            $stmt->bindValue(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $stmt->bindValue(':modulo', $modulo);
            $stmt->bindValue(':accion', $accion);
            $stmt->bindValue(':detalles', $detalles);
            $stmt->bindValue(':ip', $_SERVER['REMOTE_ADDR'] ?? null);
            $stmt->execute();
        } catch (\Throwable $e) {
            error_log('AuditService::log error: ' . $e->getMessage());
        }
    }

    /** Registra un intento de acceso denegado por rol insuficiente. */
    public function denied(string $modulo): void {
        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO accesos_denegados (usuario_id, modulo_intentado, ip_address)
                 VALUES (:usuario_id, :modulo, :ip)"
            );
            $usuario_id = $_SESSION['usuario_id'] ?? null;
            $stmt->bindValue(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $stmt->bindValue(':modulo', $modulo);
            $stmt->bindValue(':ip', $_SERVER['REMOTE_ADDR'] ?? null);
            $stmt->execute();
        } catch (\Throwable $e) {
            error_log('AuditService::denied error: ' . $e->getMessage());
        }
    }
}
