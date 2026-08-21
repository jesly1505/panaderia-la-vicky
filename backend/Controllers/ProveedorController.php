<?php
namespace App\Controllers;

use App\Core\AuditService;
use App\Core\Validator;
use App\Models\ProveedorModel;

class ProveedorController {
    private $proveedorModel;
    private $audit;

    public function __construct(ProveedorModel $proveedorModel, AuditService $audit) {
        $this->proveedorModel = $proveedorModel;
        $this->audit = $audit;
    }

    public function getAll() {
        $proveedores = $this->proveedorModel->readAll();
        echo json_encode(['success' => true, 'data' => $proveedores]);
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
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

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
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

        if ($this->proveedorModel->delete($id)) {
            $this->audit->log('Proveedores', 'Baja de proveedor', "Proveedor ID {$id} eliminado.");
            echo json_encode(['success' => true, 'message' => 'Proveedor eliminado.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar el proveedor.']);
        }
    }
}
?>
