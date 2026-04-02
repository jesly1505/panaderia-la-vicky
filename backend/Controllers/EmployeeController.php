<?php
require_once __DIR__ . '/../Models/UserModel.php';

class EmployeeController {
    private $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    public function getAll() {
        $this->checkAdmin();
        $employees = $this->userModel->getAll();
        echo json_encode(['success' => true, 'data' => $employees]);
    }

    public function create() {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (!$data || empty($data['nombre']) || empty($data['email']) || empty($data['password'])) {
            echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios.']);
            return;
        }

        if ($this->userModel->create($data['nombre'], $data['email'], $data['password'], $data['rol_id'] ?? 2)) {
            echo json_encode(['success' => true, 'message' => 'Empleado creado exitosamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear empleado. El correo ya podría estar en uso.']);
        }
    }

    public function delete() {
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $id = $data['id'] ?? null;

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado.']);
            return;
        }

        if ($this->userModel->delete($id)) {
            echo json_encode(['success' => true, 'message' => 'Empleado eliminado.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se puede eliminar este empleado.']);
        }
    }

    public function getStats() {
        $this->checkAdmin();
        $stats = $this->userModel->getProfitsByUser();
        echo json_encode(['success' => true, 'data' => $stats]);
    }

    private function checkAdmin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Acceso denegado. Solo administradores.']);
            exit;
        }
    }
}
?>
