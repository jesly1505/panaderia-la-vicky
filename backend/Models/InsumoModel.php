<?php
namespace App\Models;

use PDO;
use App\Core\Interfaces\InsumoRepositoryInterface;

class InsumoModel implements InsumoRepositoryInterface {
    /** @var PDO Conexión a la base de datos. */
    private $conn;
    /** @var string Nombre de la tabla principal. */
    private $table_name = "insumos";

    /**
     * @param PDO $db Conexión PDO activa.
     */
    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    /**
     * Crea un nuevo insumo en el inventario.
     *
     * @param  int|float $proveedor_id   ID del proveedor.
     * @param  string    $nombre         Nombre del insumo.
     * @param  string    $unidad_medida  Unidad de medida (kg, lt, ud).
     * @param  float     $stock_inicial  Stock inicial.
     * @param  float     $stock_minimo   Stock mínimo para alertas.
     * @param  float     $precio_costo   Costo por unidad.
     * @return bool
     */
    public function create($proveedor_id, $nombre, $unidad_medida, $stock_inicial, $stock_minimo, $precio_costo) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (proveedor_id, nombre, unidad_medida, stock_actual, stock_minimo, precio_costo, eliminado, visible) 
                  VALUES (:proveedor_id, :nombre, :unidad_medida, :stock_inicial, :stock_minimo, :precio_costo, false, 1)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":proveedor_id", $proveedor_id);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":unidad_medida", $unidad_medida);
        $stmt->bindParam(":stock_inicial", $stock_inicial);
        $stmt->bindParam(":stock_minimo", $stock_minimo);
        $stmt->bindParam(":precio_costo", $precio_costo);
        
        return $stmt->execute();
    }

    /**
     * Ajusta el stock actual de un insumo (suma/resta).
     *
     * @param  int|float $id       ID del insumo.
     * @param  float     $cantidad Cantidad a sumar (negativo para restar).
     * @return bool
     */
    public function updateStock($id, $cantidad) {
        $query = "UPDATE " . $this->table_name . " SET stock_actual = stock_actual + :cantidad WHERE id = :id AND eliminado = false";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cantidad", $cantidad);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    /**
     * Read all insumos with optional pagination and search.
     * If $limit is null, returns all rows (legacy behavior).
     */
    public function readAll($limit = null, $offset = null, $onlyVisible = true, $search = '') {
        $where = " i.eliminado = false ";
        if ($onlyVisible) $where .= " AND i.visible = 1 ";
        if (!empty($search)) {
            $where .= " AND (i.nombre LIKE :search OR p.nombre LIKE :search2) ";
        }
        $query = "SELECT i.*, p.nombre as proveedor_nombre FROM " . $this->table_name . " i 
                  LEFT JOIN proveedores p ON i.proveedor_id = p.id
                  WHERE $where
                  ORDER BY i.nombre ASC";
        if (is_int($limit) && $limit > 0) {
            $query .= " LIMIT :limit";
            if (is_int($offset) && $offset >= 0) {
                $query .= " OFFSET :offset";
            }
        }
        $stmt = $this->conn->prepare($query);
        if (!empty($search)) {
            $term = "%" . $search . "%";
            $stmt->bindValue(':search', $term);
            $stmt->bindValue(':search2', $term);
        }
        if (is_int($limit) && $limit > 0) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            if (is_int($offset) && $offset >= 0) {
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            }
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Return total count of insumos (optionally filtered by visibility and search).
     */
    public function countAll($onlyVisible = true, $search = '') {
        $where = " i.eliminado = false ";
        if ($onlyVisible) $where .= " AND i.visible = 1 ";
        if (!empty($search)) {
            $where .= " AND (i.nombre LIKE :search OR p.nombre LIKE :search2) ";
        }
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " i 
                  LEFT JOIN proveedores p ON i.proveedor_id = p.id
                  WHERE $where";
        $stmt = $this->conn->prepare($query);
        if (!empty($search)) {
            $term = "%" . $search . "%";
            $stmt->bindValue(':search', $term);
            $stmt->bindValue(':search2', $term);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['total'] : 0;
    }

    /**
     * Obtiene un insumo por su ID.
     *
     * @param  int        $id ID del insumo.
     * @return array|false Fila del insumo o false si no existe.
     */
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id AND eliminado = false";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Cambia la visibilidad de un insumo (ocultar/mostrar).
     *
     * @param  int   $id      ID del insumo.
     * @param  int   $visible 1 = visible, 0 = oculto.
     * @return bool
     */
    public function setVisibility($id, $visible) {
        $query = "UPDATE " . $this->table_name . " SET visible = :visible WHERE id = :id AND eliminado = false";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":visible", $visible);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    /**
     * Devuelve los insumos con stock por debajo del mínimo.
     *
     * @return array Lista de insumos con bajo stock.
     */
    public function getLowStock() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE stock_actual <= stock_minimo AND visible = 1 AND eliminado = false";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function delete($id) {
        $query = "UPDATE " . $this->table_name . " SET eliminado = true, deleted_at = NOW() WHERE id = :id AND eliminado = false";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    /**
     * Registra una compra de insumo: inserta en compras_insumos y actualiza stock y precio.
     *
     * @param  int|float $insumo_id     ID del insumo.
     * @param  int       $proveedor_id  ID del proveedor.
     * @param  float     $cantidad      Cantidad comprada.
     * @param  float     $precio_compra Precio unitario de compra.
     * @return bool
     */
    public function registrarCompra($insumo_id, $proveedor_id, $cantidad, $precio_compra) {
        try {
            $this->conn->beginTransaction();
            $queryC = "INSERT INTO compras_insumos (insumo_id, proveedor_id, cantidad, precio_compra) VALUES (:insumo_id, :proveedor_id, :cantidad, :precio_compra)";
            $stmtC = $this->conn->prepare($queryC);
            $stmtC->bindParam(":insumo_id", $insumo_id);
            $stmtC->bindParam(":proveedor_id", $proveedor_id);
            $stmtC->bindParam(":cantidad", $cantidad);
            $stmtC->bindParam(":precio_compra", $precio_compra);
            $stmtC->execute();

            $queryI = "UPDATE " . $this->table_name . " SET stock_actual = stock_actual + :cantidad, precio_costo = :precio_compra, proveedor_id = :proveedor_id WHERE id = :id AND eliminado = false";
            $stmtI = $this->conn->prepare($queryI);
            $stmtI->bindParam(":cantidad", $cantidad);
            $stmtI->bindParam(":precio_compra", $precio_compra);
            $stmtI->bindParam(":proveedor_id", $proveedor_id);
            $stmtI->bindParam(":id", $insumo_id);
            $stmtI->execute();
            $this->conn->commit();
            return true;
        } catch (\Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    /**
     * Actualiza todos los campos de un insumo existente.
     *
     * @param  int|float $id             ID del insumo.
     * @param  int       $proveedor_id   Nuevo proveedor.
     * @param  string    $nombre         Nuevo nombre.
     * @param  string    $unidad_medida  Nueva unidad.
     * @param  float     $stock_actual   Nuevo stock.
     * @param  float     $stock_minimo   Nuevo mínimo.
     * @param  float     $precio_costo   Nuevo costo.
     * @return bool
     */
    public function update($id, $proveedor_id, $nombre, $unidad_medida, $stock_actual, $stock_minimo, $precio_costo) {
        $query = "UPDATE " . $this->table_name . " SET proveedor_id = :proveedor_id, nombre = :nombre, unidad_medida = :unidad_medida, stock_actual = :stock_actual, stock_minimo = :stock_minimo, precio_costo = :precio_costo WHERE id = :id AND eliminado = false";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":proveedor_id", $proveedor_id);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":unidad_medida", $unidad_medida);
        $stmt->bindParam(":stock_actual", $stock_actual);
        $stmt->bindParam(":stock_minimo", $stock_minimo);
        $stmt->bindParam(":precio_costo", $precio_costo);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
}
