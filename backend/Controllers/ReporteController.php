<?php
namespace App\Controllers;

use App\Models\ReporteModel;

class ReporteController {
    private $model;

    public function __construct(ReporteModel $model) {
        $this->model = $model;
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
