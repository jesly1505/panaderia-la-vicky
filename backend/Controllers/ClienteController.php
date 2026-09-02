<?php
namespace App\Controllers;

use App\Core\AuditService;
use App\Core\Validator;
use App\Utils\Logger;
use App\Models\ClienteModel;

class ClienteController {
    /** @var ClienteModel */
    private $clienteModel;
    /** @var AuditService */
    private $audit;

    /**
     * @param ClienteModel $clienteModel Modelo de clientes.
     * @param AuditService $audit        Servicio de auditoría.
     */
    public function __construct(ClienteModel $clienteModel, AuditService $audit) {
        $this->clienteModel = $clienteModel;
        $this->audit = $audit;
    }

    /**
     * Lista clientes con paginación y búsqueda.
     *
     * @return void Emite JSON con data y total.
     */
    public function getAll()
    {
        if (!isset($this->clienteModel) || !method_exists($this->clienteModel, 'readAll')) {
            Logger::error('ClienteModel not initialized or missing readAll method.');
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor al cargar clientes.']);
            return;
        }
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : null;
        if (isset($_GET['page']) && $limit > 0 && $offset === null) {
            $page = max(1, (int)$_GET['page']);
            $offset = ($page - 1) * $limit;
        } elseif ($offset === null) {
            $offset = 0;
        }
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';

        try {
            $clientes = $this->clienteModel->readAll($limit, $offset, $search);
            $total = $this->clienteModel->countAll($search);
            echo json_encode(['success' => true, 'data' => $clientes, 'total' => $total]);
        } catch (\Throwable $e) {
            Logger::error('Error fetching clientes: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor al cargar clientes.']);
        }
    }

    /**
     * Crea un nuevo cliente.
     *
     * @return void Emite JSON con resultado.
     */
    public function add() {
        header('Content-Type: application/json');
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

    /**
     * Obtiene el historial de compras de un cliente.
     *
     * @return void Emite JSON con las ventas del cliente.
     */
    public function getHistory() {
        header('Content-Type: application/json');
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID de cliente requerido.']);
            return;
        }
        $data = $this->clienteModel->getPurchaseHistory($id);
        echo json_encode(['success' => true, 'data' => $data]);
    }

    /**
     * Actualiza los datos de un cliente existente.
     *
     * @return void Emite JSON con resultado.
     */
    public function update() {
        header('Content-Type: application/json');
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

        // Check for duplicate email (en otro cliente).
        if (!empty($email) && $this->clienteModel->existsEmailExcluding($email, $id)) {
            $msg = 'Ya existe un cliente con el mismo email.';
            Logger::error($msg);
            echo json_encode(['success' => false, 'message' => $msg]);
            return;
        }
        // Check for duplicate DNI (en otro cliente).
        if (!empty($dni) && $this->clienteModel->existsDNIExcluding($dni, $id)) {
            $msg = 'Ya existe un cliente con el mismo DNI.';
            Logger::error($msg);
            echo json_encode(['success' => false, 'message' => $msg]);
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

    /**
     * Elimina un cliente (borrado lógico).
     *
     * @return void Emite JSON con resultado.
     */
    public function delete() {
        header('Content-Type: application/json');
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
