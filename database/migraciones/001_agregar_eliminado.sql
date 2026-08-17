-- =============================================================
-- Migración: Agregar campo `eliminado` (BOOLEAN) a tablas
-- con borrado lógico. Ejecutar una sola vez.
-- =============================================================
USE `la_vicky_db`;

-- Solo agregar si no existe (idempotente)
-- Cada bloque verifica con IF NOT EXISTS para seguridad

-- 1. usuarios
SET @exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='la_vicky_db' AND TABLE_NAME='usuarios' AND COLUMN_NAME='eliminado');
SET @sql = IF(@exists = 0,
    'ALTER TABLE `usuarios` ADD COLUMN `eliminado` tinyint(1) NOT NULL DEFAULT 0 AFTER `created_at`, ADD INDEX `idx_usuarios_elim` (`eliminado`), ADD INDEX `idx_usuarios_email_elim` (`email`, `eliminado`)',
    'SELECT "usuarios: eliminado ya existe" AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2. clientes
SET @exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='la_vicky_db' AND TABLE_NAME='clientes' AND COLUMN_NAME='eliminado');
SET @sql = IF(@exists = 0,
    'ALTER TABLE `clientes` ADD COLUMN `eliminado` tinyint(1) NOT NULL DEFAULT 0 AFTER `created_at`, ADD INDEX `idx_clientes_elim` (`eliminado`), ADD INDEX `idx_clientes_email_elim` (`email`, `eliminado`)',
    'SELECT "clientes: eliminado ya existe" AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3. productos
SET @exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='la_vicky_db' AND TABLE_NAME='productos' AND COLUMN_NAME='eliminado');
SET @sql = IF(@exists = 0,
    'ALTER TABLE `productos` ADD COLUMN `eliminado` tinyint(1) NOT NULL DEFAULT 0 AFTER `created_at`, ADD INDEX `idx_productos_elim` (`eliminado`), ADD INDEX `idx_productos_cat_elim` (`categoria`, `eliminado`)',
    'SELECT "productos: eliminado ya existe" AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 4. proveedores
SET @exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='la_vicky_db' AND TABLE_NAME='proveedores' AND COLUMN_NAME='eliminado');
SET @sql = IF(@exists = 0,
    'ALTER TABLE `proveedores` ADD COLUMN `eliminado` tinyint(1) NOT NULL DEFAULT 0 AFTER `email`, ADD INDEX `idx_proveedores_elim` (`eliminado`)',
    'SELECT "proveedores: eliminado ya existe" AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 5. insumos
SET @exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='la_vicky_db' AND TABLE_NAME='insumos' AND COLUMN_NAME='eliminado');
SET @sql = IF(@exists = 0,
    'ALTER TABLE `insumos` ADD COLUMN `eliminado` tinyint(1) NOT NULL DEFAULT 0 AFTER `visible`, ADD INDEX `idx_insumos_elim` (`eliminado`)',
    'SELECT "insumos: eliminado ya existe" AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 6. pedidos
SET @exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='la_vicky_db' AND TABLE_NAME='pedidos' AND COLUMN_NAME='eliminado');
SET @sql = IF(@exists = 0,
    'ALTER TABLE `pedidos` ADD COLUMN `eliminado` tinyint(1) NOT NULL DEFAULT 0 AFTER `hora_entrega_real`, ADD INDEX `idx_pedidos_elim` (`eliminado`)',
    'SELECT "pedidos: eliminado ya existe" AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 7. gastos
SET @exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='la_vicky_db' AND TABLE_NAME='gastos' AND COLUMN_NAME='eliminado');
SET @sql = IF(@exists = 0,
    'ALTER TABLE `gastos` ADD COLUMN `eliminado` tinyint(1) NOT NULL DEFAULT 0 AFTER `fecha`, ADD INDEX `idx_gastos_elim` (`eliminado`)',
    'SELECT "gastos: eliminado ya existe" AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT 'Migración de borrado lógico completada.' AS resultado;
