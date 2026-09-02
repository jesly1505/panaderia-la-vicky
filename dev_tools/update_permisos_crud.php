<?php
/**
 * Migración: separar permisos por acción CRUD (ver / gestionar / eliminar)
 * y añadir permiso de impresión de factura (ventas.ver_factura).
 *
 * Convierte los permisos que mezclaban "crear/editar/eliminar" en gestionar,
 * separando la acción destructiva en un permiso propio "X.eliminar".
 *
 * Ejecutar desde la raíz del proyecto: php dev_tools/update_permisos_crud.php
 * Es idempotente: puede volver a ejecutarse sin romper la base de datos.
 */
require_once 'config/database.php';
$db = new Database();
$conn = $db->getConnection();

// Nuevos permisos por módulo. id > 25 (los 25 actuales) para no colisionar.
$nuevos = [
    [26, 'proveedores.eliminar', 'Proveedores', 'Eliminar proveedores',   'Dar de baja proveedores (borrado lógico).'],
    [27, 'clientes.eliminar',    'Clientes',    'Eliminar clientes',      'Dar de baja clientes (borrado lógico).'],
    [28, 'pedidos.eliminar',     'Pedidos',     'Eliminar pedidos',       'Eliminar pedidos (borrado lógico).'],
    [29, 'ventas.eliminar',      'Ventas',      'Eliminar ventas',        'Anular/cancelar ventas.'],
    [30, 'ventas.ver_factura',   'Ventas',      'Ver e imprimir factura', 'Abrir e imprimir la factura de una venta.'],
    [31, 'gastos.eliminar',      'Gastos',      'Eliminar gastos',        'Eliminar registros de gastos.'],
    [32, 'empleados.eliminar',   'Empleados',   'Eliminar empleados',     'Eliminar empleados y sus usuarios.'],
];

try {
    // 1. Insertar / actualizar permisos nuevos.
    $stmt = $conn->prepare("INSERT INTO permisos (id, codigo, modulo, nombre, descripcion)
                            VALUES (:id, :codigo, :modulo, :nombre, :descripcion)
                            ON DUPLICATE KEY UPDATE
                              codigo = VALUES(codigo), modulo = VALUES(modulo),
                              nombre = VALUES(nombre), descripcion = VALUES(descripcion)");
    foreach ($nuevos as $p) {
        $stmt->execute([':id' => $p[0], ':codigo' => $p[1], ':modulo' => $p[2],
                        ':nombre' => $p[3], ':descripcion' => $p[4]]);
    }
    echo "Ok: permisos nuevos registrados (" . count($nuevos) . ").<br>";

    // 2. Asignación por rol conservando el comportamiento actual.
    //    El Administrador recibe todos los nuevos (por rol ya tiene acceso total,
    //    pero se registran para mantener consistencia en la matriz).
    //    Cajero: conserva su capacidad de eliminar clientes/pedidos/ventas y de ver factura.
    //    Panadero: conserva su capacidad de eliminar proveedores.
    $conn->exec("INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
                 SELECT r.id, p.id FROM roles r JOIN permisos p
                 WHERE (r.nombre = 'Administrador'
                        AND p.codigo IN
                            ('proveedores.eliminar', 'clientes.eliminar', 'pedidos.eliminar',
                             'ventas.eliminar', 'ventas.ver_factura', 'gastos.eliminar',
                             'empleados.eliminar'))
                    OR (r.nombre = 'Cajero' AND p.codigo IN
                            ('clientes.eliminar', 'pedidos.eliminar', 'ventas.eliminar',
                             'ventas.ver_factura'))
                    OR (r.nombre = 'Panadero' AND p.codigo IN
                            ('proveedores.eliminar'))");
    echo "Ok: asignación por rol actualizada.<br>";

    echo "Migración CRUD de permisos completada correctamente.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
