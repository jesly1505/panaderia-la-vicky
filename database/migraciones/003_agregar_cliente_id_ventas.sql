-- Migración: Agregar columna cliente_id a la tabla ventas
ALTER TABLE `ventas` 
ADD COLUMN `cliente_id` INT DEFAULT NULL AFTER `pedido_id`,
ADD CONSTRAINT `fk_ventas_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL;
