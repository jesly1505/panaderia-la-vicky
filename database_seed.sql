-- ============================================================================
-- BASE DE DATOS: DUMP Y SEED COMPLETO PARA "LA VICKY"
-- Entorno: Local MySQL / MariaDB (XAMPP)
-- Usuario Admin: admin@lavicky.com / admin123
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET TIME_ZONE = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------
-- 1. Estructura de la tabla `roles`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `roles` (`id`, `nombre`, `descripcion`) VALUES
(1, 'Administrador', 'Acceso total y control administrativo del sistema'),
(2, 'Cajero', 'Acceso operativo a ventas, pedidos y productos'),
(3, 'Panadero', 'Acceso a inventario de materias primas, recetas y producción de lotes');

-- --------------------------------------------------------
-- 2. Estructura de la tabla `usuarios`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rol_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `intentos_fallidos` int(11) DEFAULT 0,
  `bloqueado_hasta` datetime DEFAULT NULL,
  `ultimo_acceso` datetime DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expira` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `eliminado` boolean NOT NULL DEFAULT false,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `rol_id` (`rol_id`),
  KEY `idx_usuarios_elim` (`eliminado`),
  KEY `idx_usuarios_email_elim` (`email`,`eliminado`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Admin: admin@lavicky.com | Pass: admin123
-- Cajero: cajero@lavicky.com | Pass: admin123
-- Panadero (Prueba Dev): panadero.test@lavicky.com | Pass: admin123
INSERT INTO `usuarios` (`id`, `rol_id`, `nombre`, `email`, `password_hash`, `estado`, `intentos_fallidos`, `bloqueado_hasta`, `ultimo_acceso`, `eliminado`, `deleted_at`) VALUES
(1, 1, 'Administrador General', 'admin@lavicky.com', '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0, NULL, CURRENT_TIMESTAMP, false, NULL),
(2, 2, 'Carlos Vendedor', 'cajero@lavicky.com', '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0, NULL, CURRENT_TIMESTAMP, false, NULL),
(3, 3, 'Panadero de Prueba (Dev)', 'panadero.test@lavicky.com', '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0, NULL, CURRENT_TIMESTAMP, false, NULL);

-- --------------------------------------------------------
-- 3. Estructura de la tabla `empleados_rrhh`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `empleados_rrhh`;
CREATE TABLE `empleados_rrhh` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `salario` decimal(10,2) NOT NULL,
  `turno` varchar(50) DEFAULT NULL,
  `fecha_ingreso` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `empleados_rrhh_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `empleados_rrhh` (`id`, `usuario_id`, `salario`, `turno`, `fecha_ingreso`) VALUES
(1, 1, 1200.00, 'Matutino', '2025-01-15'),
(2, 2, 600.00, 'Vespertino', '2025-02-01');

-- --------------------------------------------------------
-- 4. Estructura de la tabla `proveedores`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `proveedores`;
CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `contacto` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `eliminado` boolean NOT NULL DEFAULT false,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_proveedores_elim` (`eliminado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `proveedores` (`id`, `nombre`, `contacto`, `telefono`, `email`, `eliminado`, `deleted_at`) VALUES
(1, 'Distribuidora Molinos del Sur', 'Roberto Gómez', '555-0192', 'ventas@molinosdelsur.com', false, NULL),
(2, 'Lácteos y Harinas La Granja', 'Ana Martínez', '555-0384', 'contacto@lagranja.com', false, NULL),
(3, 'Empaques y Dulces Victoria', 'Luis Fernández', '555-0771', 'pedidos@empaquesvictoria.com', false, NULL);

