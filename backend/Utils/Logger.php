<?php
namespace App\Utils;

use App\Core\Database;
use PDO;
use PDOException;

class Logger {
    
    private static function getDb(): ?PDO {
        try {
            return (new Database())->getConnection();
        } catch (\Throwable $e) {
            error_log("Error de conexión en Logger: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Registra una acción general en la bitácora del sistema.
     */
    public static function logAction($modulo, $accion, $detalles = null) {
        try {
            $db = self::getDb();
            if (!$db) return;
            
            $usuario_id = $_SESSION['user_id'] ?? ($_SESSION['usuario_id'] ?? null);
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;

            $query = "INSERT INTO bitacora_sistema (usuario_id, modulo, accion, detalles, ip_address) 
                      VALUES (:usuario_id, :modulo, :accion, :detalles, :ip_address)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':usuario_id', $usuario_id);
            $stmt->bindParam(':modulo', $modulo);
            $stmt->bindParam(':accion', $accion);
            $stmt->bindParam(':detalles', $detalles);
            $stmt->bindParam(':ip_address', $ip_address);
            $stmt->execute();
        } catch(PDOException $e) {
            error_log("Error en Logger::logAction - " . $e->getMessage());
        }
    }

    /**
     * Registra un cambio a nivel de datos (Auditoría).
     */
    public static function logAudit($tabla_afectada, $registro_id, $accion, $valores_anteriores = null, $valores_nuevos = null) {
        try {
            $db = self::getDb();
            if (!$db) return;
            
            $usuario_id = $_SESSION['user_id'] ?? ($_SESSION['usuario_id'] ?? null);
            
            $val_ant_json = $valores_anteriores !== null ? json_encode($valores_anteriores, JSON_UNESCAPED_UNICODE) : null;
            $val_nuev_json = $valores_nuevos !== null ? json_encode($valores_nuevos, JSON_UNESCAPED_UNICODE) : null;

            $query = "INSERT INTO auditoria_cambios (tabla_afectada, registro_id, accion, valores_anteriores, valores_nuevos, usuario_id) 
                      VALUES (:tabla, :registro_id, :accion, :ant, :nuevos, :usuario_id)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':tabla', $tabla_afectada);
            $stmt->bindParam(':registro_id', $registro_id);
            $stmt->bindParam(':accion', $accion);
            $stmt->bindParam(':ant', $val_ant_json);
            $stmt->bindParam(':nuevos', $val_nuev_json);
            $stmt->bindParam(':usuario_id', $usuario_id);
            $stmt->execute();
        } catch(PDOException $e) {
            error_log("Error en Logger::logAudit - " . $e->getMessage());
        }
    }

    /**
     * Genera una alerta automática en el sistema
     */
    public static function logAlert($tipo, $modulo, $mensaje) {
        try {
            $db = self::getDb();
            if (!$db) return;

            $query = "INSERT INTO alertas_sistema (tipo_alerta, modulo, mensaje) VALUES (:tipo, :modulo, :mensaje)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':tipo', $tipo);
            $stmt->bindParam(':modulo', $modulo);
            $stmt->bindParam(':mensaje', $mensaje);
            $stmt->execute();
        } catch(PDOException $e) {
            error_log("Error en Logger::logAlert - " . $e->getMessage());
        }
    }

    /**
     * Registra un mensaje de error técnico en la bitácora de alertas.
     * Utiliza el nivel de alerta "error" y módulo "General".
     */
    public static function error(string $message) {
        // Usa logAlert para guardar el mensaje de error técnico.
        self::logAlert('error', 'General', $message);
    }
}

// Alias global para compatibilidad
if (!class_exists('Logger')) {
    class_alias('App\Utils\Logger', 'Logger');
}
