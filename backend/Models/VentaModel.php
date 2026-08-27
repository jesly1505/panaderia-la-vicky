<?php
namespace App\Models;

use PDO;
use Exception;
use Throwable;
use App\Core\Money;
use App\Utils\InventoryLogic;

/**
 * Modelo de acceso a datos para la entidad Venta.
 *
 * Encargado de ejecutar todas las consultas SQL relacionadas con ventas,
 * incluyendo lectura, creación, cancelación, historial, reportes y
 * obtención de detalles con pagos e inventario.
 */
class VentaModel {
    private $conn;
    private $inventoryLogic;
    private $productoModel;

    /**
     * Inyección de dependencias del modelo.
     *
     * @param PDO             $db             Conexión PDO a la base de datos.
     * @param InventoryLogic  $inventoryLogic Lógica de gestión de inventario para descontar/revertir stock.
     * @param ProductoModel   $productoModel  Modelo de acceso a datos de productos para obtener costos.
     */
    public function __construct(PDO $db, InventoryLogic $inventoryLogic, ProductoModel $productoModel) {
        $this->conn = $db;
        $this->inventoryLogic = $inventoryLogic;
        $this->productoModel = $productoModel;
    }

    private function buildWhereClause(string $filterType = 'all', string $startDate = '', string $endDate = '', string $search = '', string $estado = '', string $tipoPago = '', string $vendedor = ''): array {
        $conditions = [];
        $params = [];

        $dateCondition = \App\Helpers\DateFilterHelper::getSqlCondition('v.fecha_venta', $filterType, $startDate, $endDate);
        if (!empty($dateCondition)) {
            $conditions[] = $dateCondition;
        }

        if (!empty($search)) {
            $conditions[] = "(COALESCE(c.nombre, cp.nombre, 'Consumidor Final') LIKE :search OR u.nombre LIKE :search2 OR v.id LIKE :search3)";
            $params[':search'] = "%" . $search . "%";
            $params[':search2'] = "%" . $search . "%";
            $params[':search3'] = "%" . $search . "%";
        }

        if (!empty($estado) && $estado !== 'all') {
            if ($estado === 'completada' || $estado === 'completado') {
                $conditions[] = "v.estado = 'completado'";
            } elseif ($estado === 'anulada' || $estado === 'cancelado' || $estado === 'cancelada') {
                $conditions[] = "v.estado = 'cancelado'";
            } elseif ($estado === 'pendiente') {
                $conditions[] = "v.estado = 'pendiente'";
            } else {
                $conditions[] = "v.estado = :estado";
                $params[':estado'] = $estado;
            }
        }

        if (!empty($tipoPago) && $tipoPago !== 'all') {
            $conditions[] = "v.tipo_pago = :tipo_pago";
            $params[':tipo_pago'] = $tipoPago;
        }

        if (!empty($vendedor) && $vendedor !== 'all') {
            if (is_numeric($vendedor)) {
                $conditions[] = "v.usuario_id = :usuario_id";
                $params[':usuario_id'] = (int)$vendedor;
            } else {
                $conditions[] = "u.nombre = :vendedor_nombre";
                $params[':vendedor_nombre'] = $vendedor;
            }
        }

        $whereSql = !empty($conditions) ? implode(" AND ", $conditions) : "1=1";
        return ['sql' => $whereSql, 'params' => $params];
    }

