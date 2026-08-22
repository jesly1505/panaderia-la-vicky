<?php
namespace App\Controllers;

use App\Models\DashboardModel;
use App\Utils\Logger;

class DashboardController {
    private DashboardModel $model;

    public function __construct(DashboardModel $model) {
        $this->model = $model;
    }

    public function getResumen(): void {
        header('Content-Type: application/json');
        try {
            $filter = $_GET['filter'] ?? 'today';
            $startDate = $_GET['start_date'] ?? '';
            $endDate = $_GET['end_date'] ?? '';

            $data = $this->model->getResumen($filter, $startDate, $endDate);
            echo json_encode([
                'success' => true,
                'data' => $data
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            Logger::error($e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al obtener el resumen.']);
        }
    }
}