-- --------------------------------------------------------
-- 5. Estructura de la tabla `insumos`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `insumos`;
CREATE TABLE `insumos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `proveedor_id` int(11) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `unidad_medida` varchar(20) NOT NULL,
  `stock_actual` decimal(10,2) DEFAULT 0.00,
  `stock_minimo` decimal(10,2) DEFAULT 0.00,
  `precio_costo` decimal(10,2) NOT NULL,
  `visible` tinyint(1) DEFAULT 1,
  `eliminado` boolean NOT NULL DEFAULT false,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `proveedor_id` (`proveedor_id`),
  KEY `idx_insumos_elim` (`eliminado`),
  CONSTRAINT `insumos_ibfk_1` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `insumos` (`id`, `proveedor_id`, `nombre`, `unidad_medida`, `stock_actual`, `stock_minimo`, `precio_costo`, `visible`, `eliminado`, `deleted_at`) VALUES
(1, 1, 'Harina de Trigo Integral', 'Kg', 150.00, 20.00, 1.20, 1, false, NULL),
(2, 2, 'Mantequilla Sin Sal', 'Kg', 40.00, 5.00, 4.50, 1, false, NULL),
(3, 2, 'Leche Entera', 'Litros', 80.00, 15.00, 0.90, 1, false, NULL),
(4, 1, 'Azúcar Refinada', 'Kg', 100.00, 10.00, 1.10, 1, false, NULL),
(5, 3, 'Cacao en Polvo Premium', 'Kg', 12.00, 5.00, 8.50, 1, false, NULL),
(6, 2, 'Huevos Frescos', 'Unidades', 300.00, 50.00, 0.15, 1, false, NULL),
(7, 3, 'Esencia de Vainilla', 'Litros', 2.00, 1.00, 12.00, 1, false, NULL),
(8, 1, 'Levadura en Polvo', 'Kg', 3.00, 5.00, 3.20, 1, false, NULL);

-- --------------------------------------------------------
-- 6. Estructura de la tabla `compras_insumos`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `compras_insumos`;
CREATE TABLE `compras_insumos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `insumo_id` int(11) NOT NULL,
  `proveedor_id` int(11) DEFAULT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `precio_compra` decimal(10,2) NOT NULL,
  `fecha_compra` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `insumo_id` (`insumo_id`),
  KEY `proveedor_id` (`proveedor_id`),
  CONSTRAINT `compras_insumos_ibfk_1` FOREIGN KEY (`insumo_id`) REFERENCES `insumos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `compras_insumos_ibfk_2` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `compras_insumos` (`id`, `insumo_id`, `proveedor_id`, `cantidad`, `precio_compra`, `fecha_compra`) VALUES
(1, 1, 1, 100.00, 1.20, DATE_SUB(NOW(), INTERVAL 10 DAY)),
(2, 2, 2, 30.00, 4.50, DATE_SUB(NOW(), INTERVAL 8 DAY)),
(3, 4, 1, 50.00, 1.10, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(4, 5, 3, 10.00, 8.50, DATE_SUB(NOW(), INTERVAL 2 DAY));

-- --------------------------------------------------------
-- 7. Estructura de la tabla `productos`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `productos`;
CREATE TABLE `productos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio_venta` decimal(10,2) NOT NULL,
  `costo_produccion` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock_actual` int(11) DEFAULT 0,
  `stock_minimo` int(11) DEFAULT 0,
  `categoria` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `eliminado` boolean NOT NULL DEFAULT false,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_productos_elim` (`eliminado`),
  KEY `idx_productos_cat_elim` (`categoria`,`eliminado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `productos` (`id`, `nombre`, `descripcion`, `precio_venta`, `costo_produccion`, `stock_actual`, `stock_minimo`, `categoria`, `eliminado`, `deleted_at`) VALUES
(1, 'Pastel Artesanal de Chocolate', 'Delicioso pastel esponjoso elaborado con cacao fino', 25.00, 8.75, 12, 5, 'Postres', false, NULL),
(2, 'Empanada Rellena de Pollo', 'Empanada crujiente dorada al horno con relleno artesanal', 3.50, 1.10, 45, 10, 'Bocadillos', false, NULL),
(3, 'Pan de Queso Especial', 'Pan suave con queso horneado al momento', 2.00, 0.65, 3, 10, 'Panadería', false, NULL),
(4, 'Galletas Caseras de Avena', 'Caja de 6 galletas saludables de avainillado y avena', 5.00, 1.80, 25, 5, 'Galletas', false, NULL),
(5, 'Tarta de Vainilla y Frutas', 'Tarta fría rellena de crema pastelera y esencia de vainilla', 18.00, 5.50, 8, 3, 'Postres', false, NULL);

