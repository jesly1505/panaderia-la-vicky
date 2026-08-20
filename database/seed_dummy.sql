-- =============================================================
-- Seed de datos dummy para "Panadería La Vicky"
-- 50 registros por tabla principal (excepto roles/permisos).
-- Contraseña de todos los usuarios: admin123
-- =============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =============================================================
-- PROVEEDORES (20 registros)
-- =============================================================
INSERT INTO proveedores (id, nombre, contacto, telefono, email, eliminado) VALUES
(1,  'Harinas El Molino',     'Roberto García',    '0412-5551001', 'harinaselmolino@email.com',   0),
(2,  'Lácteos del Valle',     'María López',       '0414-5551002', 'lacteosdelvalle@email.com',    0),
(3,  'Aves Don Pedro',        'Pedro Ramírez',     '0416-5551003', 'avesdonpedro@email.com',       0),
(4,  'Azúcar Santa Ana',      'Ana Martínez',      '0412-5551004', 'azucarsantana@email.com',      0),
(5,  'Mantequillas Premium',  'Carlos Ruiz',       '0424-5551005', 'mantequillaspremium@email.com',0),
(6,  'Frutas Frescas S.A.',   'Luis Hernández',    '0412-5551006', 'frutasfrescas@email.com',      0),
(7,  'Chocolate Exquisito',   'Fernando Torres',   '0414-5551007', 'chocolateexquisito@email.com', 0),
(8,  'Empaques Industriales', 'Sandra Mendoza',    '0416-5551008', 'empaquesind@email.com',        0),
(9,  'Especias Orientales',   'Ahmed Hassan',      '0412-5551009', 'especiasorient@email.com',     0),
(10, 'Levaduras Pro',         'Jorge Blanco',      '0424-5551010', 'levaduraspro@email.com',       0),
(11, 'Carnes Selectas',       'Miguel Ángel Díaz', '0412-5551011', 'carnesselectas@email.com',     0),
(12, 'Granos del Sur',        'Patricia Vargas',   '0414-5551012', 'granosdelsur@email.com',       0),
(13, 'Aceites Vegetales',     'Roberto Solano',    '0416-5551013', 'aceitesvegetales@email.com',   0),
(14, 'Emulsificantes Master', 'Diego Castillo',    '0412-5551014', 'emulsificantesmaster@email.com',0),
(15, 'Frigorífico Andes',     'Carmen Rivas',      '0424-5551015', 'frigorificoandes@email.com',   0),
(16, 'Cereales Integrales',   'Andrés Peña',       '0414-5551016', 'cerealesintegrales@email.com', 0),
(17, 'Miel Pura Natural',     'Elena Rojas',       '0412-5551017', 'mielpuranatural@email.com',    0),
(18, 'Vainilla Extract',      'Hugo Morales',      '0416-5551018', 'vainillaextract@email.com',    0),
(19, 'Sal Fina Industrial',   'Lucía Campos',      '0424-5551019', 'salfinaindustrial@email.com',  0),
(20, 'Conservantes Químicos', 'Raúl Paredes',      '0412-5551020', 'conservantesquim@email.com',    0)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- =============================================================
-- INSUMOS (50 registros)
-- =============================================================
INSERT INTO insumos (id, proveedor_id, nombre, unidad_medida, stock_actual, stock_minimo, precio_costo, visible, eliminado) VALUES
(1,  1,  'Harina de Trigo Premium',   'Kg',     150.00,  30.00,  1.20, 1, 0),
(2,  1,  'Harina de Trigo Integral',   'Kg',      80.00,  20.00,  1.50, 1, 0),
(3,  1,  'Harina de Centeno',          'Kg',      40.00,  10.00,  2.00, 1, 0),
(4,  2,  'Leche Entera',               'Litros', 200.00,  50.00,  0.80, 1, 0),
(5,  2,  'Leche Descremada',           'Litros', 100.00,  30.00,  0.90, 1, 0),
(6,  2,  'Mantequilla sin Sal',        'Kg',      60.00,  15.00,  4.50, 1, 0),
(7,  2,  'Crema de Leche',             'Litros',  40.00,  10.00,  3.20, 1, 0),
(8,  2,  'Queso Crema',                'Kg',      30.00,   8.00,  5.00, 1, 0),
(9,  3,  'Huevos de Gallina',          'Unidades',500.00, 100.00,  0.15, 1, 0),
(10, 3,  'Yema de Huevo',              'Kg',      10.00,   3.00,  8.00, 1, 0),
(11, 4,  'Azúcar Blanca Refinada',     'Kg',     120.00,  25.00,  0.60, 1, 0),
(12, 4,  'Azúcar Morena',              'Kg',      50.00,  15.00,  0.70, 1, 0),
(13, 4,  'Azúcar Glás',                'Kg',      20.00,   5.00,  1.80, 1, 0),
(14, 5,  'Mantequilla con Sal',        'Kg',      45.00,  10.00,  4.80, 1, 0),
(15, 5,  'Margarina Vegetal',          'Kg',      35.00,  10.00,  2.50, 1, 0),
(16, 6,  'Manzanas Rojas',             'Kg',      25.00,   5.00,  2.00, 1, 0),
(17, 6,  'Plátanos',                   'Kg',      30.00,   8.00,  1.00, 1, 0),
(18, 6,  'Fresas Frescas',             'Kg',      15.00,   5.00,  4.00, 1, 0),
(19, 6,  'Limones',                    'Kg',      10.00,   3.00,  1.50, 1, 0),
(20, 7,  'Chocolate Negro 70%',        'Kg',      20.00,   5.00,  8.00, 1, 0),
(21, 7,  'Chocolate Blanco',           'Kg',      15.00,   4.00,  7.50, 1, 0),
(22, 7,  'Cacao en Polvo',             'Kg',      12.00,   3.00,  6.00, 1, 0),
(23, 7,  'Chispas de Chocolate',       'Kg',      18.00,   5.00,  9.00, 1, 0),
(24, 9,  'Canela Molida',              'Gramos',  500.00, 100.00, 0.02, 1, 0),
(25, 9,  'Vainilla en Polvo',          'Gramos',  300.00,  50.00, 0.05, 1, 0),
(26, 9,  'Nuez Moscada',               'Gramos',  200.00,  50.00, 0.08, 1, 0),
(27, 10, 'Levadura Fresca',            'Kg',       25.00,  10.00,  3.00, 1, 0),
(28, 10, 'Levadura Seca Instantánea',  'Kg',       10.00,   3.00,  6.00, 1, 0),
(29, 12, 'Avena Integral',             'Kg',       40.00,  10.00,  1.80, 1, 0),
(30, 12, 'Semillas de Girasol',        'Kg',       15.00,   5.00,  3.00, 1, 0),
(31, 12, 'Semillas de Chía',           'Kg',        8.00,   2.00,  5.00, 1, 0),
(32, 13, 'Aceite de Girasol',          'Litros',  50.00,  15.00,  1.50, 1, 0),
(33, 13, 'Aceite de Oliva',            'Litros',  10.00,   3.00,  8.00, 1, 0),
(34, 14, 'Emulsificante Lecitina',     'Gramos', 1000.00, 200.00, 0.01, 1, 0),
(35, 14, 'Estabilizante Mono',         'Gramos',  500.00, 100.00, 0.03, 1, 0),
(36, 16, 'Harina de Avena',            'Kg',       30.00,   8.00,  2.20, 1, 0),
(37, 17, 'Miel de Abeja Natural',      'Litros',   12.00,   3.00, 12.00, 1, 0),
(38, 18, 'Extracto de Vainilla',       'Litros',    5.00,   1.00, 15.00, 1, 0),
(39, 19, 'Sal Fina',                   'Kg',       20.00,   5.00,  0.40, 1, 0),
(40, 19, 'Sal Gruesa',                 'Kg',       10.00,   3.00,  0.35, 1, 0),
(41, 8,  'Bolsas de Papel 1kg',        'Unidades', 500.00, 100.00, 0.05, 1, 0),
(42, 8,  'Bandejas de Cartón',         'Unidades', 300.00,  80.00, 0.10, 1, 0),
(43, 8,  'Film Transparente',          'Metros',  200.00,  50.00, 0.03, 1, 0),
(44, 11, 'Pechuga de Pollo',           'Kg',       20.00,   5.00,  4.00, 1, 0),
(45, 11, 'Jamón Cocido',               'Kg',       15.00,   4.00,  5.50, 1, 0),
(46, 11, 'Queso Mozzarella',           'Kg',       18.00,   5.00,  6.00, 1, 0),
(47, 20, 'Conservante Sorbato',        'Gramos',  500.00, 100.00, 0.04, 1, 0),
(48, 20, 'Ácido Cítrico',              'Gramos',  300.00,  80.00, 0.06, 1, 0),
(49, 1,  'Maicena',                    'Kg',       25.00,   8.00,  1.00, 1, 0),
(50, 1,  'Polvo de Hornear',           'Kg',        8.00,   2.00,  4.00, 1, 0)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- =============================================================
-- PRODUCTOS (50 registros)
-- =============================================================
INSERT INTO productos (id, nombre, descripcion, precio_venta, costo_produccion, stock_actual, stock_minimo, categoria, eliminado) VALUES
(1,  'Pan Francés',              'Pan suave y crujiente artesanal',           0.25,  0.10, 200, 50, 'Pan Salado',    0),
(2,  'Pan Integral',             'Pan integral con semillas',                 0.40,  0.18, 120, 30, 'Pan Salado',    0),
(3,  'Pan de Hamburguesa',       'Pan suave para hamburguesa',                0.35,  0.12, 150, 40, 'Pan Salado',    0),
(4,  'Medialuna de Manteca',     'Medialuna hojaldrada clásica',              0.50,  0.22,  80, 20, 'Pan Dulce',     0),
(5,  'Medialuna de Grasa',       'Medialuna crujiente tradicional',           0.45,  0.20,  90, 25, 'Pan Dulce',     0),
(6,  'Croissant de Jamón y Queso','Croissant relleno de jamón y queso',       1.20,  0.55,  40, 15, 'Pan Dulce',     0),
(7,  'Factura Simple',           'Factura con crema pastelera',               0.60,  0.28,  60, 20, 'Pan Dulce',     0),
(8,  'Factura de Crema',         'Factura rellena de crema',                  0.70,  0.32,  50, 15, 'Pan Dulce',     0),
(9,  'Sosa',                     'Pan dulce bañado en chocolate',             0.55,  0.25,  70, 20, 'Pan Dulce',     0),
(10, 'Chipá',                    'Pan de yuca rallado',                       0.30,  0.12, 100, 30, 'Pan Dulce',     0),
(11, 'Rosca de Queso',           'Rosca dulce con queso cremoso',             0.65,  0.30,  45, 15, 'Pan Dulce',     0),
(12, 'Torta de Chocolate',       'Torta húmeda con cobertura de chocolate',   5.00,  2.20,  10,  3, 'Pastelería',    0),
(13, 'Torta de Vainilla',        'Torta clásica de vainilla',                 4.50,  2.00,  12,  3, 'Pastelería',    0),
(14, 'Torta Red Velvet',         'Torta roja con frosting de queso',          6.00,  2.80,   8,  2, 'Pastelería',    0),
(15, 'Cheesecake',               'Torta de queso tipo Nueva York',           7.00,  3.20,   6,  2, 'Pastelería',    0),
(16, 'Muffin de Arándanos',      'Muffin con arándanos frescos',              1.00,  0.45,  30, 10, 'Pastelería',    0),
(17, 'Brownie',                  'Brownie de chocolate intenso',              1.20,  0.55,  25,  8, 'Pastelería',    0),
(18, 'Cupcake de Vainilla',      'Cupcake con frosting de buttercream',       1.10,  0.50,  35, 10, 'Pastelería',    0),
(19, 'Galleta de Chocolate',     'Galleta crujiente con chips',               0.40,  0.18,  80, 25, 'Pastelería',    0),
(20, 'Galleta de Avena',         'Galleta integral con avena',                0.35,  0.15,  70, 20, 'Pastelería',    0),
(21, 'Alfajor de Maicena',       'Alfajor relleno de dulce de leche',         0.80,  0.38,  50, 15, 'Pastelería',    0),
(22, 'Alfajor de Chocolate',     'Alfajor bañado en chocolate',               0.90,  0.42,  45, 15, 'Pastelería',    0),
(23, 'Empanada de Carne',        'Empanada salada rellena de carne',          1.00,  0.48,  40, 15, 'Pan Salado',    0),
(24, 'Empanada de Queso',        'Empanada relleno de queso',                 0.90,  0.42,  35, 12, 'Pan Salado',    0),
(25, 'Empanada de Pollo',        'Empanada rellena de pollo',                 1.00,  0.48,  38, 12, 'Pan Salado',    0),
(26, 'Pizza Baguette',           'Baguette con salsa y queso mozzarella',     1.50,  0.70,  20,  8, 'Pan Salado',    0),
(27, 'Focaccia',                 'Pan italiano con hierbas y aceite',          1.80,  0.85,  15,  5, 'Pan Salado',    0),
(28, 'Pan de Ajo',               'Pan con mantequilla de ajo',                0.80,  0.35,  25,  8, 'Pan Salado',    0),
(29, 'Chapata',                  'Pan rústico de corteza crujiente',          1.20,  0.55,  18,  6, 'Pan Salado',    0),
(30, 'Baguette Clásica',         'Baguette francesa tradicional',             0.90,  0.40,  30, 10, 'Pan Salado',    0),
(31, 'Café con Leche',           'Café espresso con leche',                   1.00,  0.30, 100, 30, 'Bebidas',       0),
(32, 'Capuchino',                'Capuchino cremoso',                         1.50,  0.45,  80, 25, 'Bebidas',       0),
(33, 'Chocolate Caliente',       'Chocolate caliente artesanal',              1.20,  0.40,  60, 20, 'Bebidas',       0),
(34, 'Jugo de Naranja Natural',  'Jugo recién exprimido',                     1.30,  0.50,  40, 15, 'Bebidas',       0),
(35, 'Limonada',                 'Limonada fresca',                           1.00,  0.30,  50, 20, 'Bebidas',       0),
(36, 'Tostado con Jamón',        'Tostado con jamón y queso',                 1.50,  0.70,  30, 10, 'Pan Salado',    0),
(37, 'Sándwich de Pollo',        'Sándwich con pollo y vegetales',            2.00,  0.90,  25,  8, 'Pan Salado',    0),
(38, 'Torta de Zanahoria',       'Torta de zanahoria con frosting',           5.50,  2.50,   7,  2, 'Pastelería',    0),
(39, 'Churros',                  'Churros con azúcar y canela',               0.80,  0.35,  40, 12, 'Pan Dulce',     0),
(40, 'Berlinesa',                'Berlina rellena de crema',                  0.70,  0.32,  55, 18, 'Pan Dulce',     0),
(41, 'Pan de Leche',             'Pan dulce suave con leche',                 0.45,  0.20,  65, 20, 'Pan Dulce',     0),
(42, 'Marraqueta',               'Pan crujiente de corteza fina',             0.30,  0.12, 180, 50, 'Pan Salado',    0),
(43, 'Hallaca',                  'Hallaca tradicional navideña',              3.00,  1.50,  20,  5, 'Pan Salado',    0),
(44, 'Pan de Jamón',             'Pan relleno de jamón y aceitunas',          3.50,  1.80,  15,  5, 'Pan Salado',    0),
(45, 'Cannoli',                  'Cannoli relleno de ricotta',                1.50,  0.70,  20,  6, 'Pastelería',    0),
(46, 'Profiteroles',             'Profiteroles con crema y chocolate',        2.50,  1.10,  15,  5, 'Pastelería',    0),
(47, 'Éclair',                   'Éclair de chocolate',                       1.80,  0.80,  18,  6, 'Pastelería',    0),
(48, 'Strudel de Manzana',       'Strudel de manzana con canela',             2.00,  0.90,  12,  4, 'Pastelería',    0),
(49, 'Brioche',                  'Pan brioche francés',                       1.00,  0.45,  25,  8, 'Pan Dulce',     0),
(50, 'Ciabatta',                 'Pan italiano de burbujas',                  1.10,  0.50,  22,  7, 'Pan Salado',    0)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- =============================================================
-- CLIENTES (50 registros)
-- =============================================================
INSERT INTO clientes (id, nombre, email, telefono, direccion, eliminado) VALUES
(1,  'María González',      'maria.gonzalez@email.com',      '0412-1111001', 'Av. Bolívar, Caracas',              0),
(2,  'Carlos Rodríguez',    'carlos.rodriguez@email.com',    '0414-1111002', 'Calle Principal, Valencia',          0),
(3,  'Ana Martínez',        'ana.martinez@email.com',        '0416-1111003', 'Urb. El Rosario, Maracaibo',        0),
(4,  'Luis Hernández',      'luis.hernandez@email.com',      '0412-1111004', 'Av. Libertador, Barquisimeto',      0),
(5,  'Carmen López',        'carmen.lopez@email.com',        '0424-1111005', 'Calle 5, Mérida',                   0),
(6,  'Pedro Sánchez',       'pedro.sanchez@email.com',       '0414-1111006', 'Urb. Las Mercedes, Caracas',        0),
(7,  'Laura Ramírez',       'laura.ramirez@email.com',       '0412-1111007', 'Av. 4, Barinas',                    0),
(8,  'Miguel Torres',       'miguel.torres@email.com',       '0416-1111008', 'Calle Norte, San Cristóbal',        0),
(9,  'Isabel Vargas',       'isabel.vargas@email.com',       '0424-1111009', 'Urb. El Parque, Valencia',          0),
(10, 'Roberto Díaz',        'roberto.diaz@email.com',        '0412-1111010', 'Av. Bolívar, Barquisimeto',         0),
(11, 'Patricia Mendoza',    'patricia.mendoza@email.com',    '0414-1111011', 'Calle Sur, Maracaibo',              0),
(12, 'Fernando Castillo',   'fernando.castillo@email.com',   '0416-1111012', 'Urb. Los Samanes, Caracas',         0),
(13, 'Rosa Jiménez',        'rosa.jimenez@email.com',        '0412-1111013', 'Av. Principal, Mérida',             0),
(14, 'Jorge Peña',          'jorge.pena@email.com',          '0424-1111014', 'Calle 8, Barinas',                  0),
(15, 'Elena Morales',       'elena.morales@email.com',       '0414-1111015', 'Urb. Villa Florida, San Cristóbal', 0),
(16, 'Diego Reyes',         'diego.reyes@email.com',         '0412-1111016', 'Av. Libertador, Valencia',          0),
(17, 'Claudia Rivas',       'claudia.rivas@email.com',       '0416-1111017', 'Calle Oeste, Caracas',              0),
(18, 'Andrés Campos',       'andres.campos@email.com',       '0424-1111018', 'Urb. La Castellana, Maracaibo',     0),
(19, 'Sandra Paredes',      'sandra.paredes@email.com',      '0414-1111019', 'Av. 5, Barquisimeto',               0),
(20, 'Raúl Ortega',         'raul.ortega@email.com',         '0412-1111020', 'Calle Principal, Mérida',           0),
(21, 'Gabriela Flores',     'gabriela.flores@email.com',     '0416-1111021', 'Urb. El Hatillo, Caracas',          0),
(22, 'Hugo Navarro',        'hugo.navarro@email.com',        '0424-1111022', 'Av. Bolívar, San Cristóbal',        0),
(23, 'Lucía Herrera',       'lucia.herrera@email.com',       '0414-1111023', 'Calle Norte, Valencia',             0),
(24, 'Fernando Guzmán',     'fernando.guzman@email.com',     '0412-1111024', 'Urb. Las Palmas, Barquisimeto',     0),
(25, 'Daniela Silva',       'daniela.silva@email.com',       '0416-1111025', 'Av. 3, Maracaibo',                  0),
(26, 'Oscar Romero',        'oscar.romero@email.com',        '0424-1111026', 'Calle 12, Mérida',                  0),
(27, 'Valentina Cruz',      'valentina.cruz@email.com',      '0414-1111027', 'Urb. Altamira, Caracas',            0),
(28, 'Marco Vásquez',       'marco.vasquez@email.com',       '0412-1111028', 'Av. Principal, Barinas',            0),
(29, 'Paula Arias',         'paula.arias@email.com',         '0416-1111029', 'Calle Sur, San Cristóbal',          0),
(30, 'Ricardo Aguilar',     'ricardo.aguilar@email.com',     '0424-1111030', 'Urb. Los Rosales, Valencia',        0),
(31, 'Adriana Medina',      'adriana.medina@email.com',      '0414-1111031', 'Av. 7, Maracaibo',                  0),
(32, 'Sergio Contreras',    'sergio.contreras@email.com',    '0412-1111032', 'Calle Este, Caracas',               0),
(33, 'Mónica Delgado',      'monica.delgado@email.com',      '0416-1111033', 'Urb. Santa Fe, Barquisimeto',       0),
(34, 'Arturo Ibarra',       'arturo.ibarra@email.com',       '0424-1111034', 'Av. Libertador, Mérida',            0),
(35, 'Beatriz Salazar',     'beatriz.salazar@email.com',     '0414-1111035', 'Calle 4, San Cristóbal',            0),
(36, 'Enrique Ponce',       'enrique.ponce@email.com',       '0412-1111036', 'Urb. El Recreo, Valencia',          0),
(37, 'Teresa Gallegos',     'teresa.gallegos@email.com',     '0416-1111037', 'Av. Bolívar, Barinas',              0),
(38, 'Alejandro Luna',      'alejandro.luna@email.com',      '0424-1111038', 'Calle Norte, Caracas',              0),
(39, 'Cecilia Durán',       'cecilia.duran@email.com',       '0414-1111039', 'Urb. Las Mercedes, Maracaibo',      0),
(40, 'Gabriel Fuentes',     'gabriel.fuentes@email.com',     '0412-1111040', 'Av. 2, San Cristóbal',              0),
(41, 'Diana Campos',        'diana.campos@email.com',        '0416-1111041', 'Calle 10, Mérida',                  0),
(42, 'Francisco Leal',      'francisco.leal@email.com',      '0424-1111042', 'Urb. El Parque, Valencia',          0),
(43, 'Natalia Rojas',       'natalia.rojas@email.com',       '0414-1111043', 'Av. Principal, Barquisimeto',       0),
(44, 'Ricardo Lara',        'ricardo.lara@email.com',        '0412-1111044', 'Calle Sur, Caracas',                0),
(45, 'Mariana Obando',      'mariana.obando@email.com',      '0416-1111045', 'Urb. Villa Rica, Maracaibo',        0),
(46, 'Simón Buitrago',      'simon.buitrago@email.com',      '0424-1111046', 'Av. 6, San Cristóbal',              0),
(47, 'Regina Cárdenas',     'regina.cardenas@email.com',     '0414-1111047', 'Calle 3, Mérida',                   0),
(48, 'Tomás Escalante',     'tomas.escalante@email.com',     '0412-1111048', 'Urb. La Castellana, Valencia',      0),
(49, 'Camila Villeda',      'camila.villeda@email.com',      '0416-1111049', 'Av. Libertador, Barquisimeto',      0),
(50, 'Emilio Salas',        'emilio.salas@email.com',        '0424-1111050', 'Calle Principal, Caracas',          0)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- =============================================================
-- PEDIDOS (30 registros)
-- =============================================================
INSERT INTO pedidos (id, cliente_id, usuario_id, estado, total, fecha_pedido, fecha_entrega, hora_entrega, eliminado) VALUES
(1,  1,  1, 'entregado',  12.50, '2026-08-01 08:00:00', '2026-08-01', '10:00:00', 0),
(2,  2,  1, 'entregado',   8.75, '2026-08-01 09:30:00', '2026-08-01', '11:30:00', 0),
(3,  3,  2, 'entregado',  25.00, '2026-08-02 07:00:00', '2026-08-02', '09:00:00', 0),
(4,  4,  2, 'entregado',  15.30, '2026-08-02 10:00:00', '2026-08-02', '12:00:00', 0),
(5,  5,  1, 'entregado',  32.00, '2026-08-03 08:30:00', '2026-08-03', '10:30:00', 0),
(6,  6,  1, 'entregado',   6.50, '2026-08-03 11:00:00', '2026-08-03', '13:00:00', 0),
(7,  7,  2, 'entregado',  18.75, '2026-08-04 07:30:00', '2026-08-04', '09:30:00', 0),
(8,  8,  2, 'entregado',  10.20, '2026-08-04 09:00:00', '2026-08-04', '11:00:00', 0),
(9,  9,  1, 'entregado',  22.40, '2026-08-05 08:00:00', '2026-08-05', '10:00:00', 0),
(10, 10, 1, 'entregado',  14.60, '2026-08-05 10:30:00', '2026-08-05', '12:30:00', 0),
(11, 11, 2, 'entregado',  28.90, '2026-08-06 07:00:00', '2026-08-06', '09:00:00', 0),
(12, 12, 2, 'entregado',   9.80, '2026-08-06 11:30:00', '2026-08-06', '13:30:00', 0),
(13, 13, 1, 'entregado',  16.50, '2026-08-07 08:00:00', '2026-08-07', '10:00:00', 0),
(14, 14, 1, 'entregado',  20.00, '2026-08-07 09:30:00', '2026-08-07', '11:30:00', 0),
(15, 15, 2, 'entregado',  11.25, '2026-08-08 07:00:00', '2026-08-08', '09:00:00', 0),
(16, 16, 2, 'entregado',  35.00, '2026-08-08 10:00:00', '2026-08-08', '12:00:00', 0),
(17, 17, 1, 'entregado',   7.80, '2026-08-09 08:30:00', '2026-08-09', '10:30:00', 0),
(18, 18, 1, 'entregado',  19.60, '2026-08-09 11:00:00', '2026-08-09', '13:00:00', 0),
(19, 19, 2, 'entregado',  13.40, '2026-08-10 07:30:00', '2026-08-10', '09:30:00', 0),
(20, 20, 2, 'entregado',  24.80, '2026-08-10 09:00:00', '2026-08-10', '11:00:00', 0),
(21, 1,  1, 'entregado',  17.50, '2026-08-11 08:00:00', '2026-08-11', '10:00:00', 0),
(22, 5,  1, 'entregado',  10.00, '2026-08-12 09:00:00', '2026-08-12', '11:00:00', 0),
(23, 9,  2, 'entregado',  21.30, '2026-08-13 07:00:00', '2026-08-13', '09:00:00', 0),
(24, 3,  1, 'en_proceso',  8.50, '2026-08-14 08:30:00', '2026-08-14', '10:30:00', 0),
(25, 7,  2, 'en_proceso',  15.00, '2026-08-14 10:00:00', '2026-08-14', '12:00:00', 0),
(26, 11, 1, 'pendiente',  27.60, '2026-08-15 07:00:00', '2026-08-15', '09:00:00', 0),
(27, 15, 2, 'pendiente',  12.00, '2026-08-15 09:30:00', '2026-08-15', '11:30:00', 0),
(28, 20, 1, 'pendiente',  19.90, '2026-08-15 10:00:00', '2026-08-16', '10:00:00', 0),
(29, 2,  2, 'cancelado',  14.00, '2026-08-10 08:00:00', '2026-08-10', '10:00:00', 0),
(30, 8,  1, 'cancelado',   9.50, '2026-08-11 11:00:00', '2026-08-11', '13:00:00', 0)
ON DUPLICATE KEY UPDATE estado = VALUES(estado);

