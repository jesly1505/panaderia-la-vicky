<?php
require_once __DIR__ . '/../Models/ClienteModel.php';

class ClienteController {
    private $clienteModel;

    public function __construct() {
        $this->clienteModel = new ClienteModel();
    }

    public function getAll() {
        $clientes = $this->clienteModel->readAll();
        echo json_encode(['success' => true, 'data' => $clientes]);
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $nombre = $_POST['nombre'] ?? '';
        $email = $_POST['email'] ?? null;
        $telefono = $_POST['telefono'] ?? '';
        $direccion = $_POST['direccion'] ?? '';

        if (empty($nombre)) {
            echo json_encode(['success' => false, 'message' => 'Nombre del cliente obligatorio.']);
            return;
        }

        if ($this->clienteModel->create($nombre, $email, $telefono, $direccion)) {
            echo json_encode(['success' => true, 'message' => 'Cliente creado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear cliente.']);
        }
    }

    public function getHistory() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID de cliente requerido.']);
            return;
        }
        $data = $this->clienteModel->getPurchaseHistory($id);
        echo json_encode(['success' => true, 'data' => $data]);
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $id = $_POST['id'] ?? 0;
        $nombre = $_POST['nombre'] ?? '';
        $email = $_POST['email'] ?? null;
        $telefono = $_POST['telefono'] ?? '';
        $direccion = $_POST['direccion'] ?? '';

        if (empty($id) || empty($nombre)) {
            echo json_encode(['success' => false, 'message' => 'ID y Nombre obligatorios.']);
            return;
        }

        if ($this->clienteModel->update($id, $nombre, $email, $telefono, $direccion)) {
            echo json_encode(['success' => true, 'message' => 'Cliente actualizado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar cliente.']);
        }
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $id = $_POST['id'] ?? 0;
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado.']);
            return;
        }
        if ($this->clienteModel->delete($id)) {
            echo json_encode(['success' => true, 'message' => 'Cliente eliminado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar cliente (posiblemente tenga pedidos/ventas asociados).']);
        }
    }
}
?>
