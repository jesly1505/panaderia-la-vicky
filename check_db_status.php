<?php
header('Content-Type: application/json');
require_once __DIR__ . '/autoload.php';

try {
    $db = new \App\Core\Database();
    $conn = $db->getConnection();

    $permisosAdmin = $conn->query("
        SELECT p.codigo, p.modulo, p.nombre
        FROM permisos p
        JOIN rol_permiso rp ON rp.permiso_id = p.id
        JOIN roles r ON r.id = rp.rol_id
        WHERE r.nombre = 'Administrador'
    ")->fetchAll(PDO::FETCH_ASSOC);

    $allPermisos = $conn->query("SELECT id, codigo FROM permisos")->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'adminPermisosCount' => count($permisosAdmin),
        'adminPermisos' => array_column($permisosAdmin, 'codigo'),
        'hasInventarioGestionar' => in_array('inventario.gestionar', array_column($permisosAdmin, 'codigo')),
        'allPermisos' => $allPermisos
    ], JSON_PRETTY_PRINT);
} catch (Throwable $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
