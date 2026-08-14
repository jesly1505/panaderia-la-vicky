<?php
namespace App\Controllers;

use App\Core\AuditService;
use App\Core\Validator;
use App\Models\PedidoModel;

class PedidoController {
    private $pedidoModel;
    private $audit;

    /** Estados permitidos para los pedidos. */
    private const ESTADOS = ['pendiente', 'en_proceso', 'entregado', 'cancelado'];

    public function __construct(PedidoModel $pedidoModel, AuditService $audit) {
        $this->pedidoModel = $pedidoModel;
        $this->audit = $audit;
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

        $errors = [
            Validator::integer($data['cliente_id'] ?? 0, 'Cliente'),
            Validator::greaterThan($data['cliente_id'] ?? 0, 0, 'Cliente'),
            Validator::numeric($data['total'] ?? 0, 'Total'),
            Validator::min($data['total'] ?? 0, 0, 'Total'),
            Validator::date($data['fecha_entrega'] ?? null, 'Fecha de entrega'),
        ];
        foreach ($data['detalles'] as $i => $d) {
            $errors[] = Validator::integer($d['id'] ?? 0, "Producto #" . ($i + 1));
            $errors[] = Validator::greaterThan($d['id'] ?? 0, 0, "Producto #" . ($i + 1));
            $errors[] = Validator::numeric($d['cantidad'] ?? 0, "Cantidad del producto #" . ($i + 1));
            $errors[] = Validator::greaterThan($d['cantidad'] ?? 0, 0, "Cantidad del producto #" . ($i + 1));
        }
        $error = Validator::firstError($errors);
        if ($error) {
            echo json_encode(['success' => false, 'message' => $error]);
            return;
        }

        // La sesión ya fue iniciada por el Router; nunca asignar un usuario por defecto.
        $usuario_id = $_SESSION['usuario_id'] ?? null;
        $cliente_id = $data['cliente_id'] ?? null;
        $total = $data['total'] ?? 0;
        $detalles = $data['detalles'];
        $fecha_entrega = $data['fecha_entrega'] ?? null;
        $hora_entrega = $data['hora_entrega'] ?? null;

        if ($this->pedidoModel->create($cliente_id, $usuario_id, $total, $detalles, $fecha_entrega, $hora_entrega)) {
            $this->audit->log('Pedidos', 'Registro de pedido', "Pedido registrado por \$" . number_format($total, 2));
            echo json_encode(['success' => true, 'message' => 'Pedido registrado exitosamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al registrar pedido.']);
        }
    }

    public function updateEstado() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        $error = Validator::firstError([
            Validator::integer($data['id'] ?? 0, 'ID de pedido'),
            Validator::greaterThan($data['id'] ?? 0, 0, 'ID de pedido'),
            Validator::inList($data['estado'] ?? '', self::ESTADOS, 'Estado'),
        ]);
        if ($error) {
            echo json_encode(['success' => false, 'message' => $error]);
            return;
        }
        $hora_real = $data['hora_entrega_real'] ?? null;
        if ($this->pedidoModel->updateEstado($data['id'], $data['estado'], $hora_real, $_SESSION['usuario_id'] ?? null)) {
            $this->audit->log('Pedidos', 'Cambio de estado', "Pedido ID {$data['id']} -> estado '{$data['estado']}'");
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar estado.']);
        }
    }

    public function getDetalles() {
        $id = $_GET['id'] ?? null;
        $error = Validator::firstError([
            Validator::integer($id, 'ID de pedido'),
            Validator::greaterThan($id, 0, 'ID de pedido'),
        ]);
        if ($error) {
            echo json_encode(['success' => false, 'message' => $error]);
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

        $error = Validator::firstError([
            Validator::integer($id, 'ID de pedido'),
            Validator::greaterThan($id, 0, 'ID de pedido'),
        ]);
        if ($error) {
            echo json_encode(['success' => false, 'message' => $error]);
            return;
        }

        if ($this->pedidoModel->delete($id)) {
            $this->audit->log('Pedidos', 'Baja de pedido', "Pedido ID {$id} eliminado.");
            echo json_encode(['success' => true, 'message' => 'Pedido eliminado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar pedido.']);
        }
    }
}
?>
