<?php
namespace App\Controllers;

use App\Core\AuditService;
use App\Core\Database;
use App\Models\CmmiModel;
use App\Utils\Logger;
use PDO;

class CmmiController {
    private CmmiModel $model;
    private AuditService $audit;

    public function __construct(?CmmiModel $model = null, ?AuditService $audit = null) {
        if ($model !== null && $audit !== null) {
            $this->model = $model;
            $this->audit = $audit;
        } else {
            $db = (new Database())->getConnection();
            $this->model = $model ?? new CmmiModel($db);
            $this->audit = $audit ?? new AuditService($db);
        }
    }

    public function getAll(): void {
        header('Content-Type: application/json');
        $filter = $_GET['filter'] ?? 'all';
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';

        $incidencias = $this->model->getIncidencias($filter, $startDate, $endDate);
        echo json_encode(['success' => true, 'data' => $incidencias], JSON_UNESCAPED_UNICODE);
    }

    public function registrarIncidencia(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $modulo = trim($_POST['modulo'] ?? 'General');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $usuario_id = $_SESSION['user_id'] ?? ($_SESSION['usuario_id'] ?? null);

        if (empty($descripcion)) {
            if (isset($_GET['action'])) {
                header("Location: ../../frontend/incidencias.php?error=empty_desc");
                exit();
            }
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'La descripción es obligatoria']);
            return;
        }

        $ok = $this->model->registrarIncidencia($modulo, $descripcion, $usuario_id);
        if ($ok) {
            $this->audit->logAction('Incidencias', 'Incidencia registrada', "Módulo: $modulo");
            if (isset($_GET['action'])) {
                header("Location: ../../frontend/incidencias.php?success=1");
                exit();
            }
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Incidencia registrada']);
        } else {
            if (isset($_GET['action'])) {
                header("Location: ../../frontend/incidencias.php?error=db_error");
                exit();
            }
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error al registrar la incidencia']);
        }
    }

    public function resolverIncidencia(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $id = (int)($_POST['id'] ?? ($_GET['id'] ?? 0));
        if ($id <= 0) {
            if (isset($_GET['action'])) {
                header("Location: ../../frontend/incidencias.php?error=invalid_id");
                exit();
            }
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }

        $ok = $this->model->resolverIncidencia($id);
        if ($ok) {
            $this->audit->logAction('Incidencias', 'Incidencia resuelta', "ID: $id");
            if (isset($_GET['action'])) {
                header("Location: ../../frontend/incidencias.php?success=resolved");
                exit();
            }
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Incidencia resuelta']);
        } else {
            if (isset($_GET['action'])) {
                header("Location: ../../frontend/incidencias.php?error=1");
                exit();
            }
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error al resolver incidencia']);
        }
    }

    public function backupDatabase(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $database = new Database();
        $host = $database->getHost();
        $user = $database->getUsername();
        $pass = $database->getPassword();
        $dbname = $database->getDbName();

        $filename = "backup_la_vicky_" . date("Y-m-d_H-i-s") . ".sql";
        $mysqldump_path = getenv('MYSQLDUMP_PATH') ?: 'mysqldump';
        if ($mysqldump_path === 'mysqldump') {
            $xampp_mysql = "C:\\xampp\\mysql\\bin\\mysqldump.exe";
            if (file_exists($xampp_mysql)) {
                $mysqldump_path = $xampp_mysql;
            }
        }

        $temp_file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;
        $pass_arg = $pass ? "--password=" . escapeshellarg($pass) : "";
        $command = "\"" . $mysqldump_path . "\" --host=" . escapeshellarg($host) . " --user=" . escapeshellarg($user) . " $pass_arg " . escapeshellarg($dbname) . " > " . escapeshellarg($temp_file) . " 2>&1";

        exec($command, $output, $result);

        if ($result == 0 && file_exists($temp_file) && filesize($temp_file) > 0) {
            $this->audit->logAction('Sistema', 'Respaldo de Base de Datos generado');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($temp_file) . '"');
            header('Content-Length: ' . filesize($temp_file));
            readfile($temp_file);
            @unlink($temp_file);
            exit();
        } else {
            // Fallback en PHP nativo si mysqldump CLI no está disponible
            $this->nativeSqlBackup($dbname, $filename);
        }
    }

    /**
     * Respaldo SQL puro en PHP (cuando mysqldump.exe no está en PATH)
     */
    private function nativeSqlBackup(string $dbname, string $filename): void {
        try {
            $db = (new Database())->getConnection();
            $tables = [];
            $stmt = $db->query("SHOW TABLES");
            while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }

            $sql = "-- Respaldo Sistema La Vicky\n-- Fecha: " . date('Y-m-d H:i:s') . "\n-- Base de datos: $dbname\n\n";
            $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $table) {
                $createStmt = $db->query("SHOW CREATE TABLE `$table`");
                $createRow = $createStmt->fetch(PDO::FETCH_NUM);
                $sql .= "DROP TABLE IF EXISTS `$table`;\n";
                $sql .= $createRow[1] . ";\n\n";

                $rowsStmt = $db->query("SELECT * FROM `$table`");
                $rows = $rowsStmt->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($rows)) {
                    $columns = array_keys($rows[0]);
                    $colList = implode('`, `', $columns);
                    foreach ($rows as $r) {
                        $values = array_map(function ($val) use ($db) {
                            if ($val === null) return "NULL";
                            return $db->quote($val);
                        }, array_values($r));
                        $sql .= "INSERT INTO `$table` (`$colList`) VALUES (" . implode(', ', $values) . ");\n";
                    }
                    $sql .= "\n";
                }
            }
            $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

            $this->audit->logAction('Sistema', 'Respaldo de Base de Datos generado (PHP Nativo)');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . strlen($sql));
            echo $sql;
            exit();
        } catch (\Throwable $e) {
            $this->audit->logAction('Error', 'Respaldo fallido', $e->getMessage());
            header("Location: ../../frontend/respaldo.php?error=dump_failed");
            exit();
        }
    }

    public function handleRequest(): void {
        $action = $_GET['action'] ?? '';
        switch ($action) {
            case 'registrar_incidencia':
                $this->registrarIncidencia();
                break;
            case 'resolver_incidencia':
                $this->resolverIncidencia();
                break;
            case 'backup_db':
                $this->backupDatabase();
                break;
            default:
                header("Location: ../../frontend/index.php");
                break;
        }
    }
}

// Punto de entrada si se llama directamente a través de action
if (isset($_GET['action'])) {
    $controller = new CmmiController();
    $controller->handleRequest();
}
