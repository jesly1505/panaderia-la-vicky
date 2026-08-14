<?php
require_once __DIR__ . '/config/database.php';

echo "<h2>Actualización de Base de Datos - Producción Manual</h2>";

try {
    $db = new Database();
    $conn = $db->getConnection();

    // 1. Crear tabla producciones
    $sqlProducciones = "
        CREATE TABLE IF NOT EXISTS producciones (
            id INT AUTO_INCREMENT PRIMARY KEY,
            producto_id INT NOT NULL,
            cantidad_producida DECIMAL(10,2) NOT NULL,
            fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (producto_id) REFERENCES productos(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $conn->exec($sqlProducciones);
    echo "<p>✅ Tabla <b>producciones</b> verificada/creada correctamente.</p>";

    // 2. Crear tabla produccion_detalle
    $sqlDetalle = "
        CREATE TABLE IF NOT EXISTS produccion_detalle (
            id INT AUTO_INCREMENT PRIMARY KEY,
            produccion_id INT NOT NULL,
            insumo_id INT NOT NULL,
            cantidad_usada DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (produccion_id) REFERENCES producciones(id) ON DELETE CASCADE,
            FOREIGN KEY (insumo_id) REFERENCES insumos(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $conn->exec($sqlDetalle);
    echo "<p>✅ Tabla <b>produccion_detalle</b> verificada/creada correctamente.</p>";

    echo "<h3>¡Actualización completada con éxito!</h3>";
    echo "<a href='frontend/index.php'>Volver al sistema</a>";

} catch (PDOException $e) {
    echo "<h3>❌ Error en la base de datos:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
