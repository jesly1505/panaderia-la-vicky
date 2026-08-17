<?php
namespace App\Controllers;

use App\Models\ReporteModel;

class ReporteController {
    private ReporteModel $model;

    public function __construct(ReporteModel $model) {
        $this->model = $model;
    }

    /** GET ?route=get_ventas_semanales */
    public function getVentasSemanales(): void {
        header('Content-Type: application/json');
        $data = $this->model->getVentasSemanales();
        echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
    }

    /** GET ?route=get_ventas_mensuales */
    public function getVentasMensuales(): void {
        header('Content-Type: application/json');
        $data = $this->model->getVentasMensuales();
        echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
    }

    /** GET ?route=get_ventas_stats_reporte */
    public function getVentasStats(): void {
        header('Content-Type: application/json');
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';

        $data = $this->model->getVentasStats($startDate, $endDate);
        echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
    }

    /** GET ?route=export_ventas_csv */
    public function exportVentasCSV(): void {
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';

        $data = $this->model->getExportVentas($startDate, $endDate);
        $filename = "reporte_ventas_" . date('Ymd_His') . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        // UTF-8 BOM para Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, ['ID Venta', 'Fecha', 'Cliente', 'Vendedor', 'Tipo de Pago', 'Total', 'Ganancias', 'Estado']);

        foreach ($data as $row) {
            fputcsv($output, [
                $row['id'],
                $row['fecha_venta'],
                $row['cliente'] ?: 'Consumidor Final',
                $row['vendedor'] ?: 'N/A',
                $row['tipo_pago'],
                $row['total'],
                $row['ganancias'],
                $row['estado']
            ]);
        }
        fclose($output);
        exit();
    }

    /** GET ?route=export_insumos_csv */
    public function exportInsumosCSV(): void {
        $data = $this->model->getExportInsumos();
        $filename = "reporte_insumos_" . date('Ymd_His') . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, ['ID', 'Nombre', 'Unidad de Medida', 'Stock Actual', 'Stock Mínimo', 'Costo Unitario']);

        foreach ($data as $row) {
            fputcsv($output, [
                $row['id'],
                $row['nombre'],
                $row['unidad_medida'],
                $row['stock_actual'],
                $row['stock_minimo'],
                $row['costo_unitario']
            ]);
        }
        fclose($output);
        exit();
    }

    /** GET ?route=export_productos_csv */
    public function exportProductosCSV(): void {
        $data = $this->model->getExportProductos();
        $filename = "reporte_productos_" . date('Ymd_His') . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, ['ID', 'Nombre', 'Categoría', 'Precio Venta', 'Costo Producción', 'Stock Actual', 'Stock Mínimo']);

        foreach ($data as $row) {
            fputcsv($output, [
                $row['id'],
                $row['nombre'],
                $row['categoria'],
                $row['precio_venta'],
                $row['costo_produccion'],
                $row['stock_actual'],
                $row['stock_minimo']
            ]);
        }
        fclose($output);
        exit();
    }

    /** GET ?route=export_gastos_csv */
    public function exportGastosCSV(): void {
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';

        $data = $this->model->getExportGastos($startDate, $endDate);
        $filename = "reporte_gastos_" . date('Ymd_His') . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, ['ID', 'Fecha', 'Categoría', 'Monto', 'Descripción']);

        foreach ($data as $row) {
            fputcsv($output, [
                $row['id'],
                $row['fecha'],
                $row['categoria'],
                $row['monto'],
                $row['descripcion']
            ]);
        }
        fclose($output);
        exit();
    }
}
