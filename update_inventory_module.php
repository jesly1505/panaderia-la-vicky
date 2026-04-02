<?php
require_once __DIR__ . '/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    echo "--- Iniciando actualización del Módulo de Inventario ---\n";

    // 1. Agregar columna 'visible' a 'insumos' si no existe
    $checkVisible = $db->query("SHOW COLUMNS FROM insumos LIKE 'visible'");
    if ($checkVisible->rowCount() == 0) {
        $db->exec("ALTER TABLE insumos ADD COLUMN visible TINYINT(1) DEFAULT 1");
        echo "[OK] Columna 'visible' añadida a 'insumos'.\n";
    } else {
        echo "[SKIP] Columna 'visible' ya existe en 'insumos'.\n";
    }

    // 2. Crear tabla 'compras_insumos'
    $queryCompras = "CREATE TABLE IF NOT EXISTS compras_insumos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        insumo_id INT NOT NULL,
        proveedor_id INT NOT NULL,
        cantidad DECIMAL(10,2) NOT NULL,
        precio_compra DECIMAL(10,2) NOT NULL,
        fecha_compra TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (insumo_id) REFERENCES insumos(id) ON DELETE CASCADE,
        FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

    $db->exec($queryCompras);
    echo "[OK] Tabla 'compras_insumos' creada o ya existente.\n";

    echo "--- Actualización finalizada con éxito ---\n";

} catch (PDOException $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
}
?>
