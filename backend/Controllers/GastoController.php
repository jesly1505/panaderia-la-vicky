<?php
namespace App\Controllers;

use App\Core\AuditService;
use App\Core\Money;
use App\Core\Validator;
use App\Models\GastoModel;

class GastoController {
    private $model;
    private $audit;

    public function __construct(GastoModel $model, AuditService $audit) {
        $this->model = $model;
        $this->audit = $audit;
    }

    /** GET ?route=get_gastos_by_date&start_date=YYYY-MM-DD&end_date=YYYY-MM-DD */
    public function getByDate() {
        header('Content-Type: application/json');
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';
        $gastos = $this->model->getByDate($startDate, $endDate);
        echo json_encode(['success' => true, 'data' => $gastos]);
    }

    /** POST route=add_gasto */
    public function add() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $descripcion = trim(Validator::input('descripcion'));
        $monto       = Validator::input('monto', 0);
        $fecha       = Validator::input('fecha', date('Y-m-d'));
        $categoria   = trim(Validator::input('categoria')) ?: 'General';

        $error = Validator::firstError([
            Validator::required($descripcion, 'Descripción'),
            Validator::length($descripcion, 255, 'Descripción'),
            Validator::numeric($monto, 'Monto'),
            Validator::greaterThan($monto, 0, 'Monto'),
            Validator::date($fecha, 'Fecha'),
        ]);

        if ($error) {
            echo json_encode(['success' => false, 'message' => $error]);
            return;
        }

        $monto = Money::round($monto);

        if ($this->model->create($descripcion, $monto, $fecha, $categoria)) {
            $this->audit->log('Gastos', 'Registro de gasto', "Gasto de \${$monto} - {$descripcion} ({$fecha}) [{$categoria}]");
            echo json_encode(['success' => true, 'message' => 'Gasto registrado.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al registrar el gasto.']);
        }
    }

    /** POST route=delete_gasto */
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
            $this->audit->log('Gastos', 'Eliminación de gasto', "Gasto ID {$id} eliminado.");
            echo json_encode(['success' => true, 'message' => 'Gasto eliminado.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar el gasto.']);
        }
    }

    /** POST route=update_gasto */
    public function update() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $id          = Validator::input('id', 0);
        $descripcion = trim(Validator::input('descripcion'));
        $monto       = Validator::input('monto', 0);
        $fecha       = Validator::input('fecha', date('Y-m-d'));
        $categoria   = trim(Validator::input('categoria'));

        $error = Validator::firstError([
            Validator::integer($id, 'ID'),
            Validator::greaterThan($id, 0, 'ID'),
            Validator::required($descripcion, 'Descripción'),
            Validator::length($descripcion, 255, 'Descripción'),
            Validator::numeric($monto, 'Monto'),
            Validator::greaterThan($monto, 0, 'Monto'),
            Validator::date($fecha, 'Fecha'),
            Validator::required($categoria, 'Categoría'),
        ]);

        if ($error) {
            echo json_encode(['success' => false, 'message' => $error]);
            return;
        }

        $monto = Money::round($monto);

        if ($this->model->update($id, $descripcion, $monto, $fecha, $categoria)) {
            $this->audit->log('Gastos', 'Actualización de gasto', "Gasto ID {$id} actualizado: \${$monto} - {$descripcion} ({$fecha})");
            echo json_encode(['success' => true, 'message' => 'Gasto actualizado.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el gasto.']);
        }
    }
}
?>
