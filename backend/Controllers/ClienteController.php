<?php
namespace App\Controllers;

use App\Core\AuditService;
use App\Core\Validator;
use App\Models\ClienteModel;

class ClienteController {
    private $clienteModel;
    private $audit;

    public function __construct(ClienteModel $clienteModel, AuditService $audit) {
        $this->clienteModel = $clienteModel;
        $this->audit = $audit;
    }

    public function getAll() {
        $clientes = $this->clienteModel->readAll();
        echo json_encode(['success' => true, 'data' => $clientes]);
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $nombre = Validator::input('nombre');
        $email = Validator::input('email', null);
        $telefono = Validator::input('telefono');
        $direccion = Validator::input('direccion');

        $error = Validator::firstError([
            Validator::required($nombre, 'Nombre'),
            Validator::length($nombre, 100, 'Nombre'),
            Validator::email($email, 'Email'),
            Validator::length($telefono, 30, 'Teléfono', 0),
            Validator::length($direccion, 255, 'Dirección', 0),
        ]);
        if ($error) {
            echo json_encode(['success' => false, 'message' => $error]);
            return;
        }

        if ($this->clienteModel->create($nombre, $email, $telefono, $direccion)) {
            $this->audit->log('Clientes', 'Alta de cliente', "Cliente creado: {$nombre}");
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
        
        $id = Validator::input('id', 0);
        $nombre = Validator::input('nombre');
        $email = Validator::input('email', null);
        $telefono = Validator::input('telefono');
        $direccion = Validator::input('direccion');

        $error = Validator::firstError([
            Validator::integer($id, 'ID'),
            Validator::greaterThan($id, 0, 'ID'),
            Validator::required($nombre, 'Nombre'),
            Validator::length($nombre, 100, 'Nombre'),
            Validator::email($email, 'Email'),
            Validator::length($telefono, 30, 'Teléfono', 0),
            Validator::length($direccion, 255, 'Dirección', 0),
        ]);
        if ($error) {
            echo json_encode(['success' => false, 'message' => $error]);
            return;
        }

        if ($this->clienteModel->update($id, $nombre, $email, $telefono, $direccion)) {
            $this->audit->log('Clientes', 'Actualización de cliente', "Cliente ID {$id} actualizado.");
            echo json_encode(['success' => true, 'message' => 'Cliente actualizado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar cliente.']);
        }
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $id = Validator::input('id', 0);
        $error = Validator::firstError([
            Validator::integer($id, 'ID'),
            Validator::greaterThan($id, 0, 'ID'),
        ]);
        if ($error) {
            echo json_encode(['success' => false, 'message' => $error]);
            return;
        }
        if ($this->clienteModel->delete($id)) {
            $this->audit->log('Clientes', 'Baja de cliente', "Cliente ID {$id} eliminado.");
            echo json_encode(['success' => true, 'message' => 'Cliente eliminado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar cliente (posiblemente tenga pedidos/ventas asociados).']);
        }
    }
}
?>
