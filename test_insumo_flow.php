<?php
header('Content-Type: application/json');
require_once __DIR__ . '/autoload.php';

try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $db = new \App\Core\Database();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("SELECT u.*, r.nombre as rol_nombre FROM usuarios u JOIN roles r ON u.rol_id = r.id WHERE u.email = :email");
    $stmt->execute([':email' => 'admin@lavicky.com']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['error' => 'Usuario admin no encontrado']);
        exit;
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['nombre'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['rol_nombre'];
    $_SESSION['rol_id'] = $user['rol_id'];

    $permisoModel = new \App\Models\PermisoModel($conn);
    $_SESSION['permisos'] = $permisoModel->getPermisosByRol($user['rol_id']);

    $token = \App\Core\CsrfToken::get();

    $testName = 'Insumo Test Auto ' . time();
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['HTTP_X_CSRF_TOKEN'] = $token;
    $_POST = [
        'nombre' => $testName,
        'unidad_medida' => 'Kg',
        'proveedor_id' => '',
        'stock_inicial' => '15.00',
        'stock_minimo' => '3.00',
        'precio_costo' => '42.50'
    ];

    $audit = new \App\Core\AuditService($conn);
    $insumoModel = new \App\Models\InsumoModel($conn);
    $controller = new \App\Controllers\InsumoController($insumoModel, $audit);

    ob_start();
    $controller->add();
    $output = ob_get_clean();
    $responseJson = json_decode($output, true);

    $checkStmt = $conn->prepare("SELECT * FROM insumos WHERE nombre = :nombre");
    $checkStmt->execute([':nombre' => $testName]);
    $insertedRow = $checkStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'session_user' => $_SESSION['user_name'],
        'session_role' => $_SESSION['user_role'],
        'permisos_count' => count($_SESSION['permisos']),
        'has_inventario_gestionar' => in_array('inventario.gestionar', $_SESSION['permisos']),
        'controller_response' => $responseJson,
        'db_inserted_record' => $insertedRow
    ], JSON_PRETTY_PRINT);

} catch (Throwable $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
