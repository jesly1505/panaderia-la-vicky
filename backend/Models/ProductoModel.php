<?php
namespace App\Models;

use PDO;

class ProductoModel {
    private $conn;
    private $table_name = "productos";

    /**
     * Subconsulta SQL del costo de insumos de una receta, agregada por venta.
     * Única fuente de verdad para ganancias; usada por ReporteModel y VentaModel.
     * Requiere el alias "v" para la tabla ventas y columnas venta_id/cantidad
     * en detalle_venta.
     */
    public const COSTO_VENTA_SUBQUERY = "(
        SELECT SUM(dv.cantidad * pr.cantidad_requerida * i.precio_costo)
        FROM detalle_venta dv
        JOIN producto_receta pr ON pr.producto_id = dv.producto_id
        JOIN insumos i ON i.id = pr.insumo_id
        WHERE dv.venta_id = v.id
    )";

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    public function create($nombre, $descripcion, $precio, $categoria, $cantidad, $ingredientes, $stock_minimo = 0) {
        try {
            // Validar stock si hay cantidad inicial
            if ($cantidad > 0) {
                $validation = $this->validateStock($ingredientes, $cantidad);
                if ($validation !== true) {
                    return ['insuficiente' => $validation];
                }
            }

            $this->conn->beginTransaction();
            $query = "INSERT INTO " . $this->table_name . " 
                      (nombre, descripcion, precio_venta, categoria, stock_actual, stock_minimo) 
                      VALUES (:nombre, :descripcion, :precio, :categoria, :cantidad, :stock_min)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":nombre", $nombre);
            $stmt->bindParam(":descripcion", $descripcion);
            $stmt->bindParam(":precio", $precio);
            $stmt->bindParam(":categoria", $categoria);
            $stmt->bindParam(":cantidad", $cantidad);
            $stmt->bindParam(":stock_min", $stock_minimo);
            $stmt->execute();
            
            $producto_id = $this->conn->lastInsertId();
            
            foreach ($ingredientes as $ing) {
                if (empty($ing['insumo_id']) || empty($ing['cantidad_requerida'])) continue;
                
                // 1. Registrar el insumo en la receta del producto
                $q_receta = "INSERT INTO producto_receta (producto_id, insumo_id, cantidad_requerida) VALUES (:prod_id, :insumo_id, :cant_req)";
                $stmtR = $this->conn->prepare($q_receta);
                $stmtR->bindValue(":prod_id", $producto_id);
                $stmtR->bindValue(":insumo_id", $ing['insumo_id']);
                $stmtR->bindValue(":cant_req", $ing['cantidad_requerida']);
                $stmtR->execute();

                // 2. Deducir del inventario de insumos (si hay cantidad inicial de producto)
                if ($cantidad > 0) {
                    $total_a_descontar = $cantidad * $ing['cantidad_requerida'];
                    $q_descuento = "UPDATE insumos SET stock_actual = stock_actual - :descuento WHERE id = :insumo_id";
                    $stmtD = $this->conn->prepare($q_descuento);
                    $stmtD->bindValue(":descuento", $total_a_descontar);
                    $stmtD->bindValue(":insumo_id", $ing['insumo_id']);
                    $stmtD->execute();
                }
            }
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            return false;
        }
    }

    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE deleted_at IS NULL 
                  ORDER BY categoria ASC, nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByCategoria($categoria) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE categoria = :categoria AND deleted_at IS NULL 
                  ORDER BY nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':categoria', $categoria);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id AND deleted_at IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getReceta($id) {
        $query = "SELECT * FROM producto_receta WHERE producto_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateStock($id, $nuevoStock) {
        $query = "UPDATE " . $this->table_name . " SET stock_actual = :stock WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":stock", $nuevoStock);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function getCost($id) {
        $query = "SELECT pr.cantidad_requerida, i.precio_costo 
                  FROM producto_receta pr
                  JOIN insumos i ON pr.insumo_id = i.id
                  WHERE pr.producto_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $items = $stmt->fetchAll();
        
        $totalCost = 0;
        foreach ($items as $item) {
            $totalCost += $item['cantidad_requerida'] * $item['precio_costo'];
        }
        return $totalCost;
    }

    /**
     * Valida que todos los insumos tengan suficiente stock antes de crear un producto.
     * Returns true if ok, or array of insufficient ingredients.
     */
    public function validateStock($ingredientes, $cantidad) {
        $insufficient = [];
        foreach ($ingredientes as $ing) {
            if (empty($ing['insumo_id']) || empty($ing['cantidad_requerida'])) continue;
            $needed = $cantidad * $ing['cantidad_requerida'];
            $q = "SELECT nombre, stock_actual FROM insumos WHERE id = :id";
            $s = $this->conn->prepare($q);
            $s->bindParam(':id', $ing['insumo_id']);
            $s->execute();
            $row = $s->fetch(PDO::FETCH_ASSOC);
            if ($row && $row['stock_actual'] < $needed) {
                $insufficient[] = "{$row['nombre']} (necesita {$needed}, tiene {$row['stock_actual']})";
            }
        }
        return empty($insufficient) ? true : $insufficient;
    }

    /**
     * Produce una cantidad de un producto:
     * 1. Lee la receta del producto (producto_receta JOIN insumos).
     * 2. Verifica que haya suficiente stock para TODOS los insumos.
     * 3. Si hay faltantes, devuelve un array con el detalle (rollback implícito, nada se modifica).
     * 4. Si todo está bien, descuenta los insumos y suma el stock del producto en una transacción.
     *
     * @param  int   $producto_id  ID del producto a producir.
     * @param  float $cantidad     Cantidad de unidades a producir.
     * @return true|array          true = éxito; array = lista de insumos insuficientes.
     */
    public function producir($producto_id, $cantidad) {
        try {
            $this->conn->beginTransaction();

            // ── 1. Leer receta con stock actual de cada insumo ──────────────
            $qReceta = "SELECT pr.insumo_id,
                               pr.cantidad_requerida,
                               i.nombre        AS insumo_nombre,
                               i.stock_actual  AS stock_actual,
                               i.unidad_medida AS unidad
                        FROM producto_receta pr
                        JOIN insumos i ON i.id = pr.insumo_id
                        WHERE pr.producto_id = :producto_id";
            $stmtR = $this->conn->prepare($qReceta);
            $stmtR->bindParam(':producto_id', $producto_id);
            $stmtR->execute();
            $receta = $stmtR->fetchAll(PDO::FETCH_ASSOC);

            // Si el producto no tiene receta asignada no se puede producir
            if (empty($receta)) {
                $this->conn->rollBack();
                return ['sin_receta' => true,
                        'mensaje'    => 'Este producto no tiene receta de insumos asignada.'];
            }

            // ── 2. Verificar stock suficiente ────────────────────────────────
            $faltantes = [];
            foreach ($receta as $ing) {
                $necesario = $ing['cantidad_requerida'] * $cantidad;
                if ($ing['stock_actual'] < $necesario) {
                    $faltantes[] = [
                        'nombre'     => $ing['insumo_nombre'],
                        'unidad'     => $ing['unidad'],
                        'necesita'   => $necesario,
                        'disponible' => $ing['stock_actual'],
                    ];
                }
            }

            if (!empty($faltantes)) {
                // No modificamos nada; devolvemos el problema
                $this->conn->rollBack();
                return $faltantes;
            }

            // ── 3. Descontar insumos ─────────────────────────────────────────
            $qDesc = "UPDATE insumos
                      SET stock_actual = stock_actual - :descuento
                      WHERE id = :insumo_id";
            $stmtD = $this->conn->prepare($qDesc);
            foreach ($receta as $ing) {
                $descuento = $ing['cantidad_requerida'] * $cantidad;
                $stmtD->bindValue(':descuento',  $descuento,          PDO::PARAM_STR);
                $stmtD->bindValue(':insumo_id',  $ing['insumo_id'],   PDO::PARAM_INT);
                $stmtD->execute();
            }

            // ── 4. Aumentar stock del producto ───────────────────────────────
            $qProd = "UPDATE " . $this->table_name . "
                      SET stock_actual = stock_actual + :cantidad
                      WHERE id = :id";
            $stmtP = $this->conn->prepare($qProd);
            $stmtP->bindParam(':cantidad', $cantidad, PDO::PARAM_STR);
            $stmtP->bindParam(':id',       $producto_id, PDO::PARAM_INT);
            $stmtP->execute();

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Da de baja un producto (borrado lógico).
     * Si el producto aparece en ventas o pedidos no se elimina:
     * devuelve ['en_uso' => true] para preservar el histórico.
     * La receta se conserva para permitir una futura restauración.
     *
     * @return true|array
     */
    public function delete($id) {
        try {
            $qUse = "SELECT
                        (SELECT COUNT(*) FROM detalle_venta WHERE producto_id = :id) +
                        (SELECT COUNT(*) FROM detalle_pedido WHERE producto_id = :id) AS usos";
            $sUse = $this->conn->prepare($qUse);
            $sUse->bindParam(':id', $id);
            $sUse->execute();
            $usos = (int)$sUse->fetchColumn();

            if ($usos > 0) {
                return ['en_uso' => true];
            }

            $qP = "UPDATE " . $this->table_name . " SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL";
            $sP = $this->conn->prepare($qP);
            $sP->bindParam(':id', $id);
            $ok = $sP->execute() && $sP->rowCount() > 0;
            return $ok;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
