<?php
namespace App\Controllers;

use App\Core\AuditService;
use App\Core\Validator;
use App\Models\PermisoModel;

class PermisoController {
    private $permisoModel;
    private $audit;

    public function __construct(PermisoModel $permisoModel, AuditService $audit) {
        $this->permisoModel = $permisoModel;
        $this->audit = $audit;
    }

    /** Catálogo de permisos (para la UI de configuración). */
    public function getPermisos() {
        header('Content-Type: application/json');
        $permisos = $this->permisoModel->getAll();
        echo json_encode(['success' => true, 'data' => $permisos]);
    }

    /** Roles existentes. */
    public function getRoles() {
        header('Content-Type: application/json');
        $roles = $this->permisoModel->getRoles();
        echo json_encode(['success' => true, 'data' => $roles]);
    }

    /** Permisos asignados a un rol (GET ?rol_id=N). */
    public function getPermisosRol() {
        header('Content-Type: application/json');
        $rol_id = $_GET['rol_id'] ?? 0;
        $error = Validator::firstError([
            Validator::integer($rol_id, 'Rol'),
            Validator::greaterThan($rol_id, 0, 'Rol'),
        ]);
        if ($error) {
            echo json_encode(['success' => false, 'message' => $error]);
            return;
        }
        $permisos = $this->permisoModel->getPermisosByRol((int)$rol_id);
        echo json_encode(['success' => true, 'data' => $permisos]);
    }

    /** Asigna permisos a un rol (POST JSON { rol_id, permisos: [codigos] }). */
    public function setPermisosRol() {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
            return;
        }

        $rol_id = $data['rol_id'] ?? 0;
        $permisos = $data['permisos'] ?? [];

        $error = Validator::firstError([
            Validator::integer($rol_id, 'Rol'),
            Validator::greaterThan($rol_id, 0, 'Rol'),
        ]);
        if ($error) {
            echo json_encode(['success' => false, 'message' => $error]);
            return;
        }

        $rol_id = (int)$rol_id;
        $permisos = array_values(array_unique(array_filter(array_map('trim', $permisos))));

        if ($this->permisoModel->setPermisosRol($rol_id, $permisos)) {
            $rol = null;
            foreach ($this->permisoModel->getRoles() as $r) {
                if ((int)$r['id'] === $rol_id) {
                    $rol = $r['nombre'];
                    break;
                }
            }
            $this->audit->log('Permisos', 'Actualización de permisos',
                "Permisos actualizados para el rol {$rol} (ID {$rol_id}): " . implode(', ', $permisos));
            echo json_encode(['success' => true, 'message' => 'Permisos actualizados correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar los permisos.']);
        }
    }

    /** Crea un rol (POST JSON { nombre, descripcion }). */
    public function crearRol() {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
            return;
        }

        $nombre = trim($data['nombre'] ?? '');
        $descripcion = trim($data['descripcion'] ?? '');

        $error = Validator::firstError([
            Validator::required($nombre, 'Nombre del rol'),
            Validator::length($nombre, 50, 'Nombre del rol'),
            Validator::length($descripcion, 255, 'Descripción', 0),
        ]);
        if ($error) {
            echo json_encode(['success' => false, 'message' => $error]);
            return;
        }

        if ($this->permisoModel->rolNombreExiste($nombre)) {
            echo json_encode(['success' => false, 'message' => 'Ya existe un rol con ese nombre.']);
            return;
        }

        $id = $this->permisoModel->createRol($nombre, $descripcion);
        $this->audit->log('Permisos', 'Creación de rol', "Rol creado: {$nombre} (ID {$id})");
        echo json_encode(['success' => true, 'message' => 'Rol creado correctamente.', 'id' => $id]);
    }

    /** Actualiza un rol (POST JSON { id, nombre, descripcion }). */
    public function editarRol() {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
            return;
        }

        $id = $data['id'] ?? 0;
        $nombre = trim($data['nombre'] ?? '');
        $descripcion = trim($data['descripcion'] ?? '');

        $error = Validator::firstError([
            Validator::integer($id, 'Rol'),
            Validator::greaterThan($id, 0, 'Rol'),
            Validator::required($nombre, 'Nombre del rol'),
            Validator::length($nombre, 50, 'Nombre del rol'),
            Validator::length($descripcion, 255, 'Descripción', 0),
        ]);
        if ($error) {
            echo json_encode(['success' => false, 'message' => $error]);
            return;
        }

        if ($this->permisoModel->rolNombreExiste($nombre, (int)$id)) {
            echo json_encode(['success' => false, 'message' => 'Ya existe otro rol con ese nombre.']);
            return;
        }

        if ($this->permisoModel->updateRol((int)$id, $nombre, $descripcion)) {
            $this->audit->log('Permisos', 'Edición de rol', "Rol actualizado: {$nombre} (ID {$id})");
            echo json_encode(['success' => true, 'message' => 'Rol actualizado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el rol.']);
        }
    }

    /** Elimina un rol (POST JSON { id }). Protege Administrador y roles en uso. */
    public function eliminarRol() {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
            return;
        }

        $id = $data['id'] ?? 0;
        $error = Validator::firstError([
            Validator::integer($id, 'Rol'),
            Validator::greaterThan($id, 0, 'Rol'),
        ]);
        if ($error) {
            echo json_encode(['success' => false, 'message' => $error]);
            return;
        }
        $id = (int)$id;

        if ($id === 1) {
            echo json_encode(['success' => false, 'message' => 'El rol Administrador no se puede eliminar.']);
            return;
        }

        if ($this->permisoModel->getUserCountByRol($id) > 0) {
            echo json_encode(['success' => false, 'message' => 'No se puede eliminar: el rol tiene usuarios asignados.']);
            return;
        }

        if ($this->permisoModel->deleteRol($id)) {
            $this->audit->log('Permisos', 'Eliminación de rol', "Rol eliminado (ID {$id})");
            echo json_encode(['success' => true, 'message' => 'Rol eliminado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar el rol.']);
        }
    }
}
