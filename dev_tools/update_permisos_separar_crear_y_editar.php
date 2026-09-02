<?php
/**
 * Migración: separar el permiso combinado de creación+edición en dos
 * permisos distintos por cada módulo:
 *   - X.crear   (solo dar de alta / registrar)
 *   - X.editar  (solo modificar / actualizar)
 *
 * Modelo final por módulo CRUD: ver / crear / editar / eliminar.
 *
 * Estrategia (conserva el comportamiento actual):
 *   1. Renombra cada permiso combinado -> "X.crear" conservando su id,
 *      de modo que las asignaciones rol_permiso de ese id ahora significan "crear".
 *   2. Crea el permiso nuevo "X.editar" con id nuevo y copia las asignaciones
 *      rol_permiso del correspondiente "X.crear", para que quien podía
 *      crear+editar siga pudiendo ambas acciones por separado.
 *
 * Ejecutar desde la raíz del proyecto: php dev_tools/update_permisos_separar_crear_y_editar.php
 * Es idempotente: puede volver a ejecutarse sin romper la base de datos.
 */
require_once 'config/database.php';
$db = new Database();
$conn = $db->getConnection();

// Mapa: id_origen (permiso combinado) => [id_nuevo_editar, modulo, nombre_editar, descripcion_editar]
$editar = [
    3  => [33, 'Inventario',   'Editar insumos',            'Modificar insumos, ajustar stock y registrar compras.'],
    6  => [34, 'Proveedores',  'Editar proveedores',        'Editar datos de proveedores.'],
    8  => [35, 'Productos',    'Editar productos',          'Modificar productos y registrar producción.'],
    11 => [36, 'Producción',   'Editar producción',         'Modificar registros de producción manual.'],
    13 => [37, 'Clientes',     'Editar clientes',           'Editar datos de clientes.'],
    15 => [38, 'Pedidos',      'Editar pedidos',            'Editar pedidos y cambiar su estado.'],
    17 => [39, 'Ventas',       'Editar ventas',             'Modificar ventas directas.'],
    20 => [40, 'Gastos',       'Editar gastos',             'Editar registros de gastos.'],
    22 => [41, 'Empleados',    'Editar empleados',          'Editar datos de empleados.'],
];

try {
    $conn->beginTransaction();

    // 1. Pasar el id del permiso actual a id temporal para liberar el id destino.
    //    Se usa id temporal 9000+ para no colisionar con los futuros id 33-41.
    $temporal = [
        3 => 9003, 6 => 9006, 8 => 9008, 11 => 9011, 13 => 9013,
        15 => 9015, 17 => 9017, 20 => 9020, 22 => 9022,
    ];

    // Renombrar código antes de mover el id (el código no depende del id).
    $renombrar = [
        3 => 'inventario.crear',   6 => 'proveedores.crear', 8 => 'productos.crear',
        11 => 'produccion.crear',  13 => 'clientes.crear',   15 => 'pedidos.crear',
        17 => 'ventas.crear',      20 => 'gastos.crear',     22 => 'empleados.crear',
    ];
    $renombresNombre = [
        3 => ['Crear insumos', 'Crear insumos, ajustar stock y registrar compras.'],
        6 => ['Crear proveedores', 'Dar de alta nuevos proveedores.'],
        8 => ['Crear productos', 'Crear productos y registrar producción.'],
        11 => ['Registrar producción', 'Registrar producción manual de lotes.'],
        13 => ['Crear clientes', 'Dar de alta nuevos clientes.'],
        15 => ['Crear pedidos', 'Crear pedidos y cambiar su estado.'],
        17 => ['Crear ventas', 'Registrar ventas directas.'],
        20 => ['Crear gastos', 'Registrar gastos.'],
        22 => ['Crear empleados', 'Dar de alta nuevos empleados.'],
    ];

    foreach ($renombrar as $id => $codigo) {
        $conn->prepare("UPDATE permisos SET codigo = :c, nombre = :n, descripcion = :d WHERE id = :id")
             ->execute([
                 ':c' => $codigo,
                 ':n' => $renombresNombre[$id][0],
                 ':d' => $renombresNombre[$id][1],
                 ':id' => $id,
             ]);
    }

    // 2. Crear los permisos nuevos X.editar con los ids 33-41.
    $stmt = $conn->prepare("INSERT INTO permisos (id, codigo, modulo, nombre, descripcion)
                            VALUES (:id, :codigo, :modulo, :nombre, :descripcion)
                            ON DUPLICATE KEY UPDATE
                              codigo = VALUES(codigo), modulo = VALUES(modulo),
                              nombre = VALUES(nombre), descripcion = VALUES(descripcion)");
    $nombresEditar = [
        3 => 'inventario.editar',   6 => 'proveedores.editar', 8 => 'productos.editar',
        11 => 'produccion.editar',  13 => 'clientes.editar',   15 => 'pedidos.editar',
        17 => 'ventas.editar',      20 => 'gastos.editar',     22 => 'empleados.editar',
    ];
    foreach ($editar as $idOrigen => $info) {
        $stmt->execute([':id' => $info[0], ':codigo' => $nombresEditar[$idOrigen],
                        ':modulo' => $info[1], ':nombre' => $info[2], ':descripcion' => $info[3]]);
    }

    // 3. Copiar las asignaciones rol_permiso de cada X.crear(id origen) al X.editar(id nuevo).
    $copy = $conn->prepare(
        "INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
         SELECT rol_id, :nuevo FROM rol_permiso WHERE permiso_id = :origen"
    );
    foreach ($editar as $idOrigen => $info) {
        $copy->execute([':nuevo' => $info[0], ':origen' => $idOrigen]);
    }

    $conn->commit();

    echo "Ok: creados " . count($editar) . " permisos X.editar y sus asignaciones.<br>";
    echo "Migración 'separar crear/editar' completada correctamente.";

} catch (PDOException $e) {
    $conn->rollBack();
    echo "Error: " . $e->getMessage();
}
