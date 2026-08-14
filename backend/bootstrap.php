<?php
/**
 * Bootstrap de la aplicación.
 * Configura el contenedor de dependencias y registra todas las rutas de la API.
 *
 * @return array [Router, Container]
 */
require_once __DIR__ . '/../autoload.php';

use App\Core\Container;
use App\Core\Database;
use App\Core\Interfaces\InsumoRepositoryInterface;
use App\Core\Router;
use App\Controllers\AuditController;
use App\Controllers\AuthController;
use App\Controllers\ClienteController;
use App\Controllers\DashboardController;
use App\Controllers\EmployeeController;
use App\Controllers\EmpresaController;
use App\Controllers\GastoController;
use App\Controllers\InsumoController;
use App\Controllers\PedidoController;
use App\Controllers\PermisoController;
use App\Controllers\ProduccionController;
use App\Controllers\ProductoController;
use App\Controllers\ProveedorController;
use App\Controllers\ReporteController;
use App\Controllers\VentaController;
use App\Models\InsumoModel;

// --- Contenedor de dependencias ---
// PDO y las interfaces son los únicos bindings explícitos necesarios:
// el resto de clases (modelos y controladores) se resuelve con auto-wiring.
$container = new Container();

$container->set(\PDO::class, function () {
    return (new Database())->getConnection();
});

$container->set(InsumoRepositoryInterface::class, function ($c) {
    return new InsumoModel($c->get(\PDO::class));
});

// --- Rutas ---
// Controles por ruta:
//   $permiso = 'codigo'      -> solo usuarios con ese permiso (RBAC por permiso).
//   $roles   = null          -> pública, [] -> autenticado, [roles] -> RBAC legacy.
//   $methods = ['GET']|['POST']|['GET','POST'] -> validación HTTP 405.
$router = new Router();

// Autenticación (públicas)
// login / login_redirect procesan credenciales por POST:
// login responde JSON (usado por assets/js/login.js) y
// login_redirect redirige por HTTP (fallback del <form> de login.html).
$router->register('login', AuthController::class, 'login', true, null, ['POST']);
$router->register('login_redirect', AuthController::class, 'loginRedirect', false, null, ['POST']);
$router->register('logout', AuthController::class, 'logout', true, null, ['GET']);
$router->register('check_session', AuthController::class, 'checkSession', true, null, ['GET']);

// Inventario (Insumos)
$router->register('get_insumos', InsumoController::class, 'getAll', true, null, ['GET'], 'inventario.ver');
$router->register('add_insumo', InsumoController::class, 'add', true, null, ['POST'], 'inventario.gestionar');
$router->register('adjust_stock', InsumoController::class, 'adjustStock', true, null, ['POST'], 'inventario.gestionar');
$router->register('delete_insumo', InsumoController::class, 'delete', true, null, ['POST'], 'inventario.eliminar');
$router->register('toggle_insumo_visibility', InsumoController::class, 'toggleVisibility', true, null, ['POST'], 'inventario.gestionar');
$router->register('get_low_stock_alerts', InsumoController::class, 'getLowStock', true, null, ['GET'], 'inventario.ver');
$router->register('registrar_compra_insumo', InsumoController::class, 'registrarCompra', true, null, ['POST'], 'inventario.gestionar');

// Gastos
$router->register('get_gastos_by_date', GastoController::class, 'getByDate', true, null, ['GET'], 'gastos.ver');
$router->register('add_gasto', GastoController::class, 'add', true, null, ['POST'], 'gastos.gestionar');
$router->register('delete_gasto', GastoController::class, 'delete', true, null, ['POST'], 'gastos.gestionar');

// Reportes
$router->register('get_ventas_semanales', ReporteController::class, 'getVentasSemanales', true, null, ['GET'], 'reportes.ver');
$router->register('get_ventas_mensuales', ReporteController::class, 'getVentasMensuales', true, null, ['GET'], 'reportes.ver');

// Proveedores
$router->register('get_proveedores', ProveedorController::class, 'getAll', true, null, ['GET'], 'proveedores.ver');
$router->register('add_proveedor', ProveedorController::class, 'add', true, null, ['POST'], 'proveedores.gestionar');
$router->register('update_proveedor', ProveedorController::class, 'update', true, null, ['POST'], 'proveedores.gestionar');
$router->register('delete_proveedor', ProveedorController::class, 'delete', true, null, ['POST'], 'proveedores.gestionar');

// Productos
$router->register('get_productos', ProductoController::class, 'getAll', true, null, ['GET'], 'productos.ver');
$router->register('get_productos_by_categoria', ProductoController::class, 'getByCategoria', true, null, ['GET'], 'productos.ver');
$router->register('add_producto', ProductoController::class, 'add', true, null, ['POST'], 'productos.gestionar');
$router->register('delete_producto', ProductoController::class, 'delete', true, null, ['POST'], 'productos.eliminar');
$router->register('producir_producto', ProductoController::class, 'producir', true, null, ['POST'], 'productos.gestionar');

