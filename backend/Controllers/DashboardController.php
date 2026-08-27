<?php
namespace App\Controllers;

use App\Models\DashboardModel;
use App\Utils\Logger;

class DashboardController {
    /** @var DashboardModel */
    private DashboardModel $model;

    /**
     * @param DashboardModel $model Modelo del dashboard.
     */
    public function __construct(DashboardModel $model) {
        $this->model = $model;
    }

    /**
     * Devuelve el resumen completo del dashboard (KPIs, pedidos, alertas).
     *
     * @return void Emite JSON con datos del resumen.
     */
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
