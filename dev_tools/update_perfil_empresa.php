<?php
/**
 * Migración: perfil de la panadería (datos del negocio para la factura).
 * Crea la tabla `empresa`, el permiso `perfil.gestionar` (id 25) y
 * la fila por defecto. No sobreescribe un perfil ya personalizado.
 * Ejecutar desde la raíz del proyecto: php dev_tools/update_perfil_empresa.php
 * Es idempotente: puede volver a ejecutarse sin romper la BD.
 */
require_once 'config/database.php';
$db = new Database();
$conn = $db->getConnection();

function tableExists(PDO $conn, string $table): bool {
    $stmt = $conn->prepare("SHOW TABLES LIKE :t");
    $stmt->bindValue(':t', $table);
    $stmt->execute();
    return (bool)$stmt->fetch();
}

try {
    // 1. Tabla empresa (fila única, id = 1).
    $conn->exec("CREATE TABLE IF NOT EXISTS `empresa` (
        `id` int NOT NULL,
        `nombre` varchar(100) NOT NULL DEFAULT 'Panadería La Vicky',
        `descripcion` varchar(255) DEFAULT NULL,
        `direccion` varchar(255) DEFAULT NULL,
        `telefono` varchar(30) DEFAULT NULL,
        `ruc` varchar(30) DEFAULT NULL,
        `moneda` varchar(10) NOT NULL DEFAULT 'USD',
        `tasa_impuesto` decimal(5,2) NOT NULL DEFAULT '15.00',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    echo "Success: tabla empresa lista.<br>";

    // 2. Fila por defecto (solo si no existe; respeta cambios previos).
    $conn->exec("INSERT IGNORE INTO empresa
                 (id, nombre, descripcion, direccion, telefono, ruc, moneda, tasa_impuesto)
                 VALUES (1, 'Panadería La Vicky', 'Panadería & Pastelería',
                         'Av. Principal calle 5', '1234-5678', NULL, 'USD', 15.00)");
    echo "Success: perfil por defecto creado (si no existía).<br>";

    // 3. Permiso perfil.gestionar (id 25).
    $conn->exec("INSERT INTO permisos (id, codigo, modulo, nombre, descripcion)
                 VALUES (25, 'perfil.gestionar', 'Configuración', 'Gestionar perfil de la panadería',
                         'Editar datos del negocio: nombre, descripción, dirección, teléfono, RUC, moneda e impuestos.')
                 ON DUPLICATE KEY UPDATE
                   codigo = VALUES(codigo), modulo = VALUES(modulo),
                   nombre = VALUES(nombre), descripcion = VALUES(descripcion)");
    echo "Success: permiso perfil.gestionar listo.<br>";

    // 4. Asignación del nuevo permiso al Administrador.
    $conn->exec("INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
                 SELECT r.id, p.id FROM roles r JOIN permisos p
                 WHERE r.nombre = 'Administrador' AND p.codigo = 'perfil.gestionar'");
    echo "Success: perfil.gestionar asignado a Administrador.<br>";

    echo "Migración completada correctamente.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