// Producción manual
$router->register('get_produccion_historial', ProduccionController::class, 'getAll', true, null, ['GET'], 'produccion.ver');
$router->register('add_produccion_manual', ProduccionController::class, 'create', true, null, ['POST'], 'produccion.gestionar');

// Clientes
$router->register('get_clientes', ClienteController::class, 'getAll', true, null, ['GET'], 'clientes.ver');
$router->register('add_cliente', ClienteController::class, 'add', true, null, ['POST'], 'clientes.gestionar');
$router->register('get_cliente_historial', ClienteController::class, 'getHistory', true, null, ['GET'], 'clientes.ver');
$router->register('update_cliente', ClienteController::class, 'update', true, null, ['POST'], 'clientes.gestionar');
$router->register('delete_cliente', ClienteController::class, 'delete', true, null, ['POST'], 'clientes.gestionar');

// Pedidos
$router->register('get_pedidos', PedidoController::class, 'getAll', true, null, ['GET'], 'pedidos.ver');
$router->register('add_pedido', PedidoController::class, 'create', true, null, ['POST'], 'pedidos.gestionar');
$router->register('update_pedido_estado', PedidoController::class, 'updateEstado', true, null, ['POST'], 'pedidos.gestionar');
$router->register('get_pedido_detalles', PedidoController::class, 'getDetalles', true, null, ['GET'], 'pedidos.ver');
$router->register('delete_pedido', PedidoController::class, 'delete', true, null, ['POST'], 'pedidos.gestionar');

// Ventas
$router->register('get_ventas', VentaController::class, 'getAll', true, null, ['GET'], 'ventas.ver');
$router->register('add_venta_directa', VentaController::class, 'createDirecta', true, null, ['POST'], 'ventas.gestionar');
$router->register('get_top_products', VentaController::class, 'getTopProducts', true, null, ['GET'], 'ventas.ver');
$router->register('get_revenue_chart', VentaController::class, 'getRevenueChart', true, null, ['GET'], 'ventas.ver');
$router->register('get_venta_detalles', VentaController::class, 'getDetalles', true, null, ['GET'], 'ventas.ver');
$router->register('cancel_venta', VentaController::class, 'cancel', true, null, ['POST'], 'ventas.gestionar');

// Dashboard
$router->register('get_dashboard_resumen', DashboardController::class, 'getResumen', true, null, ['GET'], 'dashboard.ver');

// Empleados
$router->register('get_employees', EmployeeController::class, 'getAll', true, null, ['GET'], 'empleados.ver');
$router->register('add_employee', EmployeeController::class, 'create', true, null, ['POST'], 'empleados.gestionar');
$router->register('delete_employee', EmployeeController::class, 'delete', true, null, ['POST'], 'empleados.gestionar');
$router->register('get_employee_stats', EmployeeController::class, 'getStats', true, null, ['GET'], 'empleados.ver');

// Perfil de la panadería (empresa)
$router->register('get_perfil_empresa', EmpresaController::class, 'getPerfil', true, null, ['GET'], 'perfil.gestionar');
$router->register('set_perfil_empresa', EmpresaController::class, 'updatePerfil', true, null, ['POST'], 'perfil.gestionar');
// Datos del negocio para la factura (accesible a cualquier usuario autenticado).
$router->register('get_datos_empresa', EmpresaController::class, 'getPerfil', true, [], ['GET']);

// Auditoría
$router->register('get_bitacora', AuditController::class, 'getBitacora', true, null, ['GET'], 'auditoria.ver');
$router->register('get_accesos_denegados', AuditController::class, 'getDenied', true, null, ['GET'], 'auditoria.ver');

// Permisos de roles (RBAC)
$router->register('get_permisos', PermisoController::class, 'getPermisos', true, null, ['GET'], 'permisos.gestionar');
$router->register('get_roles', PermisoController::class, 'getRoles', true, null, ['GET'], 'permisos.gestionar');
$router->register('get_permisos_rol', PermisoController::class, 'getPermisosRol', true, null, ['GET'], 'permisos.gestionar');
$router->register('set_permisos_rol', PermisoController::class, 'setPermisosRol', true, null, ['POST'], 'permisos.gestionar');
$router->register('crear_rol', PermisoController::class, 'crearRol', true, null, ['POST'], 'permisos.gestionar');
$router->register('editar_rol', PermisoController::class, 'editarRol', true, null, ['POST'], 'permisos.gestionar');
$router->register('eliminar_rol', PermisoController::class, 'eliminarRol', true, null, ['POST'], 'permisos.gestionar');

return [$router, $container];
