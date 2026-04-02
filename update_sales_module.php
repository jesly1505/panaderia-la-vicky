<?php
require_once 'config/database.php';
$db = new Database();
$conn = $db->getConnection();

try {
    echo "Starting Sales Module database updates...<br>";

    // Update Ventas table
    $checks_ventas = [
        "subtotal" => "ALTER TABLE ventas ADD COLUMN subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER pedido_id",
        "impuestos" => "ALTER TABLE ventas ADD COLUMN impuestos DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER subtotal",
        "descuento" => "ALTER TABLE ventas ADD COLUMN descuento DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER impuestos",
        "estado" => "ALTER TABLE ventas ADD COLUMN estado ENUM('completado', 'cancelado') NOT NULL DEFAULT 'completado' AFTER ganancias"
    ];

    foreach ($checks_ventas as $col => $sql) {
        try {
            $conn->exec($sql);
            echo "Success: Column '$col' added to 'ventas'.<br>";
        } catch (PDOException $e) {
            echo "Note: Column '$col' already exists or error: " . $e->getMessage() . "<br>";
        }
    }

    // Update Detalle Venta table
    try {
        $conn->exec("ALTER TABLE detalle_venta ADD COLUMN descuento DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER precio_unitario");
        echo "Success: Column 'descuento' added to 'detalle_venta'.<br>";
    } catch (PDOException $e) {
        echo "Note: Column 'descuento' in 'detalle_venta' already exists or error.<br>";
    }

    // Update Pagos table
    try {
        $conn->exec("ALTER TABLE pagos ADD COLUMN monto DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER venta_id");
        echo "Success: Column 'monto' added to 'pagos'.<br>";
    } catch (PDOException $e) {
        echo "Note: Column 'monto' in 'pagos' already exists or error.<br>";
    }

    echo "<b>Database updates for Sales Module completed.</b>";

} catch (PDOException $e) {
    echo "Fatal Error: " . $e->getMessage();
}
?>
