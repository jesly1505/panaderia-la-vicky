<?php
/**
 * Migración: permisos por rol (RBAC) + borrado lógico (soft delete).
 * Ejecutar desde la raíz del proyecto: php dev_tools/update_permisos_soft_delete.php
 * Es idempotente: puede volver a ejecutarse sin romper la BD.
 */
require_once 'config/database.php';
$db = new Database();
$conn = $db->getConnection();

function columnExists(PDO $conn, string $table, string $column): bool {
    $stmt = $conn->prepare("SHOW COLUMNS FROM `{$table}` LIKE :col");
    $stmt->bindValue(':col', $column);
    $stmt->execute();
    return (bool)$stmt->fetch();
}

function addDeletedAt(PDO $conn, string $table): void {
    try {
        if (!columnExists($conn, $table, 'deleted_at')) {
            $conn->exec("ALTER TABLE `{$table}` ADD COLUMN `deleted_at` DATETIME DEFAULT NULL");
            echo "Success: deleted_at agregada a {$table}.<br>";
        } else {
            echo "Info: {$table} ya tiene deleted_at.<br>";
        }
    } catch (PDOException $e) {
        echo "Info: {$table}: " . $e->getMessage() . "<br>";
    }
}

try {
    // 1. Borrado lógico en las 7 tablas que hoy usan DELETE físico.
    foreach (['productos', 'insumos', 'clientes', 'proveedores', 'gastos', 'pedidos', 'usuarios'] as $tabla) {
        addDeletedAt($conn, $tabla);
    }

    // 2. Tabla de permisos (catálogo).
    $conn->exec("CREATE TABLE IF NOT EXISTS `permisos` (
        `id` int NOT NULL AUTO_INCREMENT,
        `codigo` varchar(100) NOT NULL,
        `modulo` varchar(100) NOT NULL,
        `nombre` varchar(150) NOT NULL,
        `descripcion` text,
        PRIMARY KEY (`id`),
        UNIQUE KEY `codigo` (`codigo`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    echo "Success: tabla permisos lista.<br>";

    // 3. Tabla de asignación rol -> permiso.
    $conn->exec("CREATE TABLE IF NOT EXISTS `rol_permiso` (
        `rol_id` int NOT NULL,
        `permiso_id` int NOT NULL,
        PRIMARY KEY (`rol_id`, `permiso_id`),
        KEY `permiso_id` (`permiso_id`),
        CONSTRAINT `rol_permiso_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
        CONSTRAINT `rol_permiso_ibfk_2` FOREIGN KEY (`permiso_id`) REFERENCES `permisos` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    echo "Success: tabla rol_permiso lista.<br>";

    // 4. Catálogo de permisos (idempotente). Modelo final: ver / crear / editar / eliminar.
    $catalogo = [
        [1,  'dashboard.ver',         'Dashboard',    'Ver dashboard',              'Acceso a la página principal e indicadores.'],
        [2,  'inventario.ver',        'Inventario',   'Ver insumos',                'Listar insumos y alertas de stock bajo.'],
        [3,  'inventario.crear',  'Inventario',   'Crear insumos',              'Crear insumos, ajustar stock y registrar compras.'],
        [33, 'inventario.editar', 'Inventario',   'Editar insumos',             'Modificar insumos, ajustar stock y registrar compras.'],
        [4,  'inventario.eliminar',   'Inventario',   'Eliminar insumos',           'Dar de baja insumos (borrado lógico).'],
        [5,  'proveedores.ver',       'Proveedores',  'Ver proveedores',            'Listar proveedores.'],
        [6,  'proveedores.crear', 'Proveedores',  'Crear proveedores',          'Dar de alta nuevos proveedores.'],
        [34, 'proveedores.editar','Proveedores',  'Editar proveedores',         'Editar datos de proveedores.'],
        [7,  'productos.ver',         'Productos',    'Ver productos',              'Listar productos y recetas.'],
        [8,  'productos.crear',   'Productos',    'Crear productos',            'Crear productos y registrar producción.'],
        [35, 'productos.editar',  'Productos',    'Editar productos',           'Modificar productos y registrar producción.'],
        [9,  'productos.eliminar',    'Productos',    'Eliminar productos',         'Dar de baja productos (borrado lógico).'],
        [10, 'produccion.ver',        'Producción',   'Ver producción',             'Ver historial de producción manual.'],
        [11, 'produccion.crear',  'Producción',   'Registrar producción',        'Registrar producción manual de lotes.'],
        [36, 'produccion.editar', 'Producción',   'Editar producción',          'Modificar registros de producción manual.'],
        [12, 'clientes.ver',          'Clientes',     'Ver clientes',               'Listar clientes y su historial de compras.'],
        [13, 'clientes.crear',    'Clientes',     'Crear clientes',             'Dar de alta nuevos clientes.'],
        [37, 'clientes.editar',   'Clientes',     'Editar clientes',            'Editar datos de clientes.'],
        [14, 'pedidos.ver',           'Pedidos',      'Ver pedidos',                'Listar pedidos y sus detalles.'],
        [15, 'pedidos.crear',     'Pedidos',      'Crear pedidos',              'Crear pedidos y cambiar su estado.'],
        [38, 'pedidos.editar',    'Pedidos',      'Editar pedidos',             'Editar pedidos y cambiar su estado.'],
        [16, 'ventas.ver',            'Ventas',       'Ver ventas',                 'Listar ventas, top productos y gráficos.'],
        [17, 'ventas.crear',      'Ventas',       'Crear ventas',               'Registrar ventas directas.'],
        [39, 'ventas.editar',     'Ventas',       'Editar ventas',              'Modificar ventas directas.'],
        [18, 'reportes.ver',          'Reportes',     'Ver reportes',               'Consultar reportes semanales y mensuales.'],
        [19, 'gastos.ver',            'Gastos',       'Ver gastos',                 'Consultar gastos por fecha.'],
        [20, 'gastos.crear',      'Gastos',       'Crear gastos',               'Registrar gastos.'],
        [40, 'gastos.editar',     'Gastos',       'Editar gastos',              'Editar registros de gastos.'],
        [21, 'empleados.ver',         'Empleados',    'Ver empleados',              'Listar empleados y su rendimiento.'],
        [22, 'empleados.crear',   'Empleados',    'Crear empleados',            'Dar de alta nuevos empleados.'],
        [41, 'empleados.editar',  'Empleados',    'Editar empleados',           'Editar datos de empleados.'],
        [23, 'auditoria.ver',         'Auditoría',    'Ver auditoría',              'Consultar bitácora y accesos denegados.'],
        [24, 'permisos.gestionar',    'Permisos',     'Gestionar permisos',         'Asignar permisos a los roles.'],
        [25, 'perfil.gestionar',      'Configuración','Gestionar perfil de la panadería', 'Editar datos del negocio: nombre, descripción, dirección, teléfono, RUC, moneda e impuestos.'],
        [26, 'proveedores.eliminar','Proveedores', 'Eliminar proveedores',      'Dar de baja proveedores (borrado lógico).'],
        [27, 'clientes.eliminar',   'Clientes',    'Eliminar clientes',         'Dar de baja clientes (borrado lógico).'],
        [28, 'pedidos.eliminar',    'Pedidos',     'Eliminar pedidos',          'Eliminar pedidos (borrado lógico).'],
        [29, 'ventas.eliminar',     'Ventas',      'Eliminar ventas',           'Anular/cancelar ventas.'],
        [30, 'ventas.ver_factura',  'Ventas',      'Ver e imprimir factura',    'Abrir e imprimir la factura de una venta.'],
        [31, 'gastos.eliminar',     'Gastos',      'Eliminar gastos',           'Eliminar registros de gastos.'],
        [32, 'empleados.eliminar',  'Empleados',   'Eliminar empleados',        'Eliminar empleados y sus usuarios.'],
    ];
    $stmt = $conn->prepare("INSERT INTO permisos (id, codigo, modulo, nombre, descripcion)
                            VALUES (:id, :codigo, :modulo, :nombre, :descripcion)
                            ON DUPLICATE KEY UPDATE
                              codigo = VALUES(codigo), modulo = VALUES(modulo),
                              nombre = VALUES(nombre), descripcion = VALUES(descripcion)");
    foreach ($catalogo as $permiso) {
        $stmt->execute([':id' => $permiso[0], ':codigo' => $permiso[1], ':modulo' => $permiso[2],
                        ':nombre' => $permiso[3], ':descripcion' => $permiso[4]]);
    }
    echo "Success: catálogo de permisos actualizado (" . count($catalogo) . ").<br>";

    // 5. Asignación inicial por rol (replica la matriz de bootstrap.php).
    $conn->exec("INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
                 SELECT r.id, p.id FROM roles r JOIN permisos p
                 WHERE r.nombre = 'Administrador'
                    OR (r.nombre = 'Cajero'    AND p.codigo IN
                        ('dashboard.ver', 'productos.ver', 'clientes.ver', 'clientes.crear', 'clientes.editar', 'clientes.eliminar',
                         'pedidos.ver', 'pedidos.crear', 'pedidos.editar', 'pedidos.eliminar', 'ventas.ver', 'ventas.crear',
                         'ventas.editar', 'ventas.eliminar', 'ventas.ver_factura', 'gastos.ver'))
                    OR (r.nombre = 'Panadero'  AND p.codigo IN
                        ('dashboard.ver', 'inventario.ver', 'inventario.crear', 'inventario.editar', 'inventario.eliminar',
                         'proveedores.ver', 'proveedores.crear', 'proveedores.editar', 'proveedores.eliminar',
                         'productos.ver', 'productos.crear', 'productos.editar', 'productos.eliminar',
                         'produccion.ver', 'produccion.crear', 'produccion.editar'))");
    echo "Success: asignación inicial de permisos por rol lista.<br>";

    echo "Migración completada correctamente.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
