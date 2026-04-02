<?php
require_once __DIR__ . '/../Models/PedidoModel.php';

class PedidoController {
    private $pedidoModel;

    public function __construct() {
        $this->pedidoModel = new PedidoModel();
    }

    public function getAll() {
        $pedidos = $this->pedidoModel->readAll();
        echo json_encode(['success' => true, 'data' => $pedidos]);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data || empty($data['detalles'])) {
            echo json_encode(['success' => false, 'message' => 'Faltan datos del pedido o carrito está vacío.']);
            return;
        }
        
        session_start();
        $usuario_id = $_SESSION['usuario_id'] ?? 1; // Fallback admin 
        $cliente_id = $data['cliente_id'] ?? null;
        $total = $data['total'] ?? 0;
        $detalles = $data['detalles'];
        $fecha_entrega = $data['fecha_entrega'] ?? null;
        $hora_entrega = $data['hora_entrega'] ?? null;

        if ($this->pedidoModel->create($cliente_id, $usuario_id, $total, $detalles, $fecha_entrega, $hora_entrega)) {
            echo json_encode(['success' => true, 'message' => 'Pedido registrado exitosamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al registrar pedido.']);
        }
    }

    public function updateEstado() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        if (!$data || empty($data['id']) || empty($data['estado'])) {
            echo json_encode(['success' => false, 'message' => 'Faltan datos.']);
            return;
        }
        $hora_real = $data['hora_entrega_real'] ?? null;
        if ($this->pedidoModel->updateEstado($data['id'], $data['estado'], $hora_real)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar estado.']);
        }
    }

    public function getDetalles() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID de pedido no proporcionado.']);
            return;
        }
        $detalles = $this->pedidoModel->getDetalles($id);
        echo json_encode(['success' => true, 'data' => $detalles]);
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (!$data || empty($data['id'])) {
            // Support FormData as well
            $id = $_POST['id'] ?? ($data['id'] ?? 0);
        } else {
            $id = $data['id'];
        }
        
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID de pedido no proporcionado.']);
            return;
        }

        if ($this->pedidoModel->delete($id)) {
            echo json_encode(['success' => true, 'message' => 'Pedido eliminado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar pedido.']);
        }
    }
}
?>
