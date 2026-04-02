<?php
require_once 'config/database.php';
$db = new Database();
$conn = $db->getConnection();

try {
    // 1. Insumos visibility
    try {
        $conn->exec("ALTER TABLE insumos ADD COLUMN visible TINYINT(1) DEFAULT 1 AFTER precio_costo");
        echo "Success: Column visible added to insumos.<br>";
    } catch (PDOException $e) {}

    // 2. Pedidos actual delivery time
    try {
        $conn->exec("ALTER TABLE pedidos ADD COLUMN hora_entrega_real TIME DEFAULT NULL AFTER hora_entrega");
        echo "Success: Column hora_entrega_real added to pedidos.<br>";
    } catch (PDOException $e) {}

    // 3. User tracking in Sales
    try {
        $conn->exec("ALTER TABLE ventas ADD COLUMN usuario_id INT(11) DEFAULT NULL AFTER ganancias");
        echo "Success: Column usuario_id added to ventas.<br>";
    } catch (PDOException $e) {}

    // 4. Roles
    $conn->exec("UPDATE roles SET nombre = 'Administrador' WHERE id = 1");
    $conn->exec("UPDATE roles SET nombre = 'Cajero' WHERE id = 2");
    echo "Success: Roles updated.<br>";

    // 5. Ensure admin user has role 1
    $conn->exec("UPDATE usuarios SET rol_id = 1 WHERE id = 1");
    echo "Success: Admin role set.<br>";

    echo "Database update completed successfully.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
