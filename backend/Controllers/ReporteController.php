<?php
require_once __DIR__ . '/../Models/ReporteModel.php';

class ReporteController {
    private $model;

    public function __construct() {
        $this->model = new ReporteModel();
    }

    /** GET ?route=get_ventas_semanales */
    public function getVentasSemanales() {
        $data = $this->model->getVentasSemanales();
        echo json_encode(['success' => true, 'data' => $data]);
    }

    /** GET ?route=get_ventas_mensuales */
    public function getVentasMensuales() {
        $data = $this->model->getVentasMensuales();
        echo json_encode(['success' => true, 'data' => $data]);
    }
}
?>
