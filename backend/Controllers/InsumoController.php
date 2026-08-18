<?php
namespace App\Controllers;

use App\Core\AuditService;
use App\Core\Interfaces\InsumoRepositoryInterface;
use App\Core\Money;
use App\Core\Validator;

class InsumoController {
    private $insumoModel;
    private $audit;

    public function __construct(InsumoRepositoryInterface $insumoModel, AuditService $audit) {
        $this->insumoModel = $insumoModel;
        $this->audit = $audit;
    }

    public function getAll() {
        $insumos = $this->insumoModel->readAll();
        echo json_encode(['success' => true, 'data' => $insumos]);
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $proveedor_id = !empty(Validator::input('proveedor_id')) ? Validator::input('proveedor_id') : null;
        $nombre = trim(Validator::input('nombre'));
        $unidad = trim(Validator::input('unidad_medida'));
        $inicial = !empty(Validator::input('stock_inicial')) ? Validator::input('stock_inicial') : 0;
        $minimo = !empty(Validator::input('stock_minimo')) ? Validator::input('stock_minimo') : 0;
        $precio = !empty(Validator::input('precio_costo')) ? Validator::input('precio_costo') : 0;

        $error = Validator::firstError([
            Validator::required($nombre, 'Nombre'),
            Validator::length($nombre, 100, 'Nombre'),
            Validator::required($unidad, 'Unidad de medida'),
            Validator::length($unidad, 30, 'Unidad de medida'),
            Validator::numeric($inicial, 'Stock inicial'),
            Validator::min($inicial, 0, 'Stock inicial'),
            Validator::numeric($minimo, 'Stock mínimo'),
            Validator::min($minimo, 0, 'Stock mínimo'),
            Validator::numeric($precio, 'Precio de costo'),
            Validator::greaterThan($precio, 0, 'Precio de costo'),
        ]);

        if ($error) {
            echo json_encode(['success' => false, 'message' => $error]);
            return;
        }

        $inicial = Money::round($inicial);
        $minimo  = Money::round($minimo);
        $precio  = Money::round($precio);

        try {
            if ($this->insumoModel->create($proveedor_id, $nombre, $unidad, $inicial, $minimo, $precio)) {
                $this->audit->log('Inventario', 'Alta de insumo', "Insumo creado: {$nombre} ({$unidad})");
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
        
        $id = Validator::input('insumo_id', 0);
        $cantidad = Validator::input('cantidad', 0); // puede ser negativo

        $error = Validator::firstError([
            Validator::integer($id, 'ID'),
            Validator::greaterThan($id, 0, 'ID'),
            Validator::numeric($cantidad, 'Cantidad'),
        ]);
        if ($error) {
            echo json_encode(['success' => false, 'message' => $error]);
            return;
        }

        $cantidad = Money::round($cantidad);

        if ($this->insumoModel->updateStock($id, $cantidad)) {
            $this->audit->log('Inventario', 'Ajuste de stock', "Ajuste de stock en insumo ID {$id} (+/- {$cantidad})");
            echo json_encode(['success' => true, 'message' => 'Stock actualizado.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error actualizando stock.']);
        }
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $id = Validator::input('id', 0);
        
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID de insumo no proporcionado.']);
            return;
        }

        if ($this->insumoModel->delete($id)) {
            $this->audit->log('Inventario', 'Baja de insumo', "Insumo ID {$id} eliminado.");
            echo json_encode(['success' => true, 'message' => 'Insumo eliminado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar insumo. Puede estar en uso.']);
        }
    }

    public function toggleVisibility() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $id = Validator::input('id', 0);
        $visible = Validator::input('visible', 1);
        
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID de insumo no proporcionado.']);
            return;
        }

        if ($this->insumoModel->setVisibility($id, $visible)) {
            $this->audit->log('Inventario', 'Cambio de visibilidad', "Insumo ID {$id} visibilidad={$visible}");
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
        
        $insumo_id = Validator::input('insumo_id', 0);
        $proveedor_id = Validator::input('proveedor_id', 0);
        $cantidad = Validator::input('cantidad', 0);
        $precio = Validator::input('costo_unitario', 0);

        $error = Validator::firstError([
            Validator::integer($insumo_id, 'Insumo'),
            Validator::greaterThan($insumo_id, 0, 'Insumo'),
            Validator::integer($proveedor_id, 'Proveedor'),
            Validator::greaterThan($proveedor_id, 0, 'Proveedor'),
            Validator::numeric($cantidad, 'Cantidad'),
            Validator::greaterThan($cantidad, 0, 'Cantidad'),
            Validator::numeric($precio, 'Precio de compra'),
            Validator::greaterThan($precio, 0, 'Precio de compra'),
        ]);
        if ($error) {
            echo json_encode(['success' => false, 'message' => $error]);
            return;
        }

        $cantidad = Money::round($cantidad);
        $precio   = Money::round($precio);

        if ($this->insumoModel->registrarCompra($insumo_id, $proveedor_id, $cantidad, $precio)) {
            $this->audit->log('Inventario', 'Compra de insumo', "Compra de {$cantidad} unidades del insumo ID {$insumo_id}");
            echo json_encode(['success' => true, 'message' => 'Compra registrada y stock actualizado.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al registrar la compra.']);
        }
    }
}
?>
