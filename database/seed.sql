-- =============================================================
-- Seed de datos base para "Panadería La Vicky"
-- Roles y usuarios de ejemplo.
-- Contraseña de los 3 usuarios: admin123
-- =============================================================

SET NAMES utf8mb4;

INSERT INTO roles (id, nombre, descripcion) VALUES
  (1, 'Administrador', 'Acceso total y control administrativo del sistema'),
  (2, 'Cajero', 'Acceso operativo a ventas, pedidos y productos'),
  (3, 'Panadero', 'Acceso a inventario de materias primas, recetas y producción de lotes')
ON DUPLICATE KEY UPDATE
  nombre = VALUES(nombre),
  descripcion = VALUES(descripcion);

INSERT INTO usuarios (id, rol_id, nombre, email, password_hash, estado) VALUES
  (1, 1, 'Administrador General', 'admin@lavicky.com',    '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo'),
  (2, 2, 'Carlos Vendedor',       'cajero@lavicky.com',   '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo'),
  (3, 3, 'Panadero de Prueba (Dev)', 'panadero.test@lavicky.com', '$2y$10$hqnOM7rn.Bq7a7Anhivbw.tyPXxkavVLx5hpth1zNbrAnipQj4P8C', 'activo')
ON DUPLICATE KEY UPDATE
  rol_id = VALUES(rol_id),
  nombre = VALUES(nombre),
  password_hash = VALUES(password_hash),
  estado = VALUES(estado);

-- =============================================================
-- Catálogo de permisos y asignación por rol (RBAC).
-- El Administrador recibe todos; Cajero y Panadero reciben los
-- que hoy permitía la matriz de rutas (ver backend/bootstrap.php).
-- =============================================================

INSERT INTO permisos (id, codigo, modulo, nombre, descripcion) VALUES
  (1,  'dashboard.ver',         'Dashboard',    'Ver dashboard',              'Acceso a la página principal e indicadores.'),
  (2,  'inventario.ver',        'Inventario',   'Ver insumos',                'Listar insumos y alertas de stock bajo.'),
  (3,  'inventario.crear',  'Inventario',   'Crear insumos',              'Crear insumos, ajustar stock y registrar compras.'),
  (33, 'inventario.editar', 'Inventario',   'Editar insumos',             'Modificar insumos, ajustar stock y registrar compras.'),
  (4,  'inventario.eliminar',   'Inventario',   'Eliminar insumos',           'Dar de baja insumos (borrado lógico).'),
  (5,  'proveedores.ver',       'Proveedores',  'Ver proveedores',            'Listar proveedores.'),
  (6,  'proveedores.crear', 'Proveedores',  'Crear proveedores',          'Dar de alta nuevos proveedores.'),
  (34, 'proveedores.editar', 'Proveedores', 'Editar proveedores',         'Editar datos de proveedores.'),
  (7,  'productos.ver',         'Productos',    'Ver productos',              'Listar productos y recetas.'),
  (8,  'productos.crear',   'Productos',    'Crear productos',            'Crear productos y registrar producción.'),
  (35, 'productos.editar',  'Productos',    'Editar productos',           'Modificar productos y registrar producción.'),
  (9,  'productos.eliminar',    'Productos',    'Eliminar productos',         'Dar de baja productos (borrado lógico).'),
  (10, 'produccion.ver',        'Producción',   'Ver producción',             'Ver historial de producción manual.'),
  (11, 'produccion.crear',  'Producción',   'Registrar producción',        'Registrar producción manual de lotes.'),
  (36, 'produccion.editar', 'Producción',   'Editar producción',          'Modificar registros de producción manual.'),
  (12, 'clientes.ver',          'Clientes',     'Ver clientes',               'Listar clientes y su historial de compras.'),
  (13, 'clientes.crear',    'Clientes',     'Crear clientes',             'Dar de alta nuevos clientes.'),
  (37, 'clientes.editar',   'Clientes',     'Editar clientes',            'Editar datos de clientes.'),
  (14, 'pedidos.ver',           'Pedidos',      'Ver pedidos',                'Listar pedidos y sus detalles.'),
  (15, 'pedidos.crear',     'Pedidos',      'Crear pedidos',              'Crear pedidos y cambiar su estado.'),
  (38, 'pedidos.editar',    'Pedidos',      'Editar pedidos',             'Editar pedidos y cambiar su estado.'),
  (16, 'ventas.ver',            'Ventas',       'Ver ventas',                 'Listar ventas, top productos y gráficos.'),
  (17, 'ventas.crear',      'Ventas',       'Crear ventas',               'Registrar ventas directas.'),
  (39, 'ventas.editar',     'Ventas',       'Editar ventas',              'Modificar ventas directas.'),
  (18, 'reportes.ver',          'Reportes',     'Ver reportes',               'Consultar reportes semanales y mensuales.'),
  (19, 'gastos.ver',            'Gastos',       'Ver gastos',                 'Consultar gastos por fecha.'),
  (20, 'gastos.crear',      'Gastos',       'Crear gastos',               'Registrar gastos.'),
  (40, 'gastos.editar',     'Gastos',       'Editar gastos',              'Editar registros de gastos.'),
  (21, 'empleados.ver',         'Empleados',    'Ver empleados',              'Listar empleados y su rendimiento.'),
  (22, 'empleados.crear',   'Empleados',    'Crear empleados',            'Dar de alta nuevos empleados.'),
  (41, 'empleados.editar',  'Empleados',    'Editar empleados',           'Editar datos de empleados.'),
  (23, 'auditoria.ver',         'Auditoría',    'Ver auditoría',              'Consultar bitácora y accesos denegados.'),
  (24, 'permisos.gestionar',    'Permisos',     'Gestionar permisos',         'Asignar permisos a los roles.'),
  (25, 'perfil.gestionar',      'Configuración', 'Gestionar perfil de la panadería', 'Editar datos del negocio: nombre, descripción, dirección, teléfono, RUC, moneda e impuestos.'),
  (26, 'proveedores.eliminar', 'Proveedores',  'Eliminar proveedores',      'Dar de baja proveedores (borrado lógico).'),
  (27, 'clientes.eliminar',    'Clientes',     'Eliminar clientes',         'Dar de baja clientes (borrado lógico).'),
  (28, 'pedidos.eliminar',     'Pedidos',      'Eliminar pedidos',          'Eliminar pedidos (borrado lógico).'),
  (29, 'ventas.eliminar',      'Ventas',       'Eliminar ventas',           'Anular/cancelar ventas.'),
  (30, 'ventas.ver_factura',   'Ventas',       'Ver e imprimir factura',    'Abrir e imprimir la factura de una venta.'),
  (31, 'gastos.eliminar',      'Gastos',       'Eliminar gastos',           'Eliminar registros de gastos.'),
  (32, 'empleados.eliminar',   'Empleados',    'Eliminar empleados',        'Eliminar empleados y sus usuarios.')
