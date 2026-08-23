<?php
namespace App\Controllers;

use App\Core\AuditService;
use App\Core\Validator;
use App\Models\CatalogoModel;

class CatalogoController {
    private CatalogoModel $model;
    private AuditService $audit;

    public function __construct(CatalogoModel $model, AuditService $audit) {
        $this->model = $model;
        $this->audit = $audit;
    }

    /** GET: listar catálogo por tipo */
    public function getAll() {
        header('Content-Type: application/json');
        $tipo = $_GET['tipo'] ?? '';
        if ($tipo === '') {
            echo json_encode(['success' => false, 'message' => 'Parámetro tipo requerido.']);
            return;
        }
        $data = $this->model->getAll($tipo);
        echo json_encode(['success' => true, 'data' => $data]);
    }

    /** GET: solo activos por tipo (para selects) */
    public function getActivos() {
        header('Content-Type: application/json');
        $tipo = $_GET['tipo'] ?? '';
        if ($tipo === '') {
            echo json_encode(['success' => false, 'message' => 'Parámetro tipo requerido.']);
            return;
        }
        $data = $this->model->getByTipo($tipo);
        echo json_encode(['success' => true, 'data' => $data]);
    }

    /** POST: crear ítem de catálogo */
    public function add() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $tipo    = trim(Validator::input('tipo'));
        $valor   = trim(Validator::input('valor'));
        $etiqueta = trim(Validator::input('etiqueta'));

        $error = Validator::firstError([
            Validator::required($tipo, 'Tipo'),
            Validator::required($valor, 'Valor'),
            Validator::length($valor, 100, 'Valor'),
            Validator::required($etiqueta, 'Etiqueta'),
            Validator::length($etiqueta, 150, 'Etiqueta'),
        ]);
        if ($error) {
            echo json_encode(['success' => false, 'message' => $error]);
            return;
        }

        $id = $this->model->create($tipo, strtolower($valor), $etiqueta);
        if ($id) {
            $this->audit->log('Catálogos', 'Alta de catálogo', "Nuevo ítem [{$tipo}] {$etiqueta}");
            echo json_encode(['success' => true, 'message' => 'Ítem registrado.', 'id' => $id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al registrar. Verifique que el valor no esté duplicado.']);
        }
    }

    /** POST: editar ítem de catálogo */
    public function update() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $id       = Validator::input('id', 0);
        $etiqueta = trim(Validator::input('etiqueta'));
        $estado   = Validator::input('estado', 1);

        $error = Validator::firstError([
            Validator::integer($id, 'ID'),
            Validator::greaterThan($id, 0, 'ID'),
            Validator::required($etiqueta, 'Etiqueta'),
        ]);
        if ($error) {
            echo json_encode(['success' => false, 'message' => $error]);
            return;
        }

        if ($this->model->update($id, $etiqueta, (int)$estado)) {
            $this->audit->log('Catálogos', 'Edición de catálogo', "Ítem ID {$id} actualizado");
            echo json_encode(['success' => true, 'message' => 'Ítem actualizado.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar.']);
        }
    }

    /** POST: eliminar ítem de catálogo */
    public function delete() {
        header('Content-Type: application/json');
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

        if ($this->model->delete($id)) {
            $this->audit->log('Catálogos', 'Baja de catálogo', "Ítem ID {$id} eliminado");
            echo json_encode(['success' => true, 'message' => 'Ítem eliminado.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar.']);
        }
    }
}