-- =============================================================
-- GASTOS (20 registros)
-- =============================================================
INSERT INTO gastos (id, descripcion, monto, fecha, eliminado) VALUES
(1,  'Pago de electricidad Agosto',        150.00, '2026-08-01 08:00:00', 0),
(2,  'Compra de combustible generador',     45.00, '2026-08-01 14:00:00', 0),
(3,  'Mantenimiento horno industrial',     200.00, '2026-08-02 09:00:00', 0),
(4,  'Compra de limpieza general',          35.00, '2026-08-02 15:00:00', 0),
(5,  'Pago agua potable Julio',             28.00, '2026-08-03 08:00:00', 0),
(6,  'Reparación amasadora',               350.00, '2026-08-03 10:00:00', 0),
(7,  'Compra de uniformes personal',       120.00, '2026-08-04 09:00:00', 0),
(8,  'Servicio de internet mensual',        45.00, '2026-08-05 08:00:00', 0),
(9,  'Compra de papelería y útiles',        22.00, '2026-08-05 14:00:00', 0),
(10, 'Mantenimiento extractor de aire',     80.00, '2026-08-06 09:00:00', 0),
(11, 'Pago de teléfono fijo',               30.00, '2026-08-07 08:00:00', 0),
(12, 'Compra de bombillas LED',             25.00, '2026-08-07 15:00:00', 0),
(13, 'Servicio de seguridad mensual',      180.00, '2026-08-08 08:00:00', 0),
(14, 'Reparación enfriador de bizcochos',  150.00, '2026-08-09 10:00:00', 0),
(15, 'Compra de etiquetas y precios',       18.00, '2026-08-10 09:00:00', 0),
(16, 'Pago de impuesto municipal',          65.00, '2026-08-11 08:00:00', 0),
(17, 'Mantenimiento preventivo ovens',     100.00, '2026-08-12 09:00:00', 0),
(18, 'Compra de extintores recarga',       40.00, '2026-08-13 10:00:00', 0),
(19, 'Pago servicio de recolección',       55.00, '2026-08-14 08:00:00', 0),
(20, 'Capital semillero',                    500.00, '2026-08-01 07:00:00', 0)
ON DUPLICATE KEY UPDATE descripcion = VALUES(descripcion);

