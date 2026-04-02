<?php
require_once __DIR__ . '/../Models/GastoModel.php';

class GastoController {
    private $model;

    public function __construct() {
        $this->model = new GastoModel();
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

        if (empty($descripcion) || $monto <= 0) {
            echo json_encode(['success' => false, 'message' => 'Descripción y monto son obligatorios.']);
            return;
        }

        if ($this->model->create($descripcion, $monto, $fecha)) {
            echo json_encode(['success' => true, 'message' => 'Gasto registrado.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al registrar el gasto.']);
        }
    }

    /** POST route=delete_gasto */
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $id = $_POST['id'] ?? 0;
        if ($this->model->delete($id)) {
            echo json_encode(['success' => true, 'message' => 'Gasto eliminado.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar el gasto.']);
        }
    }
}
?>
