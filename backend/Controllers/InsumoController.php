<?php
require_once __DIR__ . '/../Core/Interfaces/InsumoRepositoryInterface.php';

use Core\Interfaces\InsumoRepositoryInterface;

class InsumoController {
    private $insumoModel;

    public function __construct(InsumoRepositoryInterface $insumoModel) {
        $this->insumoModel = $insumoModel;
    }

    public function getAll() {
        $insumos = $this->insumoModel->readAll();
        echo json_encode(['success' => true, 'data' => $insumos]);
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $proveedor_id = !empty($_POST['proveedor_id']) ? $_POST['proveedor_id'] : null;
        $nombre = trim($_POST['nombre'] ?? '');
        $unidad = trim($_POST['unidad_medida'] ?? '');
        $inicial = !empty($_POST['stock_inicial']) ? $_POST['stock_inicial'] : 0;
        $minimo = !empty($_POST['stock_minimo']) ? $_POST['stock_minimo'] : 0;
        $precio = !empty($_POST['precio_costo']) ? $_POST['precio_costo'] : 0;

        if (empty($nombre) || empty($unidad) || $precio <= 0) {
            echo json_encode(['success' => false, 'message' => 'El nombre, la unidad y un precio válido son obligatorios.']);
            return;
        }

        try {
            if ($this->insumoModel->create($proveedor_id, $nombre, $unidad, $inicial, $minimo, $precio)) {
                echo json_encode(['success' => true, 'message' => 'Insumo creado correctamente.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo guardar el insumo en la base de datos. Verifique si el nombre ya existe.']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
        }
    }
    
    public function adjustStock() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $id = $_POST['id'] ?? 0;
        $cantidad = $_POST['cantidad'] ?? 0; // puede ser negativo
        
        if ($this->insumoModel->updateStock($id, $cantidad)) {
            echo json_encode(['success' => true, 'message' => 'Stock actualizado.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error actualizando stock.']);
        }
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $id = $_POST['id'] ?? 0;
        
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID de insumo no proporcionado.']);
            return;
        }

        if ($this->insumoModel->delete($id)) {
            echo json_encode(['success' => true, 'message' => 'Insumo eliminado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar insumo. Puede estar en uso.']);
        }
    }

    public function toggleVisibility() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $id = $_POST['id'] ?? 0;
        $visible = $_POST['visible'] ?? 1;
        
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID de insumo no proporcionado.']);
            return;
        }

        if ($this->insumoModel->setVisibility($id, $visible)) {
            echo json_encode(['success' => true, 'message' => 'Visibilidad actualizada.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar visibilidad.']);
        }
    }

    public function getLowStock() {
        $insumos = $this->insumoModel->getLowStock();
        echo json_encode(['success' => true, 'data' => $insumos]);
    }

    public function registrarCompra() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $insumo_id = $_POST['insumo_id'] ?? 0;
        $proveedor_id = $_POST['proveedor_id'] ?? 0;
        $cantidad = $_POST['cantidad'] ?? 0;
        $precio = $_POST['precio_compra'] ?? 0;

        if (empty($insumo_id) || empty($proveedor_id) || $cantidad <= 0 || $precio <= 0) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios y deben ser mayores a cero.']);
            return;
        }

        if ($this->insumoModel->registrarCompra($insumo_id, $proveedor_id, $cantidad, $precio)) {
            echo json_encode(['success' => true, 'message' => 'Compra registrada y stock actualizado.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al registrar la compra.']);
        }
    }
}
?>
