<?php
namespace App\Controllers;

use App\Core\AuditService;
use App\Core\Validator;
use App\Utils\Logger;
use App\Models\ClienteModel;

class ClienteController {
    private $clienteModel;
    private $audit;

    public function __construct(ClienteModel $clienteModel, AuditService $audit) {
        $this->clienteModel = $clienteModel;
        $this->audit = $audit;
    }

    public function getAll()
    {
        if (!isset($this->clienteModel) || !method_exists($this->clienteModel, 'readAll')) {
            Logger::error('ClienteModel not initialized or missing readAll method.');
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor al cargar clientes.']);
            return;
        }
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100; // default limit
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
        try {
            $clientes = $this->clienteModel->readAll($limit, $offset);
            $total = $this->clienteModel->countAll();
            echo json_encode(['success' => true, 'data' => $clientes, 'total' => $total]);
        } catch (\Throwable $e) {
            Logger::error('Error fetching clientes: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor al cargar clientes.']);
        }
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $nombre = Validator::input('nombre');
        $email = Validator::input('email', null);
        $telefono = Validator::input('telefono');
        $direccion = Validator::input('direccion');
        $dni = Validator::input('dni');

        $error = Validator::firstError([
            Validator::required($nombre, 'Nombre'),
            Validator::length($nombre, 100, 'Nombre'),
            Validator::email($email, 'Email'),
            Validator::required($dni, 'DNI'),
            Validator::length($dni, 20, 'DNI', 0),
            Validator::length($telefono, 30, 'Teléfono', 0),
            Validator::length($direccion, 255, 'Dirección', 0),
        ]);
        if ($error) {
            Logger::error('Validación falló al crear cliente: ' . $error);
            echo json_encode(['success' => false, 'message' => $error]);
            return;
        }

        // Check for duplicate email
        if (!empty($email) && $this->clienteModel->existsEmail($email)) {
            $msg = 'Ya existe un cliente con el mismo email.';
            Logger::error($msg);
            echo json_encode(['success' => false, 'message' => $msg]);
            return;
        }
        // Check for duplicate DNI
        if (!empty($dni) && $this->clienteModel->existsDNI($dni)) {
            $msg = 'Ya existe un cliente con el mismo DNI.';
            Logger::error($msg);
            echo json_encode(['success' => false, 'message' => $msg]);
            return;
        }

        if ($this->clienteModel->create($nombre, $email, $telefono, $direccion, $dni)) {
            $this->audit->log('Clientes', 'Alta de cliente', "Cliente creado: {$nombre}");
            echo json_encode(['success' => true, 'message' => 'Cliente creado correctamente.']);
        } else {
            Logger::error('Error al crear cliente en la base de datos.');
                echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
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
        $dni = Validator::input('dni');

        $error = Validator::firstError([
            Validator::integer($id, 'ID'),
            Validator::greaterThan($id, 0, 'ID'),
            Validator::required($nombre, 'Nombre'),
            Validator::length($nombre, 100, 'Nombre'),
            Validator::email($email, 'Email'),
            Validator::required($dni, 'DNI'),
            Validator::length($dni, 20, 'DNI', 0),
            Validator::length($telefono, 30, 'Teléfono', 0),
            Validator::length($direccion, 255, 'Dirección', 0),
        ]);
        if ($error) {
            Logger::error('Validación falló al actualizar cliente: ' . $error);
            echo json_encode(['success' => false, 'message' => $error]);
            return;
        }

        if ($this->clienteModel->update($id, $nombre, $email, $telefono, $direccion, $dni)) {
            $this->audit->log('Clientes', 'Actualización de cliente', "Cliente ID {$id} actualizado.");
            echo json_encode(['success' => true, 'message' => 'Cliente actualizado correctamente.']);
        } else {
            Logger::error('Error al actualizar cliente en la base de datos.');
                echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
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
            Logger::error('Validación falló al eliminar cliente.');
                echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
            return;
        }
        if ($this->clienteModel->delete($id)) {
            $this->audit->log('Clientes', 'Baja de cliente', "Cliente ID {$id} eliminado.");
            echo json_encode(['success' => true, 'message' => 'Cliente eliminado correctamente.']);
        } else {
            Logger::error('Error al eliminar cliente: posible referencia en ventas/pedidos.');
                echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
        }
    }
}
?>
