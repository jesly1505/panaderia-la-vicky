<?php
namespace Core\Interfaces;

interface InsumoRepositoryInterface {
    public function readAll();
    public function create($proveedor_id, $nombre, $unidad, $inicial, $minimo, $precio);
    public function updateStock($id, $cantidad);
    public function delete($id);
    public function setVisibility($id, $visible);
    public function getLowStock();
    public function registrarCompra($insumo_id, $proveedor_id, $cantidad, $precio);
}
