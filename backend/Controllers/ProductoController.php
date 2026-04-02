<?php
require_once __DIR__ . '/../Models/ProductoModel.php';

class ProductoController {
    private $productoModel;

    public function __construct() {
        $this->productoModel = new ProductoModel();
    }

    public function getAll() {
        $productos = $this->productoModel->readAll();
        echo json_encode(['success' => true, 'data' => $productos]);
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data) {
            echo json_encode(['success' => false, 'message' => 'Faltan datos de producto.']);
            return;
        }

        $nombre = $data['nombre'] ?? '';
        $descripcion = $data['descripcion'] ?? '';
        $precio = $data['precio_venta'] ?? 0;
        $categoria = $data['categoria'] ?? '';
        $cantidad = $data['cantidad'] ?? 0;
        $ingredientes = $data['ingredientes'] ?? [];

        $stock_minimo = $data['stock_minimo'] ?? 0;
 
        if (empty($nombre) || $precio <= 0) {
            echo json_encode(['success' => false, 'message' => 'Nombre y precio válido obligatorios.']);
            return;
        }

        require_once __DIR__ . '/../Helpers/UnitConverter.php';
        require_once __DIR__ . '/../Models/InsumoModel.php';
        $insumoModel = new InsumoModel();

        // Convertir y validar los ingredientes (receta)
        $ingredientes_procesados = [];
        try {
            foreach ($ingredientes as $ing) {
                if (empty($ing['insumo_id']) || empty($ing['cantidad_requerida'])) continue;
                
                $unidad_usada = $ing['unidad_usada'] ?? 'Unidades';
                
                // Obtener unidad base del insumo
                $infoInsumo = $insumoModel->getById($ing['insumo_id']);
                if (!$infoInsumo) {
                    throw new Exception("El insumo seleccionado no existe.");
                }
                
                $unidad_base = $infoInsumo['unidad_medida'];
                
                // Convertir
                $cantidad_convertida = UnitConverter::convert($ing['cantidad_requerida'], $unidad_usada, $unidad_base);
                
                $ingredientes_procesados[] = [
                    'insumo_id' => $ing['insumo_id'],
                    'cantidad_requerida' => $cantidad_convertida,
                    // guardamos la original si se quiere mostrar después, pero BD usa la calculada.
                ];
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error en conversión de unidades: ' . $e->getMessage()]);
            return;
        }

        if ($this->productoModel->create($nombre, $descripcion, $precio, $categoria, $cantidad, $ingredientes_procesados, $stock_minimo)) {
            echo json_encode(['success' => true, 'message' => 'Producto creado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear producto. Verifique que haya stock suficiente de insumos.']);
        }
    }

    public function getByCategoria() {
        $categoria = $_GET['categoria'] ?? '';
        if (empty($categoria)) {
            $this->getAll();
            return;
        }
        $productos = $this->productoModel->getByCategoria($categoria);
        echo json_encode(['success' => true, 'data' => $productos]);
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $id = $_POST['id'] ?? 0;
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado.']);
            return;
        }
        if ($this->productoModel->delete($id)) {
            echo json_encode(['success' => true, 'message' => 'Producto eliminado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar el producto.']);
        }
    }

    /**
     * POST JSON { "producto_id": N, "cantidad": N }
     * Llama a ProductoModel::producir() y responde con JSON.
     * En caso de insumos insuficientes devuelve el detalle de cada faltante.
     */
    public function producir() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $data        = json_decode(file_get_contents('php://input'), true);
        $producto_id = $data['producto_id'] ?? 0;
        $cantidad    = $data['cantidad']    ?? 0;

        // ── Validación básica ──────────────────────────────────────────────
        if (empty($producto_id) || $cantidad <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Debes indicar un producto y una cantidad mayor a cero.'
            ]);
            return;
        }

        // ── Delegar al modelo ──────────────────────────────────────────────
        $resultado = $this->productoModel->producir((int)$producto_id, (float)$cantidad);

        if ($resultado === true) {
            // Éxito: todo se procesó correctamente
            echo json_encode([
                'success' => true,
                'message' => "Producción registrada correctamente. Se agregaron {$cantidad} unidades al stock."
            ]);
        } elseif (isset($resultado['sin_receta'])) {
            // El producto no tiene receta asignada
            echo json_encode([
                'success' => false,
                'message' => $resultado['mensaje']
            ]);
        } elseif (isset($resultado['error'])) {
            // Error inesperado de base de datos
            echo json_encode([
                'success' => false,
                'message' => 'Error interno: ' . $resultado['error']
            ]);
        } else {
            // Array de insumos con stock insuficiente
            $detalle = array_map(function($f) {
                return "{$f['nombre']} (necesita {$f['necesita']} {$f['unidad']}, disponible: {$f['disponible']})";
            }, $resultado);

            echo json_encode([
                'success'   => false,
                'message'   => 'Stock insuficiente en los siguientes insumos:',
                'faltantes' => $detalle
            ]);
        }
    }
}
?>