    /**
     * Retorna la lista de ventas aplicando filtros de fecha, búsqueda, estado,
     * método de pago y vendedor, con soporte de paginación.
     *
     * @param string   $filterType Tipo de filtro temporal (all, today, week, month, year, custom).
     * @param string   $startDate  Fecha de inicio del rango personalizado (formato YYYY-MM-DD).
     * @param string   $endDate    Fecha de fin del rango personalizado (formato YYYY-MM-DD).
     * @param int|null $limit      Cantidad máxima de registros por página, o null para traer todos.
     * @param int|null $offset     Desplazamiento para paginación, o null para traer todos.
     * @param string   $search     Término de búsqueda que coincide con nombre de cliente, vendedor o ID.
     * @param string   $estado     Filtro por estado de la venta (completado, cancelado, pendiente).
     * @param string   $tipoPago   Filtro por método de pago (efectivo, tarjeta, transferencia, otro).
     * @param string   $vendedor   Filtro por nombre o ID del vendedor.
     * @return array<int, array<string, mixed>> Array de ventas con datos asociados.
     */
    public function readAll(string $filterType = 'all', string $startDate = '', string $endDate = '', ?int $limit = null, ?int $offset = null, string $search = '', string $estado = '', string $tipoPago = '', string $vendedor = '') {
        $where = $this->buildWhereClause($filterType, $startDate, $endDate, $search, $estado, $tipoPago, $vendedor);
        $query = "SELECT v.*, p.estado as estado_pedido, u.nombre as vendedor,
                         COALESCE(c.nombre, cp.nombre, 'Consumidor Final') as cliente_nombre
                  FROM ventas v 
                  LEFT JOIN pedidos p ON v.pedido_id = p.id
                  LEFT JOIN clientes cp ON p.cliente_id = cp.id
                  LEFT JOIN clientes c ON v.cliente_id = c.id
                  LEFT JOIN usuarios u ON v.usuario_id = u.id
                  WHERE " . $where['sql'] . "
                  ORDER BY v.fecha_venta DESC";
        if ($limit !== null && $offset !== null) {
            $query .= " LIMIT :limit OFFSET :offset";
        }
        $stmt = $this->conn->prepare($query);
        foreach ($where['params'] as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        if ($limit !== null && $offset !== null) {
            $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cuenta el total de ventas que coinciden con los filtros indicados.
     *
     * @param string $filterType Tipo de filtro temporal (all, today, week, month, year, custom).
     * @param string $startDate  Fecha de inicio del rango personalizado (formato YYYY-MM-DD).
     * @param string $endDate    Fecha de fin del rango personalizado (formato YYYY-MM-DD).
     * @param string $search     Término de búsqueda por nombre de cliente, vendedor o ID.
     * @param string $estado     Filtro por estado de la venta.
     * @param string $tipoPago   Filtro por método de pago.
     * @param string $vendedor   Filtro por nombre o ID del vendedor.
     * @return int Cantidad total de ventas que coinciden con los filtros.
     */
    public function countAll(string $filterType = 'all', string $startDate = '', string $endDate = '', string $search = '', string $estado = '', string $tipoPago = '', string $vendedor = ''): int {
        try {
            $where = $this->buildWhereClause($filterType, $startDate, $endDate, $search, $estado, $tipoPago, $vendedor);
            $query = "SELECT COUNT(*) 
                      FROM ventas v 
                      LEFT JOIN pedidos p ON v.pedido_id = p.id
                      LEFT JOIN clientes cp ON p.cliente_id = cp.id
                      LEFT JOIN clientes c ON v.cliente_id = c.id
                      LEFT JOIN usuarios u ON v.usuario_id = u.id
                      WHERE " . $where['sql'];
            $stmt = $this->conn->prepare($query);
            foreach ($where['params'] as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Calcula los totales agregados de ingresos y ganancias del periodo filtrado,
     * excluyendo ventas canceladas.
     *
     * @param string $filterType Tipo de filtro temporal (all, today, week, month, year, custom).
     * @param string $startDate  Fecha de inicio del rango personalizado (formato YYYY-MM-DD).
     * @param string $endDate    Fecha de fin del rango personalizado (formato YYYY-MM-DD).
     * @param string $search     Término de búsqueda por nombre de cliente, vendedor o ID.
     * @param string $estado     Filtro por estado de la venta.
     * @param string $tipoPago   Filtro por método de pago.
     * @param string $vendedor   Filtro por nombre o ID del vendedor.
     * @return array{total_ingresos: float, total_ganancias: float} Totales del periodo.
     */
    public function getTotals(string $filterType = 'all', string $startDate = '', string $endDate = '', string $search = '', string $estado = '', string $tipoPago = '', string $vendedor = ''): array {
        try {
            $where = $this->buildWhereClause($filterType, $startDate, $endDate, $search, $estado, $tipoPago, $vendedor);
            $query = "SELECT COALESCE(SUM(v.total), 0) as total_ingresos, 
                             COALESCE(SUM(v.ganancias), 0) as total_ganancias 
                      FROM ventas v 
                      LEFT JOIN pedidos p ON v.pedido_id = p.id
                      LEFT JOIN clientes cp ON p.cliente_id = cp.id
                      LEFT JOIN clientes c ON v.cliente_id = c.id
                      LEFT JOIN usuarios u ON v.usuario_id = u.id
                      WHERE " . $where['sql'] . " AND v.estado != 'cancelado'";
            $stmt = $this->conn->prepare($query);
            foreach ($where['params'] as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->execute();
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return [
                'total_ingresos' => (float)($res['total_ingresos'] ?? 0),
                'total_ganancias' => (float)($res['total_ganancias'] ?? 0),
            ];
        } catch (\Exception $e) {
            return ['total_ingresos' => 0.0, 'total_ganancias' => 0.0];
        }
    }

    /**
     * Retorna la lista de usuarios vendedores activos (no eliminados) ordenados
     * alfabéticamente por nombre.
     *
     * @return array<int, array{id: int, nombre: string}> Lista de vendedores con su ID y nombre.
     */
    public function getVendedores(): array {
        try {
            $query = "SELECT DISTINCT u.id, u.nombre 
                      FROM usuarios u 
                      WHERE u.eliminado = 0 
                      ORDER BY u.nombre ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Registra una venta directa (sin pedido previo), insertando la venta, sus
     * detalles, los pagos asociados y descontando el inventario de cada producto.
     *
     * Calcula las ganancias reales restando el costo de los insumos al total vendido.
     * Toda la operación se ejecuta dentro de una transacción de base de datos.
     *
     * @param array<string, mixed> $data       Datos de la venta: subtotal, impuestos, descuento,
     *                                         total, cliente_id, detalles (array) y pagos (array).
     * @param int|null             $usuario_id ID del usuario vendedor que realiza la venta.
     * @return int|false El ID de la venta creada, o false si ocurrió un error.
     */
    public function createDirecta($data, ?int $usuario_id = null) {
        try {
            $this->conn->beginTransaction();

            $subtotal = Money::round($data['subtotal'] ?? 0);
            $impuestos = Money::round($data['impuestos'] ?? 0);
            $descuento = Money::round($data['descuento'] ?? 0);
            $total = Money::round($data['total'] ?? 0);
            $cliente_id = !empty($data['cliente_id']) ? (int)$data['cliente_id'] : null;
            $detalles = $data['detalles'] ?? [];
            $pagos = $data['pagos'] ?? [];

            // Calcular ganancias reales (ventas - costos de insumos)
            $costoTotal = 0;
            foreach ($detalles as $detalle) {
                $costoTotal += $this->productoModel->getCost($detalle['producto_id']) * $detalle['cantidad'];
            }
            $ganancias = Money::round($total - $costoTotal);

            // 1. Insertar Venta
            $tipo_pago = $pagos[0]['metodo'] ?? 'efectivo';
            $query = "INSERT INTO ventas (pedido_id, cliente_id, subtotal, impuestos, descuento, total, tipo_pago, ganancias, estado, usuario_id) 
                      VALUES (NULL, :cliente_id, :subtotal, :impuestos, :descuento, :total, :tipo_pago, :ganancias, 'completado', :usuario_id)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":cliente_id", $cliente_id);
            $stmt->bindParam(":subtotal", $subtotal);
            $stmt->bindParam(":impuestos", $impuestos);
            $stmt->bindParam(":descuento", $descuento);
            $stmt->bindParam(":total", $total);
            $stmt->bindParam(":tipo_pago", $tipo_pago);
            $stmt->bindParam(":ganancias", $ganancias);
            $stmt->bindParam(":usuario_id", $usuario_id);
            $stmt->execute();
            
            $venta_id = $this->conn->lastInsertId();

            // 2. Insertar Detalle Venta
            foreach ($detalles as $detalle) {
                $qd = "INSERT INTO detalle_venta (venta_id, producto_id, cantidad, precio_unitario, descuento, subtotal)
                       VALUES (:venta_id, :prod_id, :cant, :precio, :desc, :subtotal)";
                $stmtD = $this->conn->prepare($qd);
                $d_precio = Money::round($detalle['precio_unitario'] ?? $detalle['precio'] ?? 0);
                $d_desc = Money::round($detalle['descuento'] ?? 0);
                $d_subtotal = Money::round($detalle['cantidad'] * $d_precio - $d_desc);

                $stmtD->bindParam(":venta_id", $venta_id);
                $stmtD->bindParam(":prod_id", $detalle['producto_id']);
                $stmtD->bindParam(":cant", $detalle['cantidad']);
                $stmtD->bindParam(":precio", $d_precio);
                $stmtD->bindParam(":desc", $d_desc);
                $stmtD->bindParam(":subtotal", $d_subtotal);
                $stmtD->execute();
            }

            // 3. Insertar Pagos (Múltiples métodos)
            foreach ($pagos as $pago) {
                $qp = "INSERT INTO pagos (venta_id, monto, metodo_pago, estado, referencia) 
                       VALUES (:venta_id, :monto, :metodo, 'completado', :ref)";
                $stmtP = $this->conn->prepare($qp);
                $p_monto = Money::round($pago['monto']);
                $stmtP->bindParam(":venta_id", $venta_id);
                $stmtP->bindParam(":monto", $p_monto);
                $stmtP->bindParam(":metodo", $pago['metodo']);
                $stmtP->bindValue(":ref", $pago['referencia'] ?? null);
                $stmtP->execute();
            }

            // 4. Descontar inventario
            $this->inventoryLogic->descontarVarios($detalles);

            $this->conn->commit();
            return $venta_id;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            error_log("Error en createDirecta: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cancela una venta existente, revierte el inventario de cada producto vendido,
     * actualiza el estado de la venta a 'cancelado' y marca los pagos como 'fallido'.
     *
     * @param int $id Identificador de la venta a cancelar.
     * @return bool true si la cancelación fue exitosa, false si ocurrió un error
     *              o la venta no existe / ya está cancelada.
     */
    public function cancelarVenta($id) {
        try {
            $this->conn->beginTransaction();

            // 1. Obtener datos de la venta
            $qV = "SELECT estado FROM ventas WHERE id = :id";
            $stmtV = $this->conn->prepare($qV);
            $stmtV->bindParam(":id", $id);
            $stmtV->execute();
            $venta = $stmtV->fetch(PDO::FETCH_ASSOC);

            if (!$venta || $venta['estado'] === 'cancelado') {
                throw new Exception("La venta no existe o ya está cancelada.");
            }

            // 2. Obtener detalles para revertir stock
            $qD = "SELECT producto_id, cantidad FROM detalle_venta WHERE venta_id = :id";
            $stmtD = $this->conn->prepare($qD);
            $stmtD->bindParam(":id", $id);
            $stmtD->execute();
            $detalles = $stmtD->fetchAll(PDO::FETCH_ASSOC);

            // 3. Revertir Inventario
            $this->inventoryLogic->revertirVarios($detalles);

            // 4. Actualizar estado de la venta
            $qU = "UPDATE ventas SET estado = 'cancelado' WHERE id = :id";
            $stmtU = $this->conn->prepare($qU);
            $stmtU->bindParam(":id", $id);
            $stmtU->execute();

            // 5. Opcional: Actualizar estado de los pagos asociados
            $qP = "UPDATE pagos SET estado = 'fallido' WHERE venta_id = :id";
            $stmtP = $this->conn->prepare($qP);
            $stmtP->bindParam(":id", $id);
            $stmtP->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            error_log("Error al cancelar venta: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crea una venta a partir de un pedido existente cuando este se marca como 'entregado'.
     *
     * Obtiene los datos y detalles del pedido, calcula las ganancias restando el
     * costo de los insumos, inserta la venta, el detalle, el pago y descuenta el
     * inventario. La transacción es externa si ya existe una activa.
     *
     * @param int                $pedido_id  Identificador del pedido source.
     * @param array<string, mixed> $data     Datos opcionales adicionales (tipo_pago, metodo_pago).
     * @param int|null           $usuario_id ID del usuario vendedor.
     * @return int|false El ID de la venta creada, o false si ocurrió un error.
     */
    public function createFromPedido($pedido_id, $data = [], ?int $usuario_id = null) {
        $startedTransaction = false;
        try {
            if (!$this->conn->inTransaction()) {
                $this->conn->beginTransaction();
                $startedTransaction = true;
            }

            // 1. Obtener datos del pedido
            $qP = "SELECT total FROM pedidos WHERE id = :id";
            $stmtP = $this->conn->prepare($qP);
            $stmtP->bindParam(":id", $pedido_id);
            $stmtP->execute();
            $pedido = $stmtP->fetch();

            // 2. Obtener detalles del pedido
            $qD = "SELECT producto_id, cantidad, precio_unitario FROM detalle_pedido WHERE pedido_id = :id";
            $stmtD = $this->conn->prepare($qD);
            $stmtD->bindParam(":id", $pedido_id);
            $stmtD->execute();
            $detalles = $stmtD->fetchAll();

            $costoTotal = 0;
            foreach ($detalles as $detalle) {
                $costoTotal += $this->productoModel->getCost($detalle['producto_id']) * $detalle['cantidad'];
            }
            $pedido_total = Money::round($pedido['total']);
            $ganancias = Money::round($pedido_total - $costoTotal);

            // 3. Registrar Venta (simplificada para pedidos previa implementación total)
            $tipo_pago = $data['tipo_pago'] ?? 'efectivo';
            $query = "INSERT INTO ventas (pedido_id, subtotal, impuestos, descuento, total, tipo_pago, ganancias, estado, usuario_id) 
                      VALUES (:pedido_id, :total, 0, 0, :total, :tipo_pago, :ganancias, 'completado', :usuario_id)";
            $stmtV = $this->conn->prepare($query);
            $stmtV->bindParam(":pedido_id", $pedido_id);
            $stmtV->bindParam(":total", $pedido_total);
            $stmtV->bindParam(":tipo_pago", $tipo_pago);
            $stmtV->bindParam(":ganancias", $ganancias);
            $stmtV->bindParam(":usuario_id", $usuario_id);
            $stmtV->execute();
            $venta_id = $this->conn->lastInsertId();

            // Detalle venta desde pedido
            foreach ($detalles as $detalle) {
                $qd = "INSERT INTO detalle_venta (venta_id, producto_id, cantidad, precio_unitario, subtotal)
                       VALUES (:venta_id, :prod_id, :cant, :precio, :sub)";
                $sd = $this->conn->prepare($qd);
                $precio = Money::round($detalle['precio_unitario']);
                $sub = Money::round($detalle['cantidad'] * $precio);
                $sd->bindParam(":venta_id", $venta_id);
                $sd->bindParam(":prod_id", $detalle['producto_id']);
                $sd->bindParam(":cant", $detalle['cantidad']);
                $sd->bindParam(":precio", $precio);
                $sd->bindParam(":sub", $sub);
                $sd->execute();
            }

            // 4. Pago único por el total
            $metodo = $data['metodo_pago'] ?? 'efectivo';
            $qp = "INSERT INTO pagos (venta_id, monto, metodo_pago, estado) 
                   VALUES (:venta_id, :monto, :metodo, 'completado')";
            $sp = $this->conn->prepare($qp);
            $sp->bindParam(":venta_id", $venta_id);
            $sp->bindParam(":monto", $pedido_total);
            $sp->bindParam(":metodo", $metodo);
            $sp->execute();

            // 5. Descontar Inventario
            $this->inventoryLogic->descontarVarios($detalles);

            if ($startedTransaction && $this->conn->inTransaction()) {
                $this->conn->commit();
            }
            return $venta_id;
        } catch (Exception $e) {
            if ($startedTransaction && $this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Error en createFromPedido: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retorna el historial de ventas más recientes con el nombre del vendedor.
     *
     * @param int $limit Cantidad máxima de registros a retornar (por defecto 50).
     * @return array<int, array<string, mixed>> Array de ventas ordenadas por fecha descendente.
     */
    public function getSalesHistory($limit = 50) {
        $query = "SELECT v.*, u.nombre as vendedor 
                  FROM ventas v 
                  LEFT JOIN usuarios u ON v.usuario_id = u.id 
                  ORDER BY v.fecha_venta DESC LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retorna el ranking de productos más vendidos (por cantidad total vendida),
     * excluyendo las ventas canceladas.
     *
     * @param int $limit Cantidad máxima de productos a retornar (por defecto 5).
     * @return array<int, array{nombre: string, total_vendido: int}> Productos ordenados por ventas descendentes.
     */
    public function getTopProducts($limit = 5) {
        $query = "SELECT p.nombre, SUM(dv.cantidad) as total_vendido 
                  FROM detalle_venta dv 
                  JOIN productos p ON dv.producto_id = p.id 
                  WHERE (SELECT estado FROM ventas WHERE id = dv.venta_id) != 'cancelado'
                  GROUP BY dv.producto_id 
                  ORDER BY total_vendido DESC LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retorna los datos de ingresos diarios de los últimos 7 días para
     * renderizar el gráfico de ingresos del dashboard.
     *
     * @return array<int, array{fecha: string, total_dia: float}> Serie temporal de ingresos por día.
     */
    public function getRevenueChartData() {
        $query = "SELECT DATE(fecha_venta) as fecha, SUM(total) as total_dia 
                  FROM ventas 
                  WHERE estado != 'cancelado' AND fecha_venta >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                  GROUP BY DATE(fecha_venta) 
                  ORDER BY fecha ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene una venta por su ID junto con sus detalles (productos vendidos)
     * y los pagos asociados.
     *
     * @param int $id Identificador de la venta.
     * @return array|null Array con los datos de la venta, incluyendo claves
     *                    'detalles' y 'pagos', o null si la venta no existe.
     */
    public function getVentaConDetalles($id) {
        $query = "SELECT v.*, u.nombre as vendedor, 
                         COALESCE(c.nombre, cp.nombre) as cliente_nombre, 
                         COALESCE(c.email, cp.email) as cliente_email, 
                         COALESCE(c.direccion, cp.direccion) as cliente_direccion,
                         COALESCE(c.dni, cp.dni) as cliente_dni,
                         COALESCE(c.telefono, cp.telefono) as cliente_telefono
                  FROM ventas v 
                  LEFT JOIN usuarios u ON v.usuario_id = u.id 
                  LEFT JOIN pedidos p ON v.pedido_id = p.id 
                  LEFT JOIN clientes cp ON p.cliente_id = cp.id 
                  LEFT JOIN clientes c ON v.cliente_id = c.id
                  WHERE v.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $venta = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($venta) {
            $qd = "SELECT dv.*, p.nombre as producto_nombre 
                   FROM detalle_venta dv 
                   JOIN productos p ON dv.producto_id = p.id 
                   WHERE dv.venta_id = :id";
            $stmtD = $this->conn->prepare($qd);
            $stmtD->bindParam(":id", $id);
            $stmtD->execute();
            $venta['detalles'] = $stmtD->fetchAll(PDO::FETCH_ASSOC);

            $qp = "SELECT * FROM pagos WHERE venta_id = :id";
            $stmtP = $this->conn->prepare($qp);
            $stmtP->bindParam(":id", $id);
            $stmtP->execute();
            $venta['pagos'] = $stmtP->fetchAll(PDO::FETCH_ASSOC);
        }
        return $venta;
    }
}
?>
