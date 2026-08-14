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

    /** GET ?route=get_gastos_by_date&fecha=YYYY-MM-DD */
    public function getByDate() {
        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        $gastos = $this->model->getByDate($fecha);
        echo json_encode(['success' => true, 'data' => $gastos]);
    }

    /** POST route=add_gasto */
    public function add() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $descripcion = trim($_POST['descripcion'] ?? '');
        $monto       = $_POST['monto'] ?? 0;
        $fecha       = $_POST['fecha'] ?? date('Y-m-d');

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

        if ($this->model->create($descripcion, $monto, $fecha)) {
            $this->audit->log('Gastos', 'Registro de gasto', "Gasto de \${$monto} - {$descripcion} ({$fecha})");
            echo json_encode(['success' => true, 'message' => 'Gasto registrado.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al registrar el gasto.']);
        }
    }

    /** POST route=delete_gasto */
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $id = $_POST['id'] ?? 0;
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
}
?>
