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

    // 4. Catálogo de permisos (idempotente).
    $catalogo = [
        [1,  'dashboard.ver',         'Dashboard',    'Ver dashboard',              'Acceso a la página principal e indicadores.'],
        [2,  'inventario.ver',        'Inventario',   'Ver insumos',                'Listar insumos y alertas de stock bajo.'],
        [3,  'inventario.gestionar',  'Inventario',   'Gestionar insumos',          'Crear insumos, ajustar stock y registrar compras.'],
        [4,  'inventario.eliminar',   'Inventario',   'Eliminar insumos',           'Dar de baja insumos (borrado lógico).'],
        [5,  'proveedores.ver',       'Proveedores',  'Ver proveedores',            'Listar proveedores.'],
        [6,  'proveedores.gestionar', 'Proveedores',  'Gestionar proveedores',      'Crear, editar y eliminar proveedores.'],
        [7,  'productos.ver',         'Productos',    'Ver productos',              'Listar productos y recetas.'],
        [8,  'productos.gestionar',   'Productos',    'Gestionar productos',        'Crear productos y registrar producción.'],
        [9,  'productos.eliminar',    'Productos',    'Eliminar productos',         'Dar de baja productos (borrado lógico).'],
        [10, 'produccion.ver',        'Producción',   'Ver producción',             'Ver historial de producción manual.'],
        [11, 'produccion.gestionar',  'Producción',   'Registrar producción',       'Registrar producción manual de lotes.'],
        [12, 'clientes.ver',          'Clientes',     'Ver clientes',               'Listar clientes y su historial de compras.'],
        [13, 'clientes.gestionar',    'Clientes',     'Gestionar clientes',         'Crear, editar y eliminar clientes.'],
        [14, 'pedidos.ver',           'Pedidos',      'Ver pedidos',                'Listar pedidos y sus detalles.'],
        [15, 'pedidos.gestionar',     'Pedidos',      'Gestionar pedidos',          'Crear pedidos, cambiar estados y eliminarlos.'],
        [16, 'ventas.ver',            'Ventas',       'Ver ventas',                 'Listar ventas, top productos y gráficos.'],
        [17, 'ventas.gestionar',      'Ventas',       'Gestionar ventas',           'Registrar ventas directas y cancelarlas.'],
        [18, 'reportes.ver',          'Reportes',     'Ver reportes',               'Consultar reportes semanales y mensuales.'],
        [19, 'gastos.ver',            'Gastos',       'Ver gastos',                 'Consultar gastos por fecha.'],
        [20, 'gastos.gestionar',      'Gastos',       'Gestionar gastos',           'Registrar y eliminar gastos.'],
        [21, 'empleados.ver',         'Empleados',    'Ver empleados',              'Listar empleados y su rendimiento.'],
        [22, 'empleados.gestionar',   'Empleados',    'Gestionar empleados',        'Crear y eliminar empleados.'],
        [23, 'auditoria.ver',         'Auditoría',    'Ver auditoría',              'Consultar bitácora y accesos denegados.'],
        [24, 'permisos.gestionar',    'Permisos',     'Gestionar permisos',         'Asignar permisos a los roles.'],
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
    echo "Success: catálogo de permisos actualizado (24).<br>";

    // 5. Asignación inicial por rol (replica la matriz de bootstrap.php).
    $conn->exec("INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
                 SELECT r.id, p.id FROM roles r JOIN permisos p
                 WHERE r.nombre = 'Administrador'
                    OR (r.nombre = 'Cajero'    AND p.codigo IN
                        ('dashboard.ver', 'productos.ver', 'clientes.ver', 'clientes.gestionar',
                         'pedidos.ver', 'pedidos.gestionar', 'ventas.ver', 'ventas.gestionar', 'gastos.ver'))
                    OR (r.nombre = 'Panadero'  AND p.codigo IN
                        ('dashboard.ver', 'inventario.ver', 'inventario.gestionar', 'inventario.eliminar',
                         'proveedores.ver', 'proveedores.gestionar', 'productos.ver', 'productos.gestionar',
                         'productos.eliminar', 'produccion.ver', 'produccion.gestionar'))");
    echo "Success: asignación inicial de permisos por rol lista.<br>";

    echo "Migración completada correctamente.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
