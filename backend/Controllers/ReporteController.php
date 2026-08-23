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

    private function renderPdfHtml(string $title, string $tableHeaders, string $tableRows): void {
        $fecha = date('d/m/Y H:i');
        echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>' . htmlspecialchars($title) . '</title>';
        echo '<style>
            * { margin:0; padding:0; box-sizing:border-box; }
            body { font-family:Arial,Helvetica,sans-serif; font-size:11pt; color:#333; padding:20mm; }
            .header { text-align:center; border-bottom:2px solid #c0560f; padding-bottom:10px; margin-bottom:20px; }
            .header h1 { font-size:18pt; color:#c0560f; margin-bottom:4px; }
            .header p { font-size:10pt; color:#777; }
            table { width:100%; border-collapse:collapse; margin-top:15px; }
            th { background:#c0560f; color:#fff; padding:8px 10px; text-align:left; font-size:9pt; text-transform:uppercase; }
            td { padding:7px 10px; border-bottom:1px solid #e0e0e0; font-size:10pt; }
            tr:nth-child(even) { background:#fdf5ef; }
            .total { font-weight:bold; background:#fdf5ef; }
            .footer { text-align:center; margin-top:30px; padding-top:10px; border-top:1px solid #ddd; font-size:8pt; color:#999; }
            @media print { body { padding:15mm; } @page { margin:15mm; } }
        </style></head><body>';
        echo '<div class="header"><h1>Panadería La Vicky</h1><p>' . htmlspecialchars($title) . '</p>';
        echo '<p>Generado: ' . $fecha . '</p></div>';
        echo '<table><thead><tr>' . $tableHeaders . '</tr></thead><tbody>' . $tableRows . '</tbody></table>';
        echo '<div class="footer">Sistema de Gestión Panadería La Vicky — Documento generado automáticamente</div>';
        echo '</body></html>';
        exit();
    }

    /** GET ?route=export_ventas_pdf */
    public function exportVentasPDF(): void {
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';
        $data = $this->model->getExportVentas($startDate, $endDate);

        $headers = '<th>ID</th><th>Fecha</th><th>Cliente</th><th>Vendedor</th><th>Pago</th><th>Total</th><th>Ganancias</th><th>Estado</th>';
        $rows = '';
        $totalGeneral = 0;
        $gananciasGeneral = 0;
        foreach ($data as $r) {
            $totalGeneral += (float)$r['total'];
            $gananciasGeneral += (float)$r['ganancias'];
            $estado = $r['estado'] === 'cancelado' ? '<span style="color:#dc3545">Cancelado</span>' : '<span style="color:#198754">Completado</span>';
            $rows .= '<tr><td>' . (int)$r['id'] . '</td><td>' . htmlspecialchars($r['fecha_venta']) . '</td>';
            $rows .= '<td>' . htmlspecialchars($r['cliente'] ?: 'Consumidor Final') . '</td>';
            $rows .= '<td>' . htmlspecialchars($r['vendedor'] ?: 'N/A') . '</td>';
            $rows .= '<td>' . htmlspecialchars($r['tipo_pago']) . '</td>';
            $rows .= '<td>$' . number_format((float)$r['total'], 2) . '</td>';
            $rows .= '<td>$' . number_format((float)$r['ganancias'], 2) . '</td><td>' . $estado . '</td></tr>';
        }
        $rows .= '<tr class="total"><td colspan="5">TOTALES</td><td>$' . number_format($totalGeneral, 2) . '</td><td>$' . number_format($gananciasGeneral, 2) . '</td><td></td></tr>';

        $this->renderPdfHtml('Reporte de Ventas', $headers, $rows);
    }

    /** GET ?route=export_insumos_pdf */
    public function exportInsumosPDF(): void {
        $data = $this->model->getExportInsumos();

        $headers = '<th>ID</th><th>Nombre</th><th>Unidad</th><th>Stock Actual</th><th>Stock Mínimo</th><th>Costo Unitario</th>';
        $rows = '';
        foreach ($data as $r) {
            $rows .= '<tr><td>' . (int)$r['id'] . '</td><td>' . htmlspecialchars($r['nombre']) . '</td>';
            $rows .= '<td>' . htmlspecialchars($r['unidad_medida']) . '</td>';
            $rows .= '<td>' . number_format((float)$r['stock_actual'], 2) . '</td>';
            $rows .= '<td>' . number_format((float)$r['stock_minimo'], 2) . '</td>';
            $rows .= '<td>$' . number_format((float)$r['precio_costo'], 2) . '</td></tr>';
        }

        $this->renderPdfHtml('Reporte de Insumos', $headers, $rows);
    }

    /** GET ?route=export_productos_pdf */
    public function exportProductosPDF(): void {
        $data = $this->model->getExportProductos();

        $headers = '<th>ID</th><th>Nombre</th><th>Categoría</th><th>Precio Venta</th><th>Costo Producción</th><th>Stock</th><th>Stock Mín.</th>';
        $rows = '';
        foreach ($data as $r) {
            $rows .= '<tr><td>' . (int)$r['id'] . '</td><td>' . htmlspecialchars($r['nombre']) . '</td>';
            $rows .= '<td>' . htmlspecialchars($r['categoria']) . '</td>';
            $rows .= '<td>$' . number_format((float)$r['precio_venta'], 2) . '</td>';
            $rows .= '<td>$' . number_format((float)$r['costo_produccion'], 2) . '</td>';
            $rows .= '<td>' . number_format((float)$r['stock_actual'], 2) . '</td>';
            $rows .= '<td>' . number_format((float)$r['stock_minimo'], 2) . '</td></tr>';
        }

        $this->renderPdfHtml('Reporte de Productos', $headers, $rows);
    }

    /** GET ?route=export_gastos_pdf */
    public function exportGastosPDF(): void {
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';
        $data = $this->model->getExportGastos($startDate, $endDate);

        $headers = '<th>ID</th><th>Fecha</th><th>Categoría</th><th>Monto</th><th>Descripción</th>';
        $rows = '';
        $totalGeneral = 0;
        foreach ($data as $r) {
            $totalGeneral += (float)$r['monto'];
            $rows .= '<tr><td>' . (int)$r['id'] . '</td><td>' . htmlspecialchars($r['fecha']) . '</td>';
            $rows .= '<td>' . htmlspecialchars($r['categoria']) . '</td>';
            $rows .= '<td>$' . number_format((float)$r['monto'], 2) . '</td>';
            $rows .= '<td>' . htmlspecialchars($r['descripcion']) . '</td></tr>';
        }
        $rows .= '<tr class="total"><td colspan="3">TOTAL GASTOS</td><td>$' . number_format($totalGeneral, 2) . '</td><td></td></tr>';

        $this->renderPdfHtml('Reporte de Gastos', $headers, $rows);
    }
}
