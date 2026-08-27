<?php
namespace App\Controllers;

use App\Core\AuditService;
use App\Core\Validator;
use App\Models\VentaModel;

/**
 * Controlador encargado de gestionar las operaciones relacionadas con ventas.
 *
 * Provee endpoints para listar, crear, cancelar ventas directas, obtener
 * detalles de una venta y datos agregados para reportes y dashboards.
 */
class VentaController {
    private $ventaModel;
    private $audit;

    /** Métodos de pago permitidos. */
    private const METODOS_PAGO = ['efectivo', 'tarjeta', 'transferencia', 'otro'];

    /**
     * Inyección de dependencias del controller.
     *
     * @param VentaModel   $ventaModel Modelo de acceso a datos de ventas.
     * @param AuditService $audit      Servicio de auditoría para registrar acciones.
     */
    public function __construct(VentaModel $ventaModel, AuditService $audit) {
        $this->ventaModel = $ventaModel;
        $this->audit = $audit;
    }

    /**
     * Obtiene la lista paginada de ventas con filtros y totales del periodo.
     *
     * Responde con JSON que contiene el array de ventas, el total de registros
     * y los totales de ingresos y ganancias del periodo filtrado.
     *
     * @return void Salida directa en formato JSON.
     */
    public function getAll(): void {
        header('Content-Type: application/json');
        $filter = $_GET['filter'] ?? 'all';
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $estado = isset($_GET['estado']) ? trim($_GET['estado']) : '';
        $tipoPago = isset($_GET['tipo_pago']) ? trim($_GET['tipo_pago']) : '';
        $vendedor = isset($_GET['vendedor']) ? trim($_GET['vendedor']) : '';

        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : null;
        if (isset($_GET['page']) && $limit !== null && $offset === null) {
            $page = max(1, (int)$_GET['page']);
            $offset = ($page - 1) * $limit;
        }

        $ventas = $this->ventaModel->readAll($filter, $startDate, $endDate, $limit, $offset, $search, $estado, $tipoPago, $vendedor);
        $total = $this->ventaModel->countAll($filter, $startDate, $endDate, $search, $estado, $tipoPago, $vendedor);
        $totals = $this->ventaModel->getTotals($filter, $startDate, $endDate, $search, $estado, $tipoPago, $vendedor);

        echo json_encode([
            'success' => true, 
            'data' => $ventas, 
            'total' => $total, 
            'totales_periodo' => $totals
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Retorna la lista de vendedores (usuarios) registrados en el sistema.
     *
     * @return void Salida directa en formato JSON con la lista de vendedores.
     */
    public function getVendedores(): void {
        header('Content-Type: application/json');
        $vendedores = $this->ventaModel->getVendedores();
        echo json_encode(['success' => true, 'data' => $vendedores], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Registra una venta directa (sin pedido previo) a partir de los datos enviados
     * en el body de la petición POST.
     *
     * Valida los campos obligatorios del carrito y los pagos, crea la venta en
     * base de datos, descuenta el inventario y registra la auditoría.
     *
     * @return void Salida directa en formato JSON con el resultado de la operación.
     */
    public function createDirecta() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data || empty($data['detalles'])) {
            echo json_encode(['success' => false, 'message' => 'Faltan datos de la venta o carrito vacío.']);
            return;
        }

        $errors = [
            Validator::numeric($data['total'] ?? 0, 'Total'),
            Validator::min($data['total'] ?? 0, 0, 'Total'),
        ];
        if (isset($data['cliente_id']) && $data['cliente_id'] !== null && $data['cliente_id'] !== '') {
            $errors[] = Validator::integer($data['cliente_id'], 'Cliente');
            $errors[] = Validator::greaterThan((int)$data['cliente_id'], 0, 'Cliente');
        }
        foreach ($data['detalles'] as $i => $d) {
            $pid = $d['producto_id'] ?? $d['id'] ?? 0;
            $errors[] = Validator::integer($pid, "Producto #" . ($i + 1));
            $errors[] = Validator::greaterThan($pid, 0, "Producto #" . ($i + 1));
            $errors[] = Validator::numeric($d['cantidad'] ?? 0, "Cantidad del producto #" . ($i + 1));
            $errors[] = Validator::greaterThan($d['cantidad'] ?? 0, 0, "Cantidad del producto #" . ($i + 1));
            $errors[] = Validator::numeric($d['precio_unitario'] ?? $d['precio'] ?? 0, "Precio del producto #" . ($i + 1));
            $errors[] = Validator::min($d['precio_unitario'] ?? $d['precio'] ?? 0, 0, "Precio del producto #" . ($i + 1));
        }
        foreach (($data['pagos'] ?? []) as $i => $p) {
            $errors[] = Validator::numeric($p['monto'] ?? 0, "Monto del pago #" . ($i + 1));
            $errors[] = Validator::greaterThan($p['monto'] ?? 0, 0, "Monto del pago #" . ($i + 1));
            $errors[] = Validator::inList($p['metodo'] ?? '', self::METODOS_PAGO, "Método del pago #" . ($i + 1));
        }

        $error = Validator::firstError($errors);
        if ($error) {
            echo json_encode(['success' => false, 'message' => $error]);
            return;
        }

        // El modelo espera el array completo $data con subtotales, pagos, etc.
        // El usuario se inyecta desde la sesión (el modelo no depende de $_SESSION).
        $result = $this->ventaModel->createDirecta($data, $_SESSION['usuario_id'] ?? null);
        if ($result) {
            $this->audit->log('Ventas', 'Registro de venta directa', "Venta #{$result} registrada por \$" . number_format($data['total'] ?? 0, 2));
            echo json_encode(['success' => true, 'message' => 'Venta registrada exitosamente.', 'venta_id' => $result]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al registrar venta.']);
        }
    }

    /**
     * Cancela una venta existente, revierte el inventario asociado y actualiza
     * los estados de la venta y sus pagos.
     *
     * Espera un body JSON con el campo "id" correspondiente al identificador
     * de la venta a cancelar.
     *
     * @return void Salida directa en formato JSON con el resultado de la operación.
     */
    public function cancel() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $json = file_get_contents('php://input');
        $data = json_decode($json, true) ?: [];
        $id = $data['venta_id'] ?? $data['id'] ?? ($_POST['venta_id'] ?? $_POST['id'] ?? null);

        $error = Validator::firstError([
            Validator::integer($id, 'ID de venta'),
            Validator::greaterThan($id, 0, 'ID de venta'),
        ]);
        if ($error) {
            echo json_encode(['success' => false, 'message' => $error]);
            return;
        }

        if ($this->ventaModel->cancelarVenta($id)) {
            $this->audit->log('Ventas', 'Cancelación de venta', "Venta #{$id} cancelada e inventario revertido.");
            echo json_encode(['success' => true, 'message' => 'Venta cancelada e inventario revertido correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al cancelar la venta.']);
        }
    }

    /**
     * Obtiene el ranking de productos más vendidos.
     *
     * @return void Salida directa en formato JSON con los productos más vendidos.
     */
    public function getTopProducts() {
        header('Content-Type: application/json');
        $data = $this->ventaModel->getTopProducts();
        echo json_encode(['success' => true, 'data' => $data]);
    }

    /**
     * Obtiene los datos de ingresos diarios de los últimos 7 días para el gráfico
     * de ingresos del dashboard.
     *
     * @return void Salida directa en formato JSON con las series temporales de ingresos.
     */
    public function getRevenueChart() {
        header('Content-Type: application/json');
        $data = $this->ventaModel->getRevenueChartData();
        echo json_encode(['success' => true, 'data' => $data]);
    }

    /**
     * Obtiene los detalles completos de una venta por su identificador,
     * incluyendo los ítems vendidos y los pagos asociados.
     *
     * Espera el parámetro "id" en la query string de la petición GET.
     *
     * @return void Salida directa en formato JSON con la venta y sus detalles.
     */
    public function getDetalles() {
        header('Content-Type: application/json');
        $id = $_GET['id'] ?? null;
        $error = Validator::firstError([
            Validator::integer($id, 'ID de venta'),
            Validator::greaterThan($id, 0, 'ID de venta'),
        ]);
        if ($error) {
            echo json_encode(['success' => false, 'message' => $error]);
            return;
        }
        $data = $this->ventaModel->getVentaConDetalles($id);
        echo json_encode(['success' => true, 'data' => $data]);
    }
}
?>
