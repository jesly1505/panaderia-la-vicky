<?php
require_once __DIR__ . '/../../config/database.php';

class ProduccionModel {
    private $conn;

    public function __construct($db = null) {
        if ($db) {
            $this->conn = $db;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    /**
     * Registra una producción manual:
     * 1. Verifica stock de los insumos usados.
     * 2. Descuenta insumos.
     * 3. Aumenta stock del producto.
     * 4. Registra en `producciones` y `produccion_detalle`.
     */
    public function create($producto_id, $cantidad_producida, $insumos_usados) {
        try {
            $this->conn->beginTransaction();

            // 1. Validar stock de todos los insumos primero
            $faltantes = [];
            foreach ($insumos_usados as $ins) {
                $qStock = "SELECT nombre, stock_actual, unidad_medida FROM insumos WHERE id = :id";
                $stmtStock = $this->conn->prepare($qStock);
                $stmtStock->bindParam(':id', $ins['insumo_id']);
                $stmtStock->execute();
                $row = $stmtStock->fetch(PDO::FETCH_ASSOC);

                if (!$row) {
                    $this->conn->rollBack();
                    return ['error' => 'Un insumo proporcionado no existe en la base de datos.'];
                }

                if ($row['stock_actual'] < $ins['cantidad_usada']) {
                    $faltantes[] = "{$row['nombre']} (disponible: {$row['stock_actual']} {$row['unidad_medida']})";
                }
            }

            if (!empty($faltantes)) {
                $this->conn->rollBack();
                return ['insuficiente' => $faltantes];
            }

            // 2. Insertar en tabla `producciones`
            $qProd = "INSERT INTO producciones (producto_id, cantidad_producida) VALUES (:producto_id, :cantidad)";
            $stmtP = $this->conn->prepare($qProd);
            $stmtP->bindParam(':producto_id', $producto_id);
            $stmtP->bindParam(':cantidad', $cantidad_producida);
            $stmtP->execute();
            $produccion_id = $this->conn->lastInsertId();

            // 3. Descontar insumos e insertar detalles
            $qDetalle = "INSERT INTO produccion_detalle (produccion_id, insumo_id, cantidad_usada) VALUES (:prod_id, :insumo_id, :cantidad)";
            $stmtDet = $this->conn->prepare($qDetalle);
            
            $qUpdateIns = "UPDATE insumos SET stock_actual = stock_actual - :cantidad WHERE id = :insumo_id";
            $stmtUpdIns = $this->conn->prepare($qUpdateIns);

            foreach ($insumos_usados as $ins) {
                // Detalle
                $stmtDet->bindValue(':prod_id', $produccion_id);
                $stmtDet->bindValue(':insumo_id', $ins['insumo_id']);
                $stmtDet->bindValue(':cantidad', $ins['cantidad_usada']);
                $stmtDet->execute();

                // Descuento stock insumo
                $stmtUpdIns->bindValue(':cantidad', $ins['cantidad_usada']);
                $stmtUpdIns->bindValue(':insumo_id', $ins['insumo_id']);
                $stmtUpdIns->execute();
            }

            // 4. Aumentar stock del producto final
            $qUpdateProd = "UPDATE productos SET stock_actual = stock_actual + :cantidad WHERE id = :producto_id";
            $stmtUpdProd = $this->conn->prepare($qUpdateProd);
            $stmtUpdProd->bindParam(':cantidad', $cantidad_producida);
            $stmtUpdProd->bindParam(':producto_id', $producto_id);
            $stmtUpdProd->execute();

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Obtiene el historial de producciones.
     */
    public function getAll() {
        $query = "
            SELECT 
                p.id, 
                p.cantidad_producida, 
                p.fecha, 
                prod.nombre AS producto_nombre,
                (
                    SELECT GROUP_CONCAT(CONCAT(i.nombre, ': ', pd.cantidad_usada, ' ', i.unidad_medida) SEPARATOR '| ')
                    FROM produccion_detalle pd
                    JOIN insumos i ON pd.insumo_id = i.id
                    WHERE pd.produccion_id = p.id
                ) AS detalles_insumos
            FROM producciones p
            JOIN productos prod ON p.producto_id = prod.id
            ORDER BY p.fecha DESC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
