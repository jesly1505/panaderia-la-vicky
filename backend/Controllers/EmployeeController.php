<?php
namespace App\Controllers;

use App\Core\AuditService;
use App\Core\Validator;
use App\Models\PermisoModel;
use App\Models\UserModel;

class EmployeeController {
    /** @var UserModel */
    private $userModel;
    /** @var AuditService */
    private $audit;
    /** @var PermisoModel */
    private $permisoModel;

    /**
     * @param UserModel    $userModel    Modelo de usuarios.
     * @param AuditService $audit        Servicio de auditoría.
     * @param PermisoModel $permisoModel Modelo de permisos/roles.
     */
    public function __construct(UserModel $userModel, AuditService $audit, PermisoModel $permisoModel) {
        $this->userModel = $userModel;
        $this->audit = $audit;
        $this->permisoModel = $permisoModel;
    }

    /**
     * Lista empleados paginados.
     *
     * @return void Emite JSON con data, total, page y limit.
     */
    public function getAll() {
        header('Content-Type: application/json');
        $page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $employees = $this->userModel->getAll($limit, $offset);
        $total = $this->userModel->countAll();

        echo json_encode([
            'success' => true,
            'data' => $employees,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ]);
    }

    /**
     * Crea un nuevo empleado (usuario del sistema).
     *
     * @return void Emite JSON con resultado.
     */
    public function create() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (!$data || empty($data['nombre']) || empty($data['email']) || empty($data['password'])) {
            echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios.']);
            return;
        }

        $nombre   = trim($data['nombre'] ?? '');
        $email    = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $rol_id   = isset($data['rol_id']) ? (int)$data['rol_id'] : 2;

        $error = Validator::firstError([
            Validator::required($nombre, 'Nombre'),
            Validator::length($nombre, 100, 'Nombre'),
            Validator::required($email, 'Email'),
            Validator::email($email, 'Email'),
            Validator::required($password, 'Contraseña'),
            Validator::length($password, 255, 'Contraseña', 6),
            Validator::integer($rol_id, 'Rol'),
            Validator::greaterThan($rol_id, 0, 'Rol'),
        ]);
        // Validate that the role exists
        if (!$this->permisoModel->rolExists($rol_id)) {
            echo json_encode(['success' => false, 'message' => 'Rol no válido o no autorizado.']);
            return;
        }
        if ($error) {
            echo json_encode(['success' => false, 'message' => $error]);
            return;
        }

        if ($this->userModel->create($nombre, $email, $password, $rol_id)) {
            $this->audit->log('Empleados', 'Alta de empleado', "Empleado creado: {$email} ({$nombre})");
            echo json_encode(['success' => true, 'message' => 'Empleado creado exitosamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear empleado. El correo ya podría estar en uso.']);
        }
    }

    /**
     * Elimina un empleado (borrado lógico). No permite eliminar el admin principal.
     *
     * @return void Emite JSON con resultado.
     */
    public function delete() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $id = $data['id'] ?? null;

        $error = Validator::firstError([
            Validator::required($id, 'ID'),
            Validator::integer($id, 'ID'),
            Validator::greaterThan($id, 0, 'ID'),
        ]);
        if ($error) {
            echo json_encode(['success' => false, 'message' => $error]);
            return;
        }

        if ($this->userModel->delete($id)) {
            $this->audit->log('Empleados', 'Baja de empleado', "Empleado con ID {$id} eliminado.");
            echo json_encode(['success' => true, 'message' => 'Empleado eliminado.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se puede eliminar este empleado.']);
        }
    }

    /**
     * Actualiza nombre, email y rol de un empleado.
     *
     * @return void Emite JSON con resultado.
     */
    public function update() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        $id      = $data['id'] ?? null;
        $nombre  = trim($data['nombre'] ?? '');
        $email   = trim($data['email'] ?? '');
        $rol_id  = $data['rol_id'] ?? 2;

        $error = Validator::firstError([
            Validator::required($id, 'ID'),
            Validator::integer($id, 'ID'),
            Validator::greaterThan($id, 0, 'ID'),
            Validator::required($nombre, 'Nombre'),
            Validator::length($nombre, 100, 'Nombre'),
            Validator::required($email, 'Email'),
            Validator::email($email, 'Email'),
            Validator::integer($rol_id, 'Rol'),
            Validator::greaterThan($rol_id, 0, 'Rol'),
        ]);
        if ($error) {
            echo json_encode(['success' => false, 'message' => $error]);
            return;
        }

        if (!$this->permisoModel->rolExists($rol_id)) {
            echo json_encode(['success' => false, 'message' => 'Rol no válido o no autorizado.']);
            return;
        }

        if ($this->userModel->update($id, $nombre, $email, $rol_id)) {
            $this->audit->log('Empleados', 'Actualización de empleado', "Empleado ID {$id} actualizado: {$email} ({$nombre})");
            echo json_encode(['success' => true, 'message' => 'Empleado actualizado exitosamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar empleado. El correo ya podría estar en uso.']);
        }
    }

    /**
     * Devuelve estadísticas de ganancias por empleado.
     *
     * @return void Emite JSON con la lista de ganancias.
     */
    public function getStats() {
        header('Content-Type: application/json');
        $stats = $this->userModel->getProfitsByUser();
        echo json_encode(['success' => true, 'data' => $stats]);
    }
}
