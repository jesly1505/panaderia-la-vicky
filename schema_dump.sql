-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: localhost    Database: la_vicky_db
-- ------------------------------------------------------
-- Server version	8.0.46

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `accesos_denegados`
--

DROP TABLE IF EXISTS `accesos_denegados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `accesos_denegados` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int DEFAULT NULL,
  `modulo_intentado` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ip_address` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fecha_hora` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `accesos_denegados_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accesos_denegados`
--

LOCK TABLES `accesos_denegados` WRITE;
/*!40000 ALTER TABLE `accesos_denegados` DISABLE KEYS */;
INSERT INTO `accesos_denegados` VALUES (1,2,'bitacora','127.0.0.1','2026-08-12 10:28:43');
/*!40000 ALTER TABLE `accesos_denegados` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `alertas_sistema`
--

DROP TABLE IF EXISTS `alertas_sistema`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `alertas_sistema` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipo_alerta` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `modulo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `mensaje` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `estado` enum('activa','resuelta') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'activa',
  `leida` tinyint(1) DEFAULT '0',
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_resolucion` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alertas_sistema`
--

LOCK TABLES `alertas_sistema` WRITE;
/*!40000 ALTER TABLE `alertas_sistema` DISABLE KEYS */;
INSERT INTO `alertas_sistema` VALUES (1,'Stock Bajo','Inventario','El insumo Levadura en Polvo ha alcanzado su stock mínimo.','activa',0,'2026-08-12 13:28:43',NULL),(2,'Stock Bajo','Productos','El producto Pan de Queso Especial tiene stock por debajo del límite.','activa',0,'2026-08-12 13:28:43',NULL);
/*!40000 ALTER TABLE `alertas_sistema` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auditoria_cambios`
--

DROP TABLE IF EXISTS `auditoria_cambios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auditoria_cambios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tabla_afectada` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `registro_id` int NOT NULL,
  `accion` enum('INSERT','UPDATE','DELETE') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `valores_anteriores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `valores_nuevos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `usuario_id` int DEFAULT NULL,
  `fecha_hora` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `auditoria_cambios_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auditoria_cambios`
--

