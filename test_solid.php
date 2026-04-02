<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/backend/Models/InsumoModel.php';
require_once __DIR__ . '/backend/Controllers/InsumoController.php';

echo "--- TEST SOLID: Verificación de Inyección de Dependencias ---\n";

try {
    // 1. Instanciar la base de datos (DIP)
    $db_instance = new Database();
    $conn = $db_instance->getConnection();
    echo "[OK] Conexión obtenida exitosamente.\n";

    // 2. Instanciar el modelo/repositorio inyectando la conexión (DIP)
    $repo = new InsumoModel($conn);
    echo "[OK] Repositorio instanciado con inyección de conexión.\n";

    // 3. Instanciar el controlador inyectando el repositorio (DIP)
    $controller = new InsumoController($repo);
    echo "[OK] Controlador instanciado con inyección de repositorio.\n";

    // 4. Probar una operación real
    echo "\nConsultando insumos via Controller...\n";
    
    // Capturar la salida del echo del controlador
    ob_start();
    $controller->getLowStock();
    $output = ob_get_clean();
    
    $data = json_encode(json_decode($output, true), JSON_PRETTY_PRINT);
    echo "Respuesta JSON del Controlador:\n" . $data . "\n";
    
    if (strpos($output, '"success":true') !== false) {
        echo "\n[SUCCESS] El flujo SOLID para Insumos es correcto.\n";
    } else {
        echo "\n[ERROR] Falló la respuesta del controlador.\n";
    }

} catch (Exception $e) {
    echo "\n[FATAL ERROR] " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
?>