-- --------------------------------------------------------
-- 8. Estructura de la tabla `producto_receta`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `producto_receta`;
CREATE TABLE `producto_receta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `producto_id` int(11) NOT NULL,
  `insumo_id` int(11) NOT NULL,
  `cantidad_requerida` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `producto_id` (`producto_id`),
  KEY `insumo_id` (`insumo_id`),
  CONSTRAINT `producto_receta_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `producto_receta_ibfk_2` FOREIGN KEY (`insumo_id`) REFERENCES `insumos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `producto_receta` (`id`, `producto_id`, `insumo_id`, `cantidad_requerida`) VALUES
(1, 1, 1, 0.50),
(2, 1, 2, 0.20),
(3, 1, 4, 0.30),
(4, 1, 5, 0.25),
(5, 1, 6, 4.00),
(6, 1, 7, 0.02),
(7, 2, 1, 0.15),
(8, 2, 2, 0.05),
(9, 2, 6, 1.00),
(10, 3, 1, 0.10),
(11, 3, 3, 0.10),
(12, 3, 8, 0.01),
(13, 4, 1, 0.20),
(14, 4, 2, 0.10),
(15, 4, 4, 0.15);

-- --------------------------------------------------------
-- 9. Estructura de la tabla `producciones`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `producciones`;
CREATE TABLE `producciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `producto_id` int(11) NOT NULL,
  `cantidad_producida` decimal(10,2) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `producto_id` (`producto_id`),
  CONSTRAINT `producciones_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `producciones` (`id`, `producto_id`, `cantidad_producida`, `fecha`) VALUES
(1, 1, 10.00, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(2, 2, 50.00, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(3, 4, 30.00, DATE_SUB(NOW(), INTERVAL 1 DAY));

-- --------------------------------------------------------
-- 10. Estructura de la tabla `produccion_detalle`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `produccion_detalle`;
CREATE TABLE `produccion_detalle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produccion_id` int(11) NOT NULL,
  `insumo_id` int(11) NOT NULL,
  `cantidad_usada` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `produccion_id` (`produccion_id`),
  KEY `insumo_id` (`insumo_id`),
  CONSTRAINT `produccion_detalle_ibfk_1` FOREIGN KEY (`produccion_id`) REFERENCES `producciones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `produccion_detalle_ibfk_2` FOREIGN KEY (`insumo_id`) REFERENCES `insumos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `produccion_detalle` (`id`, `produccion_id`, `insumo_id`, `cantidad_usada`) VALUES
(1, 1, 1, 5.00),
(2, 1, 2, 2.00),
(3, 1, 4, 3.00),
(4, 1, 5, 2.50),
(5, 2, 1, 7.50),
(6, 2, 2, 2.50),
(7, 3, 1, 6.00),
(8, 3, 4, 4.50);

-- --------------------------------------------------------
-- 11. Estructura de la tabla `clientes`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `clientes`;
CREATE TABLE `clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `puntos_fidelidad` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `eliminado` boolean NOT NULL DEFAULT false,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_clientes_elim` (`eliminado`),
  KEY `idx_clientes_email_elim` (`email`,`eliminado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `clientes` (`id`, `nombre`, `email`, `telefono`, `direccion`, `puntos_fidelidad`, `eliminado`, `deleted_at`) VALUES
(1, 'María Elena López', 'maria.lopez@example.com', '555-1029', 'Av. Central #45, San José', 120, false, NULL),
(2, 'Juan Carlos Pérez', 'juan.perez@example.com', '555-8832', 'Calle Los Olivos #12', 45, false, NULL),
(3, 'Sofía Ramírez', 'sofia.ramirez@example.com', '555-9921', 'Residencial Las Flores B-4', 200, false, NULL),
(4, 'Cliente General POS', 'pos@lavicky.com', 'N/A', 'Venta en Mostrador', 0, false, NULL);

