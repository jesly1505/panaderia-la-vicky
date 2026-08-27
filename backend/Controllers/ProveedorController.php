<?php
namespace App\Controllers;

use App\Core\AuditService;
use App\Core\Validator;
use App\Models\ProveedorModel;

class ProveedorController {
    private $proveedorModel;
    private $audit;

    /**
     * Constructor del controlador de proveedores.
     *
     * @param  ProveedorModel  $proveedorModel  Modelo de proveedores.
     * @param  AuditService    $audit           Servicio de auditoría.
     */
    public function __construct(ProveedorModel $proveedorModel, AuditService $audit) {
        $this->proveedorModel = $proveedorModel;
        $this->audit = $audit;
    }

    /**
     * Devuelve la lista de todos los proveedores.
     *
     * @return void  Responde con JSON con la lista de proveedores.
     */
    public function getAll() {
        header('Content-Type: application/json');
        $proveedores = $this->proveedorModel->readAll();
        echo json_encode(['success' => true, 'data' => $proveedores]);
    }

    /**
     * Registra un nuevo proveedor. POST.
     *
     * @return void  Responde con JSON indicando el resultado de la operación.
     */
    public function add() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        $nombre   = trim(Validator::input('nombre'));
        $contacto = trim(Validator::input('contacto'));
        $telefono = trim(Validator::input('telefono'));
        $email    = trim(Validator::input('email'));

        $error = Validator::firstError([
            Validator::required($nombre, 'Nombre'),
            Validator::length($nombre, 100, 'Nombre'),
            Validator::email($email, 'Email'),
            Validator::numeric($telefono, 'Teléfono'),
            Validator::length($telefono, 30, 'Teléfono', 0),
            Validator::length($contacto, 100, 'Contacto', 0),
        ]);
        if ($error) {
            echo json_encode(['success' => false, 'message' => $error]);
            return;
        }

        if ($this->proveedorModel->create($nombre, $contacto, $telefono, $email)) {
            $this->audit->log('Proveedores', 'Alta de proveedor', "Proveedor creado: {$nombre}");
            echo json_encode(['success' => true, 'message' => 'Proveedor registrado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al registrar el proveedor.']);
        }
    }

    /**
     * Actualiza un proveedor existente. POST.
     *
     * @return void  Responde con JSON indicando el resultado de la operación.
     */
    public function update() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $id       = Validator::input('id', 0);
        $nombre   = trim(Validator::input('nombre'));
        $contacto = trim(Validator::input('contacto'));
        $telefono = trim(Validator::input('telefono'));
        $email    = trim(Validator::input('email'));

        $error = Validator::firstError([
            Validator::integer($id, 'ID'),
            Validator::greaterThan($id, 0, 'ID'),
            Validator::required($nombre, 'Nombre'),
            Validator::length($nombre, 100, 'Nombre'),
            Validator::email($email, 'Email'),
            Validator::numeric($telefono, 'Teléfono'),
            Validator::length($telefono, 30, 'Teléfono', 0),
            Validator::length($contacto, 100, 'Contacto', 0),
        ]);
        if ($error) {
            echo json_encode(['success' => false, 'message' => $error]);
            return;
        }

        if ($this->proveedorModel->update($id, $nombre, $contacto, $telefono, $email)) {
            $this->audit->log('Proveedores', 'Actualización de proveedor', "Proveedor ID {$id} actualizado.");
            echo json_encode(['success' => true, 'message' => 'Proveedor actualizado.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el proveedor.']);
        }
    }

    /**
     * Devuelve una lista paginada de proveedores con búsqueda opcional. GET.
     *
     * @return void  Responde con JSON con proveedores y datos de paginación.
     *
     * @throws \Throwable  Si ocurre un error al obtener los proveedores (es capturado internamente).
     */
    public function getPaginated() {
        header('Content-Type: application/json');
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        if ($page < 1) $page = 1;
        if ($limit < 1) $limit = 10;
        $offset = ($page - 1) * $limit;
        try {
            $total = $this->proveedorModel->countAll($search);
            $proveedores = $this->proveedorModel->readPaginated($limit, $offset, $search);
            echo json_encode([
                'success' => true,
                'data' => $proveedores,
                'total' => $total,
                'page' => $page,
                'limit' => $limit
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            error_log('getPaginated error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno al obtener proveedores.']);
        }
    }

    /**
     * Elimina un proveedor por su ID. POST.
     *
     * @return void  Responde con JSON indicando el resultado de la operación.
     */
    public function delete() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        $id = Validator::input('id', 0);
        $error = Validator::firstError([
            Validator::integer($id, 'ID'),
            Validator::greaterThan($id, 0, 'ID'),
        ]);
        if ($error) {
            echo json_encode(['success' => false, 'message' => $error]);
            return;
        }
        if ($this->proveedorModel->delete($id)) {
            $this->audit->log('Proveedores', 'Baja de proveedor', "Proveedor ID {$id} eliminado.");
            echo json_encode(['success' => true, 'message' => 'Proveedor eliminado.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar el proveedor.']);
        }
    }
}
?>
