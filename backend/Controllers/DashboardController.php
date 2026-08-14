<?php
namespace App\Controllers;

use App\Models\DashboardModel;

class DashboardController {
    private $model;

    public function __construct(DashboardModel $model) {
        $this->model = $model;
    }

    public function getResumen() {
        try {
            $data = $this->model->getResumen();
            echo json_encode([
                'success' => true,
                'data' => $data
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener resumen: ' . $e->getMessage()
            ]);
        }
    }
}
?>
