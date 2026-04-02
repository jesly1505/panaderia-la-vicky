<?php
require_once __DIR__ . '/../Models/ProveedorModel.php';

class ProveedorController {
    private $proveedorModel;

    public function __construct() {
        $this->proveedorModel = new ProveedorModel();
    }

    public function getAll() {
        $proveedores = $this->proveedorModel->readAll();
        echo json_encode(['success' => true, 'data' => $proveedores]);
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $nombre = trim($_POST['nombre'] ?? '');
        $contacto = trim($_POST['contacto'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($nombre)) {
            echo json_encode(['success' => false, 'message' => 'El nombre del proveedor es obligatorio.']);
            return;
        }

        if ($this->proveedorModel->create($nombre, $contacto, $telefono, $email)) {
            echo json_encode(['success' => true, 'message' => 'Proveedor registrado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al registrar el proveedor.']);
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $id = $_POST['id'] ?? 0;
        $nombre = trim($_POST['nombre'] ?? '');
        $contacto = trim($_POST['contacto'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($id) || empty($nombre)) {
            echo json_encode(['success' => false, 'message' => 'ID y nombre son obligatorios.']);
            return;
        }

        if ($this->proveedorModel->update($id, $nombre, $contacto, $telefono, $email)) {
            echo json_encode(['success' => true, 'message' => 'Proveedor actualizado.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el proveedor.']);
        }
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $id = $_POST['id'] ?? 0;
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado.']);
            return;
        }

        if ($this->proveedorModel->delete($id)) {
            echo json_encode(['success' => true, 'message' => 'Proveedor eliminado.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar el proveedor.']);
        }
    }
}
?>
