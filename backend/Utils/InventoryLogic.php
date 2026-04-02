<?php
require_once __DIR__ . '/../Models/ProductoModel.php';
require_once __DIR__ . '/../Models/InsumoModel.php';

class InventoryLogic {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function descontarVarios($detalles) {
        foreach ($detalles as $detalle) {
            $this->descontarStockYReceta($detalle['producto_id'], $detalle['cantidad']);
        }
    }

    public function revertirVarios($detalles) {
        foreach ($detalles as $detalle) {
            $this->revertirStockYReceta($detalle['producto_id'], $detalle['cantidad']);
        }
    }

    /**
     * Descuenta el stock de un producto y sus insumos asociados.
     */
    public function descontarStockYReceta($producto_id, $cantidad) {
        $productoModel = new ProductoModel($this->db);
        $insumoModel = new InsumoModel($this->db);

        // 1. Descontar stock del producto
        $producto = $productoModel->getById($producto_id);
        if ($producto) {
            $nuevoStockProducto = $producto['stock_actual'] - $cantidad;
            $productoModel->updateStock($producto_id, $nuevoStockProducto);

            // 2. Obtener receta y descontar insumos
            $receta = $productoModel->getReceta($producto_id);
            foreach ($receta as $item) {
                $insumo_id = $item['insumo_id'];
                $cantidad_requerida_total = $item['cantidad_requerida'] * $cantidad;
                
                // updateStock resta si el valor es negativo
                $insumoModel->updateStock($insumo_id, -$cantidad_requerida_total);
            }
        }
    }

    /**
     * Revierte el stock de un producto e insumos (suma en lugar de restar).
     */
    public function revertirStockYReceta($producto_id, $cantidad) {
        $productoModel = new ProductoModel($this->db);
        $insumoModel = new InsumoModel($this->db);

        $producto = $productoModel->getById($producto_id);
        if ($producto) {
            $nuevoStockProducto = $producto['stock_actual'] + $cantidad;
            $productoModel->updateStock($producto_id, $nuevoStockProducto);

            $receta = $productoModel->getReceta($producto_id);
            foreach ($receta as $item) {
                $insumo_id = $item['insumo_id'];
                $cantidad_requerida_total = $item['cantidad_requerida'] * $cantidad;
                
                // updateStock suma si el valor es positivo
                $insumoModel->updateStock($insumo_id, $cantidad_requerida_total);
            }
        }
    }
}
?>
