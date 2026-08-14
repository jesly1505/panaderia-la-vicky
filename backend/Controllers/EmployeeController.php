<?php
namespace App\Controllers;

use App\Core\AuditService;
use App\Models\UserModel;

class EmployeeController {
    private $userModel;
    private $audit;

    public function __construct(UserModel $userModel, AuditService $audit) {
        $this->userModel = $userModel;
        $this->audit = $audit;
    }

    public function getAll() {
        $employees = $this->userModel->getAll();
        echo json_encode(['success' => true, 'data' => $employees]);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (!$data || empty($data['nombre']) || empty($data['email']) || empty($data['password'])) {
            echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios.']);
            return;
        }

        if ($this->userModel->create($data['nombre'], $data['email'], $data['password'], $data['rol_id'] ?? 2)) {
            $this->audit->log('Empleados', 'Alta de empleado', "Empleado creado: {$data['email']} ({$data['nombre']})");
            echo json_encode(['success' => true, 'message' => 'Empleado creado exitosamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear empleado. El correo ya podría estar en uso.']);
        }
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $id = $data['id'] ?? null;

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado.']);
            return;
        }

        if ($this->userModel->delete($id)) {
            $this->audit->log('Empleados', 'Baja de empleado', "Empleado con ID {$id} eliminado.");
            echo json_encode(['success' => true, 'message' => 'Empleado eliminado.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se puede eliminar este empleado.']);
        }
    }

    public function getStats() {
        $stats = $this->userModel->getProfitsByUser();
        echo json_encode(['success' => true, 'data' => $stats]);
    }
}
