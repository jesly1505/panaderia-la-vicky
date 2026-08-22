-- =====================================================
-- Migración 002: Agregar tipo_pago a ventas,
--                  categoria a gastos,
--                  crear tabla catalogos
-- =====================================================

-- 1. Agregar tipo_pago a ventas
ALTER TABLE ventas
    ADD COLUMN tipo_pago varchar(30) DEFAULT 'efectivo' AFTER cambio;

-- 2. Agregar categoria a gastos
ALTER TABLE gastos
    ADD COLUMN categoria varchar(100) DEFAULT 'General' AFTER fecha;

-- 3. Tabla genérica de catálogos
CREATE TABLE IF NOT EXISTS catalogos (
    id          int NOT NULL AUTO_INCREMENT,
    tipo        varchar(50)  NOT NULL COMMENT 'categoría del catálogo (ej: gastos, pagos)',
    valor       varchar(100) NOT NULL COMMENT 'valor interno / código',
    etiqueta    varchar(150) NOT NULL COMMENT 'texto visible en UI',
    estado      tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=activo 0=inactivo',
    eliminado   tinyint(1) NOT NULL DEFAULT 0,
    deleted_at  datetime DEFAULT NULL,
    creado_en   timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_catalogo_tipo_valor (tipo, valor),
    KEY idx_cat_tipo (tipo),
    KEY idx_cat_elim (eliminado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Semilla: categorías de gastos
INSERT INTO catalogos (tipo, valor, etiqueta, estado) VALUES
('gastos', 'servicios',       'Servicios (Agua, Luz, Gas)', 1),
('gastos', 'mantenimiento',   'Mantenimiento / Equipos',    1),
('gastos', 'limpieza',        'Limpieza',                   1),
('gastos', 'combustible',     'Combustible',                1),
('gastos', 'transporte',      'Transporte / Flete',         1),
('gastos', 'personal',        'Personal / Nómina',          1),
('gastos', 'papeleria',       'Papelería y Útiles',         1),
('gastos', 'impuestos',       'Impuestos y Tasas',          1),
('gastos', 'publicidad',      'Publicidad y Marketing',     1),
('gastos', 'otros',           'Otros',                      1);

-- 5. Migrar gastos existentes a categorías del catálogo
UPDATE gastos SET categoria = 'otros' WHERE categoria IS NULL OR categoria = '';

-- 6. Migrar ventas existentes: inferir tipo_pago del primer pago asociado
UPDATE ventas v
    JOIN pagos pg ON pg.venta_id = v.id
    SET v.tipo_pago = pg.metodo_pago
    WHERE v.tipo_pago IS NULL OR v.tipo_pago = '';

UPDATE ventas SET tipo_pago = 'efectivo' WHERE tipo_pago IS NULL OR tipo_pago = '';