-- =============================================================
-- USUARIOS ADICIONALES (47 registros, total 50)
-- =============================================================
INSERT INTO usuarios (id, rol_id, nombre, email, password_hash, estado, eliminado) VALUES
(4,  2, 'Lucía Fernández',      'lucia.fernandez@lavicky.com',   '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(5,  2, 'Marcos Herrera',       'marcos.herrera@lavicky.com',    '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(6,  3, 'Rosa Méndez',          'rosa.mendez@lavicky.com',       '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(7,  3, 'Pedro Castillo',       'pedro.castillo@lavicky.com',    '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(8,  2, 'Andrea Rivas',         'andrea.rivas@lavicky.com',      '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(9,  3, 'Jorge Domínguez',      'jorge.dominguez@lavicky.com',   '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(10, 2, 'Sofía Contreras',      'sofia.contreras@lavicky.com',   '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(11, 3, 'Diego Salazar',        'diego.salazar@lavicky.com',     '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(12, 2, 'Valeria Ortega',       'valeria.ortega@lavicky.com',    '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(13, 3, 'Carlos Mendoza',       'carlos.mendoza@lavicky.com',    '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(14, 2, 'Patricia Leal',        'patricia.leal@lavicky.com',     '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(15, 3, 'Fernando Rojas',       'fernando.rojas@lavicky.com',    '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(16, 2, 'Daniela Vargas',       'daniela.vargas@lavicky.com',    '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(17, 3, 'Ricardo Peña',         'ricardo.pena@lavicky.com',      '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(18, 2, 'Gabriela Navarro',     'gabriela.navarro@lavicky.com',  '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(19, 3, 'Hugo Jiménez',         'hugo.jimenez@lavicky.com',      '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(20, 2, 'Claudia Morales',      'claudia.morales@lavicky.com',   '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(21, 3, 'Marco Díaz',           'marco.diaz@lavicky.com',        '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(22, 2, 'Luciana Torres',       'luciana.torres@lavicky.com',    '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(23, 3, 'Sergio Blanco',        'sergio.blanco@lavicky.com',     '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(24, 2, 'Natalia Ruiz',         'natalia.ruiz@lavicky.com',      '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(25, 3, 'Oscar Medina',         'oscar.medina@lavicky.com',      '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(26, 2, 'Adriana Campos',       'adriana.campos@lavicky.com',    '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(27, 3, 'Andrés Herrera',       'andres.herrera@lavicky.com',    '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(28, 2, 'Mariana Ríos',         'mariana.rios@lavicky.com',      '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(29, 3, 'Francisco Aguilar',    'francisco.aguilar@lavicky.com', '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(30, 2, 'Regina Silva',         'regina.silva@lavicky.com',      '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(31, 3, 'Alejandro Cruz',       'alejandro.cruz@lavicky.com',    '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(32, 2, 'Teresa Fuentes',       'teresa.fuentes@lavicky.com',    '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(33, 3, 'Simón Rivas',          'simon.rivas@lavicky.com',       '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(34, 2, 'Camila Ortega',        'camila.ortega@lavicky.com',     '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(35, 3, 'Tomás Paredes',        'tomas.paredes@lavicky.com',     '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(36, 2, 'Isabela Navarro',      'isabela.navarro@lavicky.com',   '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(37, 3, 'Gabriel Obando',       'gabriel.obando@lavicky.com',    '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(38, 2, 'Paula Salas',          'paula.salas@lavicky.com',       '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(39, 3, 'Emilio Lara',          'emilio.lara@lavicky.com',       '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(40, 2, 'Beatriz Leal',         'beatriz.leal@lavicky.com',      '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(41, 3, 'Ricardo Luna',         'ricardo.luna@lavicky.com',      '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(42, 2, 'Diana Rojas',          'diana.rojas@lavicky.com',       '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(43, 3, 'Enrique Méndez',       'enrique.mendez@lavicky.com',    '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(44, 2, 'Natalia Domínguez',    'natalia.dominguez@lavicky.com', '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(45, 3, 'Alejandro Salazar',    'alejandro.salazar@lavicky.com', '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(46, 2, 'Valentina Leal',       'valentina.leal@lavicky.com',    '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(47, 3, 'Marco Vargas',         'marco.vargas@lavicky.com',      '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(48, 2, 'Sofía Paredes',        'sofia.paredes@lavicky.com',     '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(49, 3, 'Diego Rojas',          'diego.rojas@lavicky.com',       '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0),
(50, 2, 'Carla Mendoza',        'carla.mendoza@lavicky.com',     '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo', 0)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- =============================================================
-- PRODUCTO_RECETA (recetas para producción de productos clave)
-- =============================================================
INSERT INTO producto_receta (producto_id, insumo_id, cantidad_requerida) VALUES
-- Pan Francés (prod 1): harina, levadura, sal, azúcar
(1, 1, 0.30),   -- 0.30 Kg Harina Trigo Premium
(1, 27, 0.01),  -- 0.01 Kg Levadura Fresca
(1, 39, 0.005), -- 0.005 Kg Sal Fina
(1, 11, 0.01),  -- 0.01 Kg Azúcar Blanca
-- Pan Integral (prod 2): harina integral, avena, levadura
(2, 2, 0.25),   -- 0.25 Kg Harina Integral
(2, 29, 0.05),  -- 0.05 Kg Avena Integral
(2, 27, 0.01),  -- 0.01 Kg Levadura Fresca
-- Medialuna de Manteca (prod 4): harina, mantequilla, huevos, azúcar
(4, 1, 0.20),   -- 0.20 Kg Harina Trigo
(4, 6, 0.08),   -- 0.08 Kg Mantequilla sin Sal
(4, 9, 1),      -- 1 Huevo
(4, 11, 0.03),  -- 0.03 Kg Azúcar
-- Torta de Chocolate (prod 12): harina, chocolate, huevos, azúcar, mantequilla
(12, 1, 0.30),  -- 0.30 Kg Harina
(12, 20, 0.20), -- 0.20 Kg Chocolate Negro 70%
(12, 9, 4),     -- 4 Huevos
(12, 11, 0.25), -- 0.25 Kg Azúcar
(12, 6, 0.10),  -- 0.10 Kg Mantequilla sin Sal
-- Factura Simple (prod 7): harina, huevos, crema, azúcar
(7, 1, 0.15),   -- 0.15 Kg Harina
(7, 9, 1),      -- 1 Huevo
(7, 7, 0.05),   -- 0.05 Litros Crema de Leche
(7, 11, 0.05),  -- 0.05 Kg Azúcar
-- Empanada de Queso (prod 24): harina, queso mozzarella, huevos
(24, 1, 0.12),  -- 0.12 Kg Harina
(24, 46, 0.08), -- 0.08 Kg Queso Mozzarella
(24, 9, 1),     -- 1 Huevo
-- Chipá (prod 10): maicena, queso crema, huevos
(10, 49, 0.10), -- 0.10 Kg Maicena
(10, 8, 0.05),  -- 0.05 Kg Queso Crema
(10, 9, 1),     -- 1 Huevo
-- Café con Leche (prod 31): leche entera
(31, 4, 0.25),  -- 0.25 Litros Leche Entera
-- Croissant de Jamón y Queso (prod 6): harina, jamón, queso, mantequilla, huevos
(6, 1, 0.15),   -- 0.15 Kg Harina
(6, 45, 0.05),  -- 0.05 Kg Jamón Cocido
(6, 46, 0.05),  -- 0.05 Kg Queso Mozzarella
(6, 6, 0.06),   -- 0.06 Kg Mantequilla
(6, 9, 1)       -- 1 Huevo
ON DUPLICATE KEY UPDATE cantidad_requerida = VALUES(cantidad_requerida);

-- =============================================================
-- DETALLE_PEDIDO (líneas de pedido para los 30 pedidos)
-- =============================================================
INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio_unitario, subtotal) VALUES
-- Pedido 1: 50 Pan Francés @ 0.25 = 12.50
(1, 1, 50, 0.25, 12.50),
-- Pedido 2: 25 Pan Hamburguesa @ 0.35 = 8.75
(2, 3, 25, 0.35, 8.75),
-- Pedido 3: 5 Torta Chocolate @ 5.00 = 25.00
(3, 12, 5, 5.00, 25.00),
-- Pedido 4: 17 Empanada Queso @ 0.90 = 15.30
(4, 24, 17, 0.90, 15.30),
-- Pedido 5: 32 Café con Leche @ 1.00 = 32.00
(5, 31, 32, 1.00, 32.00),
-- Pedido 6: 26 Pan Francés @ 0.25 = 6.50
(6, 1, 26, 0.25, 6.50),
-- Pedido 7: 75 Pan Francés @ 0.25 = 18.75
(7, 1, 75, 0.25, 18.75),
-- Pedido 8: 17 Factura Simple @ 0.60 = 10.20
(8, 7, 17, 0.60, 10.20),
-- Pedido 9: 56 Pan Integral @ 0.40 = 22.40
(9, 2, 56, 0.40, 22.40),
-- Pedido 10: 10 Medialuna Manteca @ 0.50 + 16 Factura Simple @ 0.60 = 14.60
(10, 4, 10, 0.50, 5.00),
(10, 7, 16, 0.60, 9.60),
-- Pedido 11: 20 Medialuna Manteca @ 0.50 + 21 Empanada Queso @ 0.90 = 28.90
(11, 4, 20, 0.50, 10.00),
(11, 24, 21, 0.90, 18.90),
-- Pedido 12: 14 Factura Crema @ 0.70 = 9.80
(12, 8, 14, 0.70, 9.80),
-- Pedido 13: 33 Medialuna Manteca @ 0.50 = 16.50
(13, 4, 33, 0.50, 16.50),
-- Pedido 14: 20 Café con Leche @ 1.00 = 20.00
(14, 31, 20, 1.00, 20.00),
-- Pedido 15: 45 Pan Francés @ 0.25 = 11.25
(15, 1, 45, 0.25, 11.25),
-- Pedido 16: 35 Café con Leche @ 1.00 = 35.00
(16, 31, 35, 1.00, 35.00),
-- Pedido 17: 26 Chipá @ 0.30 = 7.80
(17, 10, 26, 0.30, 7.80),
-- Pedido 18: 49 Pan Integral @ 0.40 = 19.60
(18, 2, 49, 0.40, 19.60),
-- Pedido 19: 10 Medialuna Manteca @ 0.50 + 14 Factura Simple @ 0.60 = 13.40
(19, 4, 10, 0.50, 5.00),
(19, 7, 14, 0.60, 8.40),
-- Pedido 20: 62 Pan Integral @ 0.40 = 24.80
(20, 2, 62, 0.40, 24.80),
-- Pedido 21: 25 Factura Crema @ 0.70 = 17.50
(21, 8, 25, 0.70, 17.50),
-- Pedido 22: 20 Medialuna Manteca @ 0.50 = 10.00
(22, 4, 20, 0.50, 10.00),
-- Pedido 23: 71 Chipá @ 0.30 = 21.30
(23, 10, 71, 0.30, 21.30),
-- Pedido 24: 17 Medialuna Manteca @ 0.50 = 8.50 (en_proceso)
(24, 4, 17, 0.50, 8.50),
-- Pedido 25: 30 Medialuna Manteca @ 0.50 = 15.00 (en_proceso)
(25, 4, 30, 0.50, 15.00),
-- Pedido 26: 46 Factura Simple @ 0.60 = 27.60 (pendiente)
(26, 7, 46, 0.60, 27.60),
-- Pedido 27: 24 Medialuna Manteca @ 0.50 = 12.00 (pendiente)
(27, 4, 24, 0.50, 12.00),
-- Pedido 28: 20 Medialuna Manteca @ 0.50 + 11 Empanada Queso @ 0.90 = 19.90 (pendiente)
(28, 4, 20, 0.50, 10.00),
(28, 24, 11, 0.90, 9.90),
-- Pedido 29: 14 Café con Leche @ 1.00 = 14.00 (cancelado)
(29, 31, 14, 1.00, 14.00),
-- Pedido 30: 19 Medialuna Manteca @ 0.50 = 9.50 (cancelado)
(30, 4, 19, 0.50, 9.50);

-- =============================================================
-- VENTAS (23 registros, solo pedidos entregados)
-- Impuestos: 15% (tasa_impuesto de empresa)
-- =============================================================
INSERT INTO ventas (id, pedido_id, subtotal, impuestos, descuento, total, monto_pagado, cambio, ganancias, usuario_id, estado, fecha_venta) VALUES
(1,  1,  10.87, 1.63, 0.00, 12.50, 12.50, 0.00,  7.50, 1, 'completado', '2026-08-01 08:00:00'),
(2,  2,   7.61, 1.14, 0.00,  8.75,  8.75, 0.00,  5.75, 1, 'completado', '2026-08-01 09:30:00'),
(3,  3,  21.74, 3.26, 0.00, 25.00, 25.00, 0.00, 14.00, 2, 'completado', '2026-08-02 07:00:00'),
(4,  4,  13.30, 2.00, 0.00, 15.30, 15.30, 0.00,  8.16, 2, 'completado', '2026-08-02 10:00:00'),
(5,  5,  27.83, 4.17, 0.00, 32.00, 32.00, 0.00, 22.40, 1, 'completado', '2026-08-03 08:30:00'),
(6,  6,   5.65, 0.85, 0.00,  6.50,  6.50, 0.00,  3.90, 1, 'completado', '2026-08-03 11:00:00'),
(7,  7,  16.30, 2.45, 0.00, 18.75, 18.75, 0.00, 11.25, 2, 'completado', '2026-08-04 07:30:00'),
(8,  8,   8.87, 1.33, 0.00, 10.20, 10.20, 0.00,  5.44, 2, 'completado', '2026-08-04 09:00:00'),
(9,  9,  19.48, 2.92, 0.00, 22.40, 22.40, 0.00, 12.32, 1, 'completado', '2026-08-05 08:00:00'),
(10, 10, 12.70, 1.90, 0.00, 14.60, 14.60, 0.00,  7.92, 1, 'completado', '2026-08-05 10:30:00'),
(11, 11, 25.13, 3.77, 0.00, 28.90, 28.90, 0.00, 15.68, 2, 'completado', '2026-08-06 07:00:00'),
(12, 12,  8.52, 1.28, 0.00,  9.80,  9.80, 0.00,  5.32, 2, 'completado', '2026-08-06 11:30:00'),
(13, 13, 14.35, 2.15, 0.00, 16.50, 16.50, 0.00,  9.24, 1, 'completado', '2026-08-07 08:00:00'),
(14, 14, 17.39, 2.61, 0.00, 20.00, 20.00, 0.00, 14.00, 1, 'completado', '2026-08-07 09:30:00'),
(15, 15,  9.78, 1.47, 0.00, 11.25, 11.25, 0.00,  6.75, 2, 'completado', '2026-08-08 07:00:00'),
(16, 16, 30.43, 4.57, 0.00, 35.00, 35.00, 0.00, 24.50, 2, 'completado', '2026-08-08 10:00:00'),
(17, 17,  6.78, 1.02, 0.00,  7.80,  7.80, 0.00,  4.68, 1, 'completado', '2026-08-09 08:30:00'),
(18, 18, 17.04, 2.56, 0.00, 19.60, 19.60, 0.00, 10.78, 1, 'completado', '2026-08-09 11:00:00'),
(19, 19, 11.65, 1.75, 0.00, 13.40, 13.40, 0.00,  7.28, 2, 'completado', '2026-08-10 07:30:00'),
(20, 20, 21.57, 3.23, 0.00, 24.80, 24.80, 0.00, 13.64, 2, 'completado', '2026-08-10 09:00:00'),
(21, 21, 15.22, 2.28, 0.00, 17.50, 17.50, 0.00,  9.50, 1, 'completado', '2026-08-11 08:00:00'),
(22, 22,  8.70, 1.30, 0.00, 10.00, 10.00, 0.00,  5.60, 1, 'completado', '2026-08-12 09:00:00'),
(23, 23, 18.52, 2.78, 0.00, 21.30, 21.30, 0.00, 12.78, 2, 'completado', '2026-08-13 07:00:00');

-- =============================================================
-- DETALLE_VENTA (espejo de detalle_pedido para ventas completadas)
-- =============================================================
INSERT INTO detalle_venta (venta_id, producto_id, cantidad, precio_unitario, descuento, subtotal) VALUES
(1,  1,  50, 0.25, 0.00, 12.50),
(2,  3,  25, 0.35, 0.00,  8.75),
(3,  12,  5, 5.00, 0.00, 25.00),
(4,  24, 17, 0.90, 0.00, 15.30),
(5,  31, 32, 1.00, 0.00, 32.00),
(6,  1,  26, 0.25, 0.00,  6.50),
(7,  1,  75, 0.25, 0.00, 18.75),
(8,  7,  17, 0.60, 0.00, 10.20),
(9,  2,  56, 0.40, 0.00, 22.40),
(10, 4,  10, 0.50, 0.00,  5.00),
(10, 7,  16, 0.60, 0.00,  9.60),
(11, 4,  20, 0.50, 0.00, 10.00),
(11, 24, 21, 0.90, 0.00, 18.90),
(12, 8,  14, 0.70, 0.00,  9.80),
(13, 4,  33, 0.50, 0.00, 16.50),
(14, 31, 20, 1.00, 0.00, 20.00),
(15, 1,  45, 0.25, 0.00, 11.25),
(16, 31, 35, 1.00, 0.00, 35.00),
(17, 10, 26, 0.30, 0.00,  7.80),
(18, 2,  49, 0.40, 0.00, 19.60),
(19, 4,  10, 0.50, 0.00,  5.00),
(19, 7,  14, 0.60, 0.00,  8.40),
(20, 2,  62, 0.40, 0.00, 24.80),
(21, 8,  25, 0.70, 0.00, 17.50),
(22, 4,  20, 0.50, 0.00, 10.00),
(23, 10, 71, 0.30, 0.00, 21.30);

-- =============================================================
-- PAGOS (un pago por venta, métodos variados)
-- =============================================================
INSERT INTO pagos (venta_id, monto, metodo_pago, estado, referencia, fecha_pago) VALUES
(1,  12.50, 'efectivo',      'completado', NULL,                    '2026-08-01 08:05:00'),
(2,   8.75, 'tarjeta',       'completado', 'REF-T-000002',         '2026-08-01 09:35:00'),
(3,  25.00, 'wallet',        'completado', 'REF-W-000003',         '2026-08-02 07:05:00'),
(4,  15.30, 'efectivo',      'completado', NULL,                    '2026-08-02 10:05:00'),
(5,  32.00, 'tarjeta',       'completado', 'REF-T-000005',         '2026-08-03 08:35:00'),
(6,   6.50, 'efectivo',      'completado', NULL,                    '2026-08-03 11:05:00'),
(7,  18.75, 'wallet',        'completado', 'REF-W-000007',         '2026-08-04 07:35:00'),
(8,  10.20, 'efectivo',      'completado', NULL,                    '2026-08-04 09:05:00'),
(9,  22.40, 'tarjeta',       'completado', 'REF-T-000009',         '2026-08-05 08:05:00'),
(10, 14.60, 'efectivo',      'completado', NULL,                    '2026-08-05 10:35:00'),
(11, 28.90, 'wallet',        'completado', 'REF-W-000011',         '2026-08-06 07:05:00'),
(12,  9.80, 'efectivo',      'completado', NULL,                    '2026-08-06 11:35:00'),
(13, 16.50, 'tarjeta',       'completado', 'REF-T-000013',         '2026-08-07 08:05:00'),
(14, 20.00, 'efectivo',      'completado', NULL,                    '2026-08-07 09:35:00'),
(15, 11.25, 'wallet',        'completado', 'REF-W-000015',         '2026-08-08 07:05:00'),
(16, 35.00, 'efectivo',      'completado', NULL,                    '2026-08-08 10:05:00'),
(17,  7.80, 'tarjeta',       'completado', 'REF-T-000017',         '2026-08-09 08:35:00'),
(18, 19.60, 'efectivo',      'completado', NULL,                    '2026-08-09 11:05:00'),
(19, 13.40, 'wallet',        'completado', 'REF-W-000019',         '2026-08-10 07:35:00'),
(20, 24.80, 'tarjeta',       'completado', 'REF-T-000020',         '2026-08-10 09:05:00'),
(21, 17.50, 'efectivo',      'completado', NULL,                    '2026-08-11 08:05:00'),
(22, 10.00, 'wallet',        'completado', 'REF-W-000022',         '2026-08-12 09:05:00'),
(23, 21.30, 'tarjeta',       'completado', 'REF-T-000023',         '2026-08-13 07:05:00');

-- =============================================================
-- PRODUCCIONES (6 lotes de producción)
-- =============================================================
INSERT INTO producciones (id, producto_id, cantidad_producida, fecha) VALUES
(1,  1,  200.00, '2026-08-01 05:00:00'),
(2,  4,   50.00, '2026-08-02 05:00:00'),
(3,  2,  120.00, '2026-08-03 05:30:00'),
(4, 12,   10.00, '2026-08-05 06:00:00'),
(5,  7,   40.00, '2026-08-06 05:00:00'),
(6, 10,   60.00, '2026-08-08 05:30:00');

-- =============================================================
-- PRODUCCION_DETALLE (ingredientes usados en cada producción)
-- =============================================================
INSERT INTO produccion_detalle (produccion_id, insumo_id, cantidad_usada) VALUES
-- Producción 1: 200 Pan Francés → harina 60Kg, levadura 2Kg, sal 1Kg, azúcar 2Kg
(1, 1,  60.00),
(1, 27,  2.00),
(1, 39,  1.00),
(1, 11,  2.00),
-- Producción 2: 50 Medialuna Manteca → harina 10Kg, mantequilla 4Kg, huevos 50, azúcar 1.5Kg
(2, 1,  10.00),
(2, 6,   4.00),
(2, 9,  50.00),
(2, 11,  1.50),
-- Producción 3: 120 Pan Integral → harina integral 30Kg, avena 6Kg, levadura 1.2Kg
(3, 2,  30.00),
(3, 29,  6.00),
(3, 27,  1.20),
-- Producción 4: 10 Torta Chocolate → harina 3Kg, chocolate 2Kg, huevos 40, azúcar 2.5Kg, mantequilla 1Kg
(4, 1,   3.00),
(4, 20,  2.00),
(4, 9,  40.00),
(4, 11,  2.50),
(4, 6,   1.00),
-- Producción 5: 40 Factura Simple → harina 6Kg, huevos 40, crema 2L, azúcar 2Kg
(5, 1,   6.00),
(5, 9,  40.00),
(5, 7,   2.00),
(5, 11,  2.00),
-- Producción 6: 60 Chipá → maicena 6Kg, queso crema 3Kg, huevos 60
(6, 49,  6.00),
(6, 8,   3.00),
(6, 9,  60.00);

SET FOREIGN_KEY_CHECKS = 1;