ON DUPLICATE KEY UPDATE
  codigo = VALUES(codigo),
  modulo = VALUES(modulo),
  nombre = VALUES(nombre),
  descripcion = VALUES(descripcion);

INSERT INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p
WHERE r.nombre = 'Administrador'
   OR (r.nombre = 'Cajero'    AND p.codigo IN
       ('dashboard.ver', 'productos.ver', 'clientes.ver', 'clientes.crear', 'clientes.editar', 'clientes.eliminar',
        'pedidos.ver', 'pedidos.crear', 'pedidos.editar', 'pedidos.eliminar', 'ventas.ver', 'ventas.crear',
        'ventas.editar', 'ventas.eliminar', 'ventas.ver_factura', 'gastos.ver'))
   OR (r.nombre = 'Panadero'  AND p.codigo IN
       ('dashboard.ver', 'inventario.ver', 'inventario.crear', 'inventario.editar', 'inventario.eliminar',
        'proveedores.ver', 'proveedores.crear', 'proveedores.editar', 'proveedores.eliminar',
        'productos.ver', 'productos.crear', 'productos.editar', 'productos.eliminar',
        'produccion.ver', 'produccion.crear', 'produccion.editar'))
ON DUPLICATE KEY UPDATE rol_id = VALUES(rol_id);

-- =============================================================
-- Perfil de la panadería (datos del negocio que salen en la factura).
-- =============================================================

INSERT INTO empresa (id, nombre, descripcion, direccion, telefono, ruc, moneda, tasa_impuesto) VALUES
  (1, 'Panadería La Vicky', 'Panadería & Pastelería', 'Av. Principal calle 5', '1234-5678', NULL, 'USD', 15.00)
ON DUPLICATE KEY UPDATE
  nombre = VALUES(nombre),
  descripcion = VALUES(descripcion),
  direccion = VALUES(direccion),
  telefono = VALUES(telefono),
  ruc = VALUES(ruc),
  moneda = VALUES(moneda),
  tasa_impuesto = VALUES(tasa_impuesto);
