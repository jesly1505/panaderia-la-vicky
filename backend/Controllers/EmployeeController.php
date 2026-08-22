<?php
namespace App\Controllers;

use App\Core\AuditService;
use App\Core\Validator;
use App\Models\UserModel;

class EmployeeController {
    private $userModel;
    private $audit;

    public function __construct(UserModel $userModel, AuditService $audit) {
        $this->userModel = $userModel;
        $this->audit = $audit;
    }

    public function getAll() {
        header('Content-Type: application/json');
        $employees = $this->userModel->getAll();
        echo json_encode(['success' => true, 'data' => $employees]);
    }

    public function create() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (!$data || empty($data['nombre']) || empty($data['email']) || empty($data['password'])) {
            echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios.']);
            return;
        }

        $nombre   = $data['nombre'];
        $email    = $data['email'];
        $password = $data['password'];
        $rol_id   = $data['rol_id'] ?? 2;

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

        if ($this->userModel->update($id, $nombre, $email, $rol_id)) {
            $this->audit->log('Empleados', 'Actualización de empleado', "Empleado ID {$id} actualizado: {$email} ({$nombre})");
            echo json_encode(['success' => true, 'message' => 'Empleado actualizado exitosamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar empleado. El correo ya podría estar en uso.']);
        }
    }

    public function getStats() {
        header('Content-Type: application/json');
        $stats = $this->userModel->getProfitsByUser();
        echo json_encode(['success' => true, 'data' => $stats]);
    }
}
