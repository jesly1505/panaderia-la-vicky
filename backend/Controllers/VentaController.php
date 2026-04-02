<?php
require_once __DIR__ . '/../Models/VentaModel.php';

class VentaController {
    private $ventaModel;

    public function __construct() {
        $this->ventaModel = new VentaModel();
    }

    public function getAll() {
        $ventas = $this->ventaModel->readAll();
        echo json_encode(['success' => true, 'data' => $ventas]);
    }

    public function createDirecta() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data || empty($data['detalles'])) {
            echo json_encode(['success' => false, 'message' => 'Faltan datos de la venta o carrito vacío.']);
            return;
        }

        // El modelo ahora espera el array completo $data con subtotales, pagos, etc.
        $result = $this->ventaModel->createDirecta($data);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Venta registrada exitosamente.', 'venta_id' => $result]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al registrar venta.']);
        }
    }

    public function cancel() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $id = $data['id'] ?? null;

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID de venta requerido para cancelar.']);
            return;
        }

        if ($this->ventaModel->cancelarVenta($id)) {
            echo json_encode(['success' => true, 'message' => 'Venta cancelada e inventario revertido correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al cancelar la venta.']);
        }
    }

    public function getTopProducts() {
        $data = $this->ventaModel->getTopProducts();
        echo json_encode(['success' => true, 'data' => $data]);
    }

    public function getRevenueChart() {
        $data = $this->ventaModel->getRevenueChartData();
        echo json_encode(['success' => true, 'data' => $data]);
    }

    public function getDetalles() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID de venta requerido.']);
            return;
        }
        $data = $this->ventaModel->getVentaConDetalles($id);
        echo json_encode(['success' => true, 'data' => $data]);
    }
}
?>