-- --------------------------------------------------------
-- 12. Estructura de la tabla `pedidos`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `pedidos`;
CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) NOT NULL,
  `estado` enum('pendiente','en_proceso','entregado','cancelado') DEFAULT 'pendiente',
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `fecha_pedido` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_entrega` date DEFAULT NULL,
  `hora_entrega` time DEFAULT NULL,
  `hora_entrega_real` time DEFAULT NULL,
  `eliminado` boolean NOT NULL DEFAULT false,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cliente_id` (`cliente_id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `idx_pedidos_elim` (`eliminado`),
  CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pedidos_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `pedidos` (`id`, `cliente_id`, `usuario_id`, `estado`, `total`, `fecha_pedido`, `fecha_entrega`, `hora_entrega`, `hora_entrega_real`, `eliminado`, `deleted_at`) VALUES
(1, 1, 1, 'entregado', 50.00, DATE_SUB(NOW(), INTERVAL 4 DAY), CURDATE(), '10:00:00', '09:55:00', false, NULL),
(2, 2, 2, 'pendiente', 28.50, NOW(), CURDATE(), '16:00:00', NULL, false, NULL),
(3, 3, 1, 'en_proceso', 60.00, NOW(), CURDATE(), '18:30:00', NULL, false, NULL);

-- --------------------------------------------------------
-- 13. Estructura de la tabla `detalle_pedido`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `detalle_pedido`;
CREATE TABLE `detalle_pedido` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pedido_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_id` (`pedido_id`),
  KEY `producto_id` (`producto_id`),
  CONSTRAINT `detalle_pedido_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detalle_pedido_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `detalle_pedido` (`id`, `pedido_id`, `producto_id`, `cantidad`, `precio_unitario`, `subtotal`) VALUES
(1, 1, 1, 2, 25.00, 50.00),
(2, 2, 2, 3, 3.50, 10.50),
(3, 2, 5, 1, 18.00, 18.00),
(4, 3, 1, 2, 25.00, 50.00),
(5, 3, 4, 2, 5.00, 10.00);

-- --------------------------------------------------------
-- 14. Estructura de la tabla `ventas`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `ventas`;
CREATE TABLE `ventas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pedido_id` int(11) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `impuestos` decimal(10,2) NOT NULL DEFAULT 0.00,
  `descuento` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `monto_pagado` decimal(10,2) DEFAULT NULL,
  `cambio` decimal(10,2) DEFAULT NULL,
  `ganancias` decimal(10,2) NOT NULL DEFAULT 0.00,
  `usuario_id` int(11) DEFAULT NULL,
  `estado` enum('completado','cancelado') NOT NULL DEFAULT 'completado',
  `fecha_venta` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `pedido_id` (`pedido_id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ventas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `ventas` (`id`, `pedido_id`, `subtotal`, `impuestos`, `descuento`, `total`, `monto_pagado`, `cambio`, `ganancias`, `usuario_id`, `estado`, `fecha_venta`) VALUES
(1, 1, 50.00, 0.00, 0.00, 50.00, 50.00, 0.00, 32.50, 1, 'completado', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(2, NULL, 32.00, 0.00, 2.00, 30.00, 30.00, 0.00, 18.20, 2, 'completado', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(3, NULL, 25.00, 0.00, 0.00, 25.00, 30.00, 5.00, 16.25, 1, 'completado', NOW());

-- --------------------------------------------------------
-- 15. Estructura de la tabla `detalle_venta`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `detalle_venta`;
CREATE TABLE `detalle_venta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `venta_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `descuento` decimal(10,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `venta_id` (`venta_id`),
  KEY `producto_id` (`producto_id`),
  CONSTRAINT `detalle_venta_ibfk_1` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detalle_venta_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `detalle_venta` (`id`, `venta_id`, `producto_id`, `cantidad`, `precio_unitario`, `descuento`, `subtotal`) VALUES
(1, 1, 1, 2, 25.00, 0.00, 50.00),
(2, 2, 2, 4, 3.50, 0.00, 14.00),
(3, 2, 5, 1, 18.00, 2.00, 16.00),
(4, 3, 1, 1, 25.00, 0.00, 25.00);

-- --------------------------------------------------------
-- 16. Estructura de la tabla `pagos`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `pagos`;
CREATE TABLE `pagos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `venta_id` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL DEFAULT 0.00,
  `metodo_pago` enum('efectivo','tarjeta','transferencia','wallet') NOT NULL,
  `estado` enum('pendiente','completado','fallido') DEFAULT 'completado',
  `referencia` varchar(100) DEFAULT NULL,
  `fecha_pago` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `venta_id` (`venta_id`),
  CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `pagos` (`id`, `venta_id`, `monto`, `metodo_pago`, `estado`, `referencia`) VALUES
(1, 1, 50.00, 'transferencia', 'completado', 'TRX-998811'),
(2, 2, 30.00, 'tarjeta', 'completado', 'POS-4421'),
(3, 3, 25.00, 'efectivo', 'completado', 'EF-CASH');

-- --------------------------------------------------------
-- 17. Estructura de la tabla `gastos`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `gastos`;
CREATE TABLE `gastos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(255) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `eliminado` boolean NOT NULL DEFAULT false,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_gastos_elim` (`eliminado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `gastos` (`id`, `descripcion`, `monto`, `fecha`, `eliminado`, `deleted_at`) VALUES
(1, 'Pago de servicio eléctrico local', 85.50, DATE_SUB(NOW(), INTERVAL 15 DAY), false, NULL),
(2, 'Mantenimiento preventivo de horno pastelero', 120.00, DATE_SUB(NOW(), INTERVAL 7 DAY), false, NULL),
(3, 'Compra de bolsas y empaques biodegradables', 45.00, NOW(), false, NULL);

-- --------------------------------------------------------
-- 18. Estructura de la tabla `bitacora_sistema`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `bitacora_sistema`;
CREATE TABLE `bitacora_sistema` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `modulo` varchar(100) NOT NULL,
  `accion` varchar(255) NOT NULL,
  `detalles` text DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `fecha_hora` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `bitacora_sistema_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `bitacora_sistema` (`id`, `usuario_id`, `modulo`, `accion`, `detalles`, `ip_address`, `fecha_hora`) VALUES
(1, 1, 'Seguridad', 'Inicio de sesión', 'Usuario logueado exitosamente: admin@lavicky.com', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(2, 1, 'Inventario', 'Ajuste de stock', 'Incremento de stock en insumo Harina', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 45 MINUTE)),
(3, 2, 'Ventas', 'Registro de venta directa', 'Venta #3 registrada por $25.00', '127.0.0.1', NOW());

-- --------------------------------------------------------
-- 19. Estructura de la tabla `auditoria_cambios`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `auditoria_cambios`;
CREATE TABLE `auditoria_cambios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tabla_afectada` varchar(100) NOT NULL,
  `registro_id` int(11) NOT NULL,
  `accion` enum('INSERT','UPDATE','DELETE') NOT NULL,
  `valores_anteriores` longtext DEFAULT NULL,
  `valores_nuevos` longtext DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `fecha_hora` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `auditoria_cambios_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `auditoria_cambios` (`id`, `tabla_afectada`, `registro_id`, `accion`, `valores_anteriores`, `valores_nuevos`, `usuario_id`, `fecha_hora`) VALUES
(1, 'insumos', 1, 'UPDATE', '{"stock_actual": 100.00}', '{"stock_actual": 150.00}', 1, DATE_SUB(NOW(), INTERVAL 45 MINUTE)),
(2, 'productos', 3, 'UPDATE', '{"stock_actual": 5}', '{"stock_actual": 3}', 2, NOW());

-- --------------------------------------------------------
-- 20. Estructura de la tabla `alertas_sistema`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `alertas_sistema`;
CREATE TABLE `alertas_sistema` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo_alerta` varchar(50) NOT NULL,
  `modulo` varchar(100) NOT NULL,
  `mensaje` text NOT NULL,
  `estado` enum('activa','resuelta') DEFAULT 'activa',
  `leida` tinyint(1) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_resolucion` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `alertas_sistema` (`id`, `tipo_alerta`, `modulo`, `mensaje`, `estado`, `leida`, `fecha_creacion`) VALUES
(1, 'Stock Bajo', 'Inventario', 'El insumo Levadura en Polvo ha alcanzado su stock mínimo.', 'activa', 0, NOW()),
(2, 'Stock Bajo', 'Productos', 'El producto Pan de Queso Especial tiene stock por debajo del límite.', 'activa', 0, NOW());

-- --------------------------------------------------------
-- 21. Estructura de la tabla `incidencias`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `incidencias`;
CREATE TABLE `incidencias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `modulo` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `estado` enum('abierta','en_progreso','resuelta') DEFAULT 'abierta',
  `usuario_reporta` int(11) DEFAULT NULL,
  `fecha_reporte` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_resolucion` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_reporta` (`usuario_reporta`),
  CONSTRAINT `incidencias_ibfk_1` FOREIGN KEY (`usuario_reporta`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `incidencias` (`id`, `modulo`, `descripcion`, `estado`, `usuario_reporta`, `fecha_reporte`) VALUES
(1, 'Impresión', 'La impresora térmica desalinea los tickets de venta', 'abierta', 2, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(2, 'Inventario', 'Insumo cacao presentó empaque dañado al recibir', 'resuelta', 1, DATE_SUB(NOW(), INTERVAL 5 DAY));

-- --------------------------------------------------------
-- 22. Estructura de la tabla `accesos_denegados`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `accesos_denegados`;
CREATE TABLE `accesos_denegados` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `modulo_intentado` varchar(100) NOT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `fecha_hora` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `accesos_denegados_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `accesos_denegados` (`id`, `usuario_id`, `modulo_intentado`, `ip_address`, `fecha_hora`) VALUES
(1, 2, 'bitacora', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 3 HOUR));

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;
