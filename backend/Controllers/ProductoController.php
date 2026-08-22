<?php
namespace App\Controllers;

use App\Core\AuditService;
use App\Core\Interfaces\InsumoRepositoryInterface;
use App\Core\Money;
use App\Core\Validator;
use App\Helpers\UnitConverter;
use App\Models\ProductoModel;
use App\Utils\Logger;

class ProductoController
{
    private $productoModel;
    private $insumoModel;
    private $audit;

    public function __construct(ProductoModel $productoModel, InsumoRepositoryInterface $insumoModel, AuditService $audit)
    {
        $this->productoModel = $productoModel;
        $this->insumoModel = $insumoModel;
        $this->audit = $audit;
    }

    public function getAll()
    {
        header('Content-Type: application/json');
        $productos = $this->productoModel->readAll();
        echo json_encode(['success' => true, 'data' => $productos]);
    }

    public function add()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST')
            return;

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

        $precio = Money::round($precio);
        $cantidad = Money::round($cantidad);
        $stock_minimo = Money::round($stock_minimo);

        $insumoModel = $this->insumoModel;

        // Convertir y validar los ingredientes (receta)
        $ingredientes_procesados = [];
        try {
            foreach ($ingredientes as $ing) {
                if (empty($ing['insumo_id']) || empty($ing['cantidad_requerida']))
                    continue;

                $unidad_usada = $ing['unidad_usada'] ?? 'Unidades';

                // Obtener unidad base del insumo
                $infoInsumo = $insumoModel->getById($ing['insumo_id']);
                if (!$infoInsumo) {
                    throw new \Exception("El insumo seleccionado no existe.");
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
        } catch (\Exception $e) {
            Logger::error($e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al procesar la receta.']);
            return;
        }

        if ($this->productoModel->create($nombre, $descripcion, $precio, $categoria, $cantidad, $ingredientes_procesados, $stock_minimo)) {
            $this->audit->log('Productos', 'Alta de producto', "Producto creado: {$nombre}");
            echo json_encode(['success' => true, 'message' => 'Producto creado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear producto. Verifique que haya stock suficiente de insumos.']);
        }
    }

    public function update()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST')
            return;

        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            echo json_encode(['success' => false, 'message' => 'Faltan datos de producto.']);
            return;
        }

        $id = $data['id'] ?? 0;
        $nombre = $data['nombre'] ?? '';
        $descripcion = $data['descripcion'] ?? '';
        $precio = $data['precio_venta'] ?? 0;
        $categoria = $data['categoria'] ?? '';
        $stock_minimo = $data['stock_minimo'] ?? 0;

        $error = Validator::firstError([
            Validator::integer($id, 'ID'),
            Validator::greaterThan($id, 0, 'ID'),
            Validator::required($nombre, 'Nombre'),
            Validator::length($nombre, 100, 'Nombre'),
            Validator::required($categoria, 'Categoría'),
            Validator::length($categoria, 50, 'Categoría'),
            Validator::numeric($precio, 'Precio de venta'),
            Validator::greaterThan($precio, 0, 'Precio de venta'),
            Validator::numeric($stock_minimo, 'Stock mínimo'),
            Validator::min($stock_minimo, 0, 'Stock mínimo'),
        ]);
        if ($error) {
            echo json_encode(['success' => false, 'message' => $error]);
            return;
        }

        $precio = Money::round($precio);
        $stock_minimo = Money::round($stock_minimo);

        if ($this->productoModel->update($id, $nombre, $descripcion, $precio, $categoria, $stock_minimo)) {
            $this->audit->log('Productos', 'Edición de producto', "Producto ID {$id} actualizado: {$nombre}");
            echo json_encode(['success' => true, 'message' => 'Producto actualizado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el producto.']);
        }
    }

    public function getByCategoria()
    {
        header('Content-Type: application/json');
        $categoria = $_GET['categoria'] ?? '';
        if (empty($categoria)) {
            $this->getAll();
            return;
        }
        $productos = $this->productoModel->getByCategoria($categoria);
        echo json_encode(['success' => true, 'data' => $productos]);
    }

    public function delete()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST')
            return;
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
     * Llama a ProductoModel::producir() y responde con JSON
     */
    public function producir()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST')
            return;

        $producto_id = $data['producto_id'] ?? 0;
        $cantidad = $data['cantidad'] ?? 0;

        $error = Validator::firstError([
            Validator::integer($producto_id, 'ID de producto'),
            Validator::numeric($cantidad, 'Cantidad'),
            Validator::greaterThan($cantidad, 0, 'Cantidad'),
        ]);

        if ($error) {
            // Normalizar mensaje de validación de cantidad
            if (strpos($error, 'El campo Cantidad debe ser mayor que 0.') !== false) {
                $error = 'El campo debe de ser mayor que 0.';
            }
            echo json_encode(['success' => false, 'message' => $error]);
            return;
        }

        if (!$this->productoModel->getById($producto_id)) {
            echo json_encode(['success' => false, 'message' => 'El producto especificado no existe.']);
            return;
        }

        $resultado = $this->productoModel->producir($producto_id, $cantidad);
        if ($resultado === true) {
            echo json_encode(['success' => true, 'message' => 'Producción completada con éxito.']);
        } elseif (isset($resultado['sin_receta'])) {
            $msg = $resultado['mensaje'] ?? 'Este producto no tiene receta asignada.';
            echo json_encode(['success' => false, 'message' => $msg]);
        } elseif (isset($resultado['error'])) {
            echo json_encode(['success' => false, 'message' => 'Ha ocurrido un error interno. Por favor, contacte al administrador.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error en la producción: insumos insuficientes.', 'faltantes' => $resultado]);
        }
    }
}
?>