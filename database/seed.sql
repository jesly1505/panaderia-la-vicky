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
  (3,  'inventario.gestionar',  'Inventario',   'Gestionar insumos',          'Crear insumos, ajustar stock y registrar compras.'),
  (4,  'inventario.eliminar',   'Inventario',   'Eliminar insumos',           'Dar de baja insumos (borrado lógico).'),
  (5,  'proveedores.ver',       'Proveedores',  'Ver proveedores',            'Listar proveedores.'),
  (6,  'proveedores.gestionar', 'Proveedores',  'Gestionar proveedores',      'Crear, editar y eliminar proveedores.'),
  (7,  'productos.ver',         'Productos',    'Ver productos',              'Listar productos y recetas.'),
  (8,  'productos.gestionar',   'Productos',    'Gestionar productos',        'Crear productos y registrar producción.'),
  (9,  'productos.eliminar',    'Productos',    'Eliminar productos',         'Dar de baja productos (borrado lógico).'),
  (10, 'produccion.ver',        'Producción',   'Ver producción',             'Ver historial de producción manual.'),
  (11, 'produccion.gestionar',  'Producción',   'Registrar producción',       'Registrar producción manual de lotes.'),
  (12, 'clientes.ver',          'Clientes',     'Ver clientes',               'Listar clientes y su historial de compras.'),
  (13, 'clientes.gestionar',    'Clientes',     'Gestionar clientes',         'Crear, editar y eliminar clientes.'),
  (14, 'pedidos.ver',           'Pedidos',      'Ver pedidos',                'Listar pedidos y sus detalles.'),
  (15, 'pedidos.gestionar',     'Pedidos',      'Gestionar pedidos',          'Crear pedidos, cambiar estados y eliminarlos.'),
  (16, 'ventas.ver',            'Ventas',       'Ver ventas',                 'Listar ventas, top productos y gráficos.'),
  (17, 'ventas.gestionar',      'Ventas',       'Gestionar ventas',           'Registrar ventas directas y cancelarlas.'),
  (18, 'reportes.ver',          'Reportes',     'Ver reportes',               'Consultar reportes semanales y mensuales.'),
  (19, 'gastos.ver',            'Gastos',       'Ver gastos',                 'Consultar gastos por fecha.'),
  (20, 'gastos.gestionar',      'Gastos',       'Gestionar gastos',           'Registrar y eliminar gastos.'),
  (21, 'empleados.ver',         'Empleados',    'Ver empleados',              'Listar empleados y su rendimiento.'),
  (22, 'empleados.gestionar',   'Empleados',    'Gestionar empleados',        'Crear y eliminar empleados.'),
  (23, 'auditoria.ver',         'Auditoría',    'Ver auditoría',              'Consultar bitácora y accesos denegados.'),
  (24, 'permisos.gestionar',    'Permisos',     'Gestionar permisos',         'Asignar permisos a los roles.'),
  (25, 'perfil.gestionar',      'Configuración', 'Gestionar perfil de la panadería', 'Editar datos del negocio: nombre, descripción, dirección, teléfono, RUC, moneda e impuestos.')
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
       ('dashboard.ver', 'productos.ver', 'clientes.ver', 'clientes.gestionar',
        'pedidos.ver', 'pedidos.gestionar', 'ventas.ver', 'ventas.gestionar', 'gastos.ver'))
   OR (r.nombre = 'Panadero'  AND p.codigo IN
       ('dashboard.ver', 'inventario.ver', 'inventario.gestionar', 'inventario.eliminar',
        'proveedores.ver', 'proveedores.gestionar', 'productos.ver', 'productos.gestionar',
        'productos.eliminar', 'produccion.ver', 'produccion.gestionar'))
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
