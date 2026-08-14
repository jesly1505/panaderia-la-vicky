<?php
namespace App\Utils;

use App\Models\ProductoModel;
use App\Models\InsumoModel;

class InventoryLogic {
    private $productoModel;
    private $insumoModel;

    public function __construct(ProductoModel $productoModel, InsumoModel $insumoModel) {
        $this->productoModel = $productoModel;
        $this->insumoModel = $insumoModel;
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
        // 1. Descontar stock del producto
        $producto = $this->productoModel->getById($producto_id);
        if ($producto) {
            $nuevoStockProducto = $producto['stock_actual'] - $cantidad;
            $this->productoModel->updateStock($producto_id, $nuevoStockProducto);

            // 2. Obtener receta y descontar insumos
            $receta = $this->productoModel->getReceta($producto_id);
            foreach ($receta as $item) {
                $insumo_id = $item['insumo_id'];
                $cantidad_requerida_total = $item['cantidad_requerida'] * $cantidad;
                
                // updateStock resta si el valor es negativo
                $this->insumoModel->updateStock($insumo_id, -$cantidad_requerida_total);
            }
        }
    }

    /**
     * Revierte el stock de un producto e insumos (suma en lugar de restar).
     */
    public function revertirStockYReceta($producto_id, $cantidad) {
        $producto = $this->productoModel->getById($producto_id);
        if ($producto) {
            $nuevoStockProducto = $producto['stock_actual'] + $cantidad;
            $this->productoModel->updateStock($producto_id, $nuevoStockProducto);

            $receta = $this->productoModel->getReceta($producto_id);
            foreach ($receta as $item) {
                $insumo_id = $item['insumo_id'];
                $cantidad_requerida_total = $item['cantidad_requerida'] * $cantidad;
                
                // updateStock suma si el valor es positivo
                $this->insumoModel->updateStock($insumo_id, $cantidad_requerida_total);
            }
        }
    }
}