LOCK TABLES `auditoria_cambios` WRITE;
/*!40000 ALTER TABLE `auditoria_cambios` DISABLE KEYS */;
INSERT INTO `auditoria_cambios` VALUES (1,'insumos',1,'UPDATE','{\"stock_actual\": 100.00}','{\"stock_actual\": 150.00}',1,'2026-08-12 12:43:43'),(2,'productos',3,'UPDATE','{\"stock_actual\": 5}','{\"stock_actual\": 3}',2,'2026-08-12 13:28:43');
/*!40000 ALTER TABLE `auditoria_cambios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bitacora_sistema`
--

DROP TABLE IF EXISTS `bitacora_sistema`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bitacora_sistema` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int DEFAULT NULL,
  `modulo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `accion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `detalles` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `ip_address` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fecha_hora` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `bitacora_sistema_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bitacora_sistema`
--

LOCK TABLES `bitacora_sistema` WRITE;
/*!40000 ALTER TABLE `bitacora_sistema` DISABLE KEYS */;
INSERT INTO `bitacora_sistema` VALUES (1,1,'Seguridad','Inicio de sesión','Usuario logueado exitosamente: admin@lavicky.com','127.0.0.1','2026-08-12 12:28:42'),(2,1,'Inventario','Ajuste de stock','Incremento de stock en insumo Harina','127.0.0.1','2026-08-12 12:43:42'),(3,2,'Ventas','Registro de venta directa','Venta #3 registrada por $25.00','127.0.0.1','2026-08-12 13:28:42');
/*!40000 ALTER TABLE `bitacora_sistema` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clientes`
--

DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clientes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telefono` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `direccion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `puntos_fidelidad` int DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clientes`
--

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
INSERT INTO `clientes` VALUES (1,'María Elena López','maria.lopez@example.com','555-1029','Av. Central #45, San José',120,'2026-08-12 13:28:42'),(2,'Juan Carlos Pérez','juan.perez@example.com','555-8832','Calle Los Olivos #12',45,'2026-08-12 13:28:42'),(3,'Sofía Ramírez','sofia.ramirez@example.com','555-9921','Residencial Las Flores B-4',200,'2026-08-12 13:28:42'),(4,'Cliente General POS','pos@lavicky.com','N/A','Venta en Mostrador',0,'2026-08-12 13:28:42');
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `compras_insumos`
--

DROP TABLE IF EXISTS `compras_insumos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `compras_insumos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `insumo_id` int NOT NULL,
  `proveedor_id` int DEFAULT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `precio_compra` decimal(10,2) NOT NULL,
  `fecha_compra` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `insumo_id` (`insumo_id`),
  KEY `proveedor_id` (`proveedor_id`),
  CONSTRAINT `compras_insumos_ibfk_1` FOREIGN KEY (`insumo_id`) REFERENCES `insumos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `compras_insumos_ibfk_2` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `compras_insumos`
--

LOCK TABLES `compras_insumos` WRITE;
/*!40000 ALTER TABLE `compras_insumos` DISABLE KEYS */;
INSERT INTO `compras_insumos` VALUES (1,1,1,100.00,1.20,'2026-08-02 13:28:41'),(2,2,2,30.00,4.50,'2026-08-04 13:28:41'),(3,4,1,50.00,1.10,'2026-08-07 13:28:41'),(4,5,3,10.00,8.50,'2026-08-10 13:28:41');
/*!40000 ALTER TABLE `compras_insumos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_pedido`
--

DROP TABLE IF EXISTS `detalle_pedido`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_pedido` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pedido_id` int NOT NULL,
  `producto_id` int NOT NULL,
  `cantidad` int NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_id` (`pedido_id`),
  KEY `producto_id` (`producto_id`),
  CONSTRAINT `detalle_pedido_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detalle_pedido_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_pedido`
--

LOCK TABLES `detalle_pedido` WRITE;
/*!40000 ALTER TABLE `detalle_pedido` DISABLE KEYS */;
INSERT INTO `detalle_pedido` VALUES (1,1,1,2,25.00,50.00),(2,2,2,3,3.50,10.50),(3,2,5,1,18.00,18.00),(4,3,1,2,25.00,50.00),(5,3,4,2,5.00,10.00);
/*!40000 ALTER TABLE `detalle_pedido` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_venta`
--

DROP TABLE IF EXISTS `detalle_venta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_venta` (
  `id` int NOT NULL AUTO_INCREMENT,
  `venta_id` int NOT NULL,
  `producto_id` int NOT NULL,
  `cantidad` int NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `descuento` decimal(10,2) NOT NULL DEFAULT '0.00',
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `venta_id` (`venta_id`),
  KEY `producto_id` (`producto_id`),
  CONSTRAINT `detalle_venta_ibfk_1` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detalle_venta_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_venta`
--

LOCK TABLES `detalle_venta` WRITE;
/*!40000 ALTER TABLE `detalle_venta` DISABLE KEYS */;
INSERT INTO `detalle_venta` VALUES (1,1,1,2,25.00,0.00,50.00),(2,2,2,4,3.50,0.00,14.00),(3,2,5,1,18.00,2.00,16.00),(4,3,1,1,25.00,0.00,25.00);
/*!40000 ALTER TABLE `detalle_venta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `empleados_rrhh`
--

DROP TABLE IF EXISTS `empleados_rrhh`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empleados_rrhh` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `salario` decimal(10,2) NOT NULL,
  `turno` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fecha_ingreso` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `empleados_rrhh_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `empleados_rrhh`
--

LOCK TABLES `empleados_rrhh` WRITE;
/*!40000 ALTER TABLE `empleados_rrhh` DISABLE KEYS */;
INSERT INTO `empleados_rrhh` VALUES (1,1,1200.00,'Matutino','2025-01-15'),(2,2,600.00,'Vespertino','2025-02-01');
/*!40000 ALTER TABLE `empleados_rrhh` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gastos`
--

DROP TABLE IF EXISTS `gastos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gastos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gastos`
--

LOCK TABLES `gastos` WRITE;
/*!40000 ALTER TABLE `gastos` DISABLE KEYS */;
INSERT INTO `gastos` VALUES (1,'Pago de servicio eléctrico local',85.50,'2026-07-28 13:28:42'),(2,'Mantenimiento preventivo de horno pastelero',120.00,'2026-08-05 13:28:42'),(3,'Compra de bolsas y empaques biodegradables',45.00,'2026-08-12 13:28:42');
/*!40000 ALTER TABLE `gastos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `incidencias`
--

DROP TABLE IF EXISTS `incidencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `incidencias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `modulo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `estado` enum('abierta','en_progreso','resuelta') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'abierta',
  `usuario_reporta` int DEFAULT NULL,
  `fecha_reporte` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_resolucion` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_reporta` (`usuario_reporta`),
  CONSTRAINT `incidencias_ibfk_1` FOREIGN KEY (`usuario_reporta`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `incidencias`
--

LOCK TABLES `incidencias` WRITE;
/*!40000 ALTER TABLE `incidencias` DISABLE KEYS */;
INSERT INTO `incidencias` VALUES (1,'Impresión','La impresora térmica desalinea los tickets de venta','abierta',2,'2026-08-10 13:28:43',NULL),(2,'Inventario','Insumo cacao presentó empaque dañado al recibir','resuelta',1,'2026-08-07 13:28:43',NULL);
/*!40000 ALTER TABLE `incidencias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `insumos`
--

DROP TABLE IF EXISTS `insumos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `insumos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `proveedor_id` int DEFAULT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `unidad_medida` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `stock_actual` decimal(10,2) DEFAULT '0.00',
  `stock_minimo` decimal(10,2) DEFAULT '0.00',
  `precio_costo` decimal(10,2) NOT NULL,
  `visible` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `proveedor_id` (`proveedor_id`),
  CONSTRAINT `insumos_ibfk_1` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `insumos`
--

LOCK TABLES `insumos` WRITE;
/*!40000 ALTER TABLE `insumos` DISABLE KEYS */;
INSERT INTO `insumos` VALUES (1,1,'Harina de Trigo Integral','Kg',150.00,20.00,1.20,1),(2,2,'Mantequilla Sin Sal','Kg',40.00,5.00,4.50,1),(3,2,'Leche Entera','Litros',80.00,15.00,0.90,1),(4,1,'Azúcar Refinada','Kg',100.00,10.00,1.10,1),(5,3,'Cacao en Polvo Premium','Kg',12.00,5.00,8.50,1),(6,2,'Huevos Frescos','Unidades',300.00,50.00,0.15,1),(7,3,'Esencia de Vainilla','Litros',2.00,1.00,12.00,1),(8,1,'Levadura en Polvo','Kg',3.00,5.00,3.20,1);
/*!40000 ALTER TABLE `insumos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pagos`
--

DROP TABLE IF EXISTS `pagos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pagos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `venta_id` int NOT NULL,
  `monto` decimal(10,2) NOT NULL DEFAULT '0.00',
  `metodo_pago` enum('efectivo','tarjeta','transferencia','wallet') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `estado` enum('pendiente','completado','fallido') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'completado',
  `referencia` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fecha_pago` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `venta_id` (`venta_id`),
  CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pagos`
--

LOCK TABLES `pagos` WRITE;
/*!40000 ALTER TABLE `pagos` DISABLE KEYS */;
INSERT INTO `pagos` VALUES (1,1,50.00,'transferencia','completado','TRX-998811','2026-08-12 13:28:42'),(2,2,30.00,'tarjeta','completado','POS-4421','2026-08-12 13:28:42'),(3,3,25.00,'efectivo','completado','EF-CASH','2026-08-12 13:28:42');
/*!40000 ALTER TABLE `pagos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pedidos`
--

DROP TABLE IF EXISTS `pedidos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pedidos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cliente_id` int DEFAULT NULL,
  `usuario_id` int NOT NULL,
  `estado` enum('pendiente','en_proceso','entregado','cancelado') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'pendiente',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `fecha_pedido` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_entrega` date DEFAULT NULL,
  `hora_entrega` time DEFAULT NULL,
  `hora_entrega_real` time DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cliente_id` (`cliente_id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pedidos_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pedidos`
--

LOCK TABLES `pedidos` WRITE;
/*!40000 ALTER TABLE `pedidos` DISABLE KEYS */;
INSERT INTO `pedidos` VALUES (1,1,1,'entregado',50.00,'2026-08-08 13:28:42','2026-08-12','10:00:00','09:55:00'),(2,2,2,'pendiente',28.50,'2026-08-12 13:28:42','2026-08-12','16:00:00',NULL),(3,3,1,'en_proceso',60.00,'2026-08-12 13:28:42','2026-08-12','18:30:00',NULL);
/*!40000 ALTER TABLE `pedidos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `produccion_detalle`
--

DROP TABLE IF EXISTS `produccion_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `produccion_detalle` (
  `id` int NOT NULL AUTO_INCREMENT,
  `produccion_id` int NOT NULL,
  `insumo_id` int NOT NULL,
  `cantidad_usada` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `produccion_id` (`produccion_id`),
  KEY `insumo_id` (`insumo_id`),
  CONSTRAINT `produccion_detalle_ibfk_1` FOREIGN KEY (`produccion_id`) REFERENCES `producciones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `produccion_detalle_ibfk_2` FOREIGN KEY (`insumo_id`) REFERENCES `insumos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produccion_detalle`
--

LOCK TABLES `produccion_detalle` WRITE;
/*!40000 ALTER TABLE `produccion_detalle` DISABLE KEYS */;
INSERT INTO `produccion_detalle` VALUES (1,1,1,5.00),(2,1,2,2.00),(3,1,4,3.00),(4,1,5,2.50),(5,2,1,7.50),(6,2,2,2.50),(7,3,1,6.00),(8,3,4,4.50);
/*!40000 ALTER TABLE `produccion_detalle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `producciones`
--

DROP TABLE IF EXISTS `producciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `producciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `producto_id` int NOT NULL,
  `cantidad_producida` decimal(10,2) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `producto_id` (`producto_id`),
  CONSTRAINT `producciones_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `producciones`
--

LOCK TABLES `producciones` WRITE;
/*!40000 ALTER TABLE `producciones` DISABLE KEYS */;
INSERT INTO `producciones` VALUES (1,1,10.00,'2026-08-09 13:28:42'),(2,2,50.00,'2026-08-10 13:28:42'),(3,4,30.00,'2026-08-11 13:28:42');
/*!40000 ALTER TABLE `producciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `producto_receta`
--

DROP TABLE IF EXISTS `producto_receta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `producto_receta` (
  `id` int NOT NULL AUTO_INCREMENT,
  `producto_id` int NOT NULL,
  `insumo_id` int NOT NULL,
  `cantidad_requerida` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `producto_id` (`producto_id`),
  KEY `insumo_id` (`insumo_id`),
  CONSTRAINT `producto_receta_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `producto_receta_ibfk_2` FOREIGN KEY (`insumo_id`) REFERENCES `insumos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `producto_receta`
--

LOCK TABLES `producto_receta` WRITE;
/*!40000 ALTER TABLE `producto_receta` DISABLE KEYS */;
INSERT INTO `producto_receta` VALUES (1,1,1,0.50),(2,1,2,0.20),(3,1,4,0.30),(4,1,5,0.25),(5,1,6,4.00),(6,1,7,0.02),(7,2,1,0.15),(8,2,2,0.05),(9,2,6,1.00),(10,3,1,0.10),(11,3,3,0.10),(12,3,8,0.01),(13,4,1,0.20),(14,4,2,0.10),(15,4,4,0.15);
/*!40000 ALTER TABLE `producto_receta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productos`
--

DROP TABLE IF EXISTS `productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `productos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `precio_venta` decimal(10,2) NOT NULL,
  `costo_produccion` decimal(10,2) NOT NULL DEFAULT '0.00',
  `stock_actual` int DEFAULT '0',
  `stock_minimo` int DEFAULT '0',
  `categoria` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos`
--

LOCK TABLES `productos` WRITE;
/*!40000 ALTER TABLE `productos` DISABLE KEYS */;
INSERT INTO `productos` VALUES (1,'Pastel Artesanal de Chocolate','Delicioso pastel esponjoso elaborado con cacao fino',25.00,8.75,12,5,'Postres','2026-08-12 13:28:41'),(2,'Empanada Rellena de Pollo','Empanada crujiente dorada al horno con relleno artesanal',3.50,1.10,45,10,'Bocadillos','2026-08-12 13:28:41'),(3,'Pan de Queso Especial','Pan suave con queso horneado al momento',2.00,0.65,3,10,'Panadería','2026-08-12 13:28:41'),(4,'Galletas Caseras de Avena','Caja de 6 galletas saludables de avainillado y avena',5.00,1.80,25,5,'Galletas','2026-08-12 13:28:41'),(5,'Tarta de Vainilla y Frutas','Tarta fría rellena de crema pastelera y esencia de vainilla',18.00,5.50,8,3,'Postres','2026-08-12 13:28:41');
/*!40000 ALTER TABLE `productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proveedores`
--

DROP TABLE IF EXISTS `proveedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `proveedores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `contacto` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telefono` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proveedores`
--

LOCK TABLES `proveedores` WRITE;
/*!40000 ALTER TABLE `proveedores` DISABLE KEYS */;
INSERT INTO `proveedores` VALUES (1,'Distribuidora Molinos del Sur','Roberto Gómez','555-0192','ventas@molinosdelsur.com'),(2,'Lácteos y Harinas La Granja','Ana Martínez','555-0384','contacto@lagranja.com'),(3,'Empaques y Dulces Victoria','Luis Fernández','555-0771','pedidos@empaquesvictoria.com');
/*!40000 ALTER TABLE `proveedores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Administrador','Acceso total y control administrativo del sistema'),(2,'Cajero','Acceso operativo a ventas, pedidos y productos'),(3,'Panadero','Acceso a inventario de materias primas, recetas y producción de lotes');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `rol_id` int NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `estado` enum('activo','inactivo') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'activo',
  `intentos_fallidos` int DEFAULT '0',
  `bloqueado_hasta` datetime DEFAULT NULL,
  `ultimo_acceso` datetime DEFAULT NULL,
  `reset_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reset_token_expira` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `rol_id` (`rol_id`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,1,'Administrador General','admin@lavicky.com','$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C','activo',0,NULL,'2026-08-12 13:28:41',NULL,NULL,'2026-08-12 13:28:41'),(2,2,'Carlos Vendedor','cajero@lavicky.com','$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C','activo',0,NULL,'2026-08-12 13:28:41',NULL,NULL,'2026-08-12 13:28:41'),(3,3,'Panadero de Prueba (Dev)','panadero.test@lavicky.com','$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C','activo',0,NULL,'2026-08-12 13:28:41',NULL,NULL,'2026-08-12 13:28:41');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ventas`
--

DROP TABLE IF EXISTS `ventas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ventas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pedido_id` int DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `impuestos` decimal(10,2) NOT NULL DEFAULT '0.00',
  `descuento` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL,
  `monto_pagado` decimal(10,2) DEFAULT NULL,
  `cambio` decimal(10,2) DEFAULT NULL,
  `ganancias` decimal(10,2) NOT NULL DEFAULT '0.00',
  `usuario_id` int DEFAULT NULL,
  `estado` enum('completado','cancelado') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'completado',
  `fecha_venta` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pedido_id` (`pedido_id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ventas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ventas`
--

LOCK TABLES `ventas` WRITE;
/*!40000 ALTER TABLE `ventas` DISABLE KEYS */;
INSERT INTO `ventas` VALUES (1,1,50.00,0.00,0.00,50.00,50.00,0.00,32.50,1,'completado','2026-08-08 13:28:42'),(2,NULL,32.00,0.00,2.00,30.00,30.00,0.00,18.20,2,'completado','2026-08-11 13:28:42'),(3,NULL,25.00,0.00,0.00,25.00,30.00,5.00,16.25,1,'completado','2026-08-12 13:28:42');
/*!40000 ALTER TABLE `ventas` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-14  9:33:03
