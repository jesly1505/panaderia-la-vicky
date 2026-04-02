<?php
require_once __DIR__ . '/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    echo "--- Iniciando actualización del Módulo de Reportes ---\n";

    // 1. Crear tabla 'gastos' si no existe
    $queryGastos = "CREATE TABLE IF NOT EXISTS gastos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        descripcion VARCHAR(255) NOT NULL,
        monto DECIMAL(10,2) NOT NULL,
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

    $db->exec($queryGastos);
    echo "[OK] Tabla 'gastos' creada o ya existente.\n";

    echo "--- Actualización finalizada con éxito ---\n";

} catch (PDOException $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
}
?>
