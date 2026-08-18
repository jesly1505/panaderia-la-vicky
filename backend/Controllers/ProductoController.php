<?php
namespace App\Controllers;

use App\Core\AuditService;
use App\Core\Interfaces\InsumoRepositoryInterface;
use App\Core\Money;
use App\Core\Validator;
use App\Helpers\UnitConverter;
use App\Models\ProductoModel;

class ProductoController {
    private $productoModel;
    private $insumoModel;
    private $audit;

    public function __construct(ProductoModel $productoModel, InsumoRepositoryInterface $insumoModel, AuditService $audit) {
        $this->productoModel = $productoModel;
        $this->insumoModel = $insumoModel;
        $this->audit = $audit;
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

        $error = Validator::firstError([
            Validator::required($nombre, 'Nombre'),
            Validator::length($nombre, 100, 'Nombre'),
            Validator::required($categoria, 'Categoría'),
            Validator::length($categoria, 50, 'Categoría'),
            Validator::numeric($precio, 'Precio de venta'),
            Validator::greaterThan($precio, 0, 'Precio de venta'),
            Validator::numeric($cantidad, 'Cantidad'),
            Validator::min($cantidad, 0, 'Cantidad'),
            Validator::numeric($stock_minimo, 'Stock mínimo'),
            Validator::min($stock_minimo, 0, 'Stock mínimo'),
        ]);
        if ($error) {
            echo json_encode(['success' => false, 'message' => $error]);
            return;
        }

        $precio      = Money::round($precio);
        $cantidad    = Money::round($cantidad);
        $stock_minimo = Money::round($stock_minimo);

        $insumoModel = $this->insumoModel;

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
            $this->audit->log('Productos', 'Alta de producto', "Producto creado: {$nombre}");
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
        $id = Validator::input('id', 0);
        $error = Validator::firstError([
            Validator::integer($id, 'ID'),
            Validator::greaterThan($id, 0, 'ID'),
        ]);
        if ($error) {
            echo json_encode(['success' => false, 'message' => $error]);
            return;
        }
        $result = $this->productoModel->delete($id);
        if ($result === true) {
            $this->audit->log('Productos', 'Baja de producto', "Producto ID {$id} eliminado.");
            echo json_encode(['success' => true, 'message' => 'Producto eliminado correctamente.']);
        } elseif (is_array($result) && isset($result['en_uso'])) {
            echo json_encode(['success' => false, 'message' => 'No se puede eliminar el producto: aparece en ventas o pedidos registrados.']);
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
        $error = Validator::firstError([
            Validator::integer($producto_id, 'Producto'),
            Validator::greaterThan($producto_id, 0, 'Producto'),
            Validator::numeric($cantidad, 'Cantidad'),
            Validator::greaterThan($cantidad, 0, 'Cantidad'),
        ]);
        if ($error) {
            echo json_encode([
                'success' => false,
                'message' => $error
            ]);
            return;
        }

        // ── Delegar al modelo ──────────────────────────────────────────────
        $resultado = $this->productoModel->producir((int)$producto_id, (float)$cantidad);

        if ($resultado === true) {
            // Éxito: todo se procesó correctamente
            $this->audit->log('Producción', 'Producción de lote', "Producto ID {$producto_id}: se produjeron {$cantidad} unidades.");
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
