<?php
// Reporte de errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración robusta de sesiones
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$route = $_GET['route'] ?? '';

switch ($route) {
    case 'login':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/AuthController.php';
        $auth = new AuthController();
        $auth->login();
        break;
    case 'login_redirect':
        // No enviamos Content-Type JSON aquí porque vamos a redireccionar
        require_once __DIR__ . '/Controllers/AuthController.php';
        $auth = new AuthController();
        $auth->loginRedirect();
        break;
    case 'logout':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/AuthController.php';
        $auth = new AuthController();
        $auth->logout();
        break;
        
    // --- Rutas de Inventario (Insumos) ---
    case 'get_insumos':
    case 'add_insumo':
    case 'adjust_stock':
    case 'delete_insumo':
    case 'toggle_insumo_visibility':
    case 'get_low_stock_alerts':
    case 'registrar_compra_insumo':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/InsumoController.php';
        require_once __DIR__ . '/Models/InsumoModel.php';
        require_once __DIR__ . '/../config/database.php';
        
        $db = (new Database())->getConnection();
        $repo = new InsumoModel($db);
        $controller = new InsumoController($repo);
        
        if ($route == 'get_insumos') $controller->getAll();
        if ($route == 'add_insumo') $controller->add();
        if ($route == 'adjust_stock') $controller->adjustStock();
        if ($route == 'delete_insumo') $controller->delete();
        if ($route == 'toggle_insumo_visibility') $controller->toggleVisibility();
        if ($route == 'get_low_stock_alerts') $controller->getLowStock();
        if ($route == 'registrar_compra_insumo') $controller->registrarCompra();
        break;

    // --- Rutas de Gastos ---
    case 'get_gastos_by_date':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/GastoController.php';
        (new GastoController())->getByDate();
        break;
    case 'add_gasto':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/GastoController.php';
        (new GastoController())->add();
        break;
    case 'delete_gasto':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/GastoController.php';
        (new GastoController())->delete();
        break;

    // --- Rutas de Reportes ---
    case 'get_ventas_semanales':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/ReporteController.php';
        (new ReporteController())->getVentasSemanales();
        break;
    case 'get_ventas_mensuales':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/ReporteController.php';
        (new ReporteController())->getVentasMensuales();
        break;

    // --- Rutas de Proveedores ---
    case 'get_proveedores':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/ProveedorController.php';
        (new ProveedorController())->getAll();
        break;
    case 'add_proveedor':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/ProveedorController.php';
        (new ProveedorController())->add();
        break;
    case 'update_proveedor':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/ProveedorController.php';
        (new ProveedorController())->update();
        break;
    case 'delete_proveedor':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/ProveedorController.php';
        (new ProveedorController())->delete();
        break;
        
    // --- Rutas de Productos ---
    case 'get_productos':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/ProductoController.php';
        (new ProductoController())->getAll();
        break;
    case 'get_productos_by_categoria':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/ProductoController.php';
        (new ProductoController())->getByCategoria();
        break;
    case 'add_producto':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/ProductoController.php';
        (new ProductoController())->add();
        break;
    case 'delete_producto':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/ProductoController.php';
        (new ProductoController())->delete();
        break;

    // Registra una producción: descuenta insumos y suma stock al producto
    case 'producir_producto':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/ProductoController.php';
        (new ProductoController())->producir();
        break;

    // --- Rutas de Producción Manual ---
    case 'get_produccion_historial':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/ProduccionController.php';
        (new ProduccionController())->getAll();
        break;
    case 'add_produccion_manual':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/ProduccionController.php';
        (new ProduccionController())->create();
        break;


    // --- Rutas de Clientes ---
    case 'get_clientes':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/ClienteController.php';
        (new ClienteController())->getAll();
        break;
    case 'add_cliente':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/ClienteController.php';
        (new ClienteController())->add();
        break;
    case 'get_cliente_historial':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/ClienteController.php';
        (new ClienteController())->getHistory();
        break;
    case 'update_cliente':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/ClienteController.php';
        (new ClienteController())->update();
        break;
    case 'delete_cliente':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/ClienteController.php';
        (new ClienteController())->delete();
        break;

    // --- Rutas de Pedidos y Ventas ---
    case 'get_pedidos':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/PedidoController.php';
        (new PedidoController())->getAll();
        break;
    case 'add_pedido':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/PedidoController.php';
        (new PedidoController())->create();
        break;
    case 'update_pedido_estado':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/PedidoController.php';
        (new PedidoController())->updateEstado();
        break;
    case 'get_pedido_detalles':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/PedidoController.php';
        (new PedidoController())->getDetalles();
        break;
    case 'delete_pedido':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/PedidoController.php';
        (new PedidoController())->delete();
        break;
    case 'get_ventas':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/VentaController.php';
        (new VentaController())->getAll();
        break;
    case 'add_venta_directa':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/VentaController.php';
        (new VentaController())->createDirecta();
        break;
    case 'get_top_products':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/VentaController.php';
        (new VentaController())->getTopProducts();
        break;
    case 'get_revenue_chart':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/VentaController.php';
        (new VentaController())->getRevenueChart();
        break;
    case 'get_venta_detalles':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/VentaController.php';
        (new VentaController())->getDetalles();
        break;
    case 'cancel_venta':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/VentaController.php';
        (new VentaController())->cancel();
        break;

    // --- Rutas de Dashboard ---
    case 'get_dashboard_resumen':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/DashboardController.php';
        (new DashboardController())->getResumen();
        break;

    // --- Rutas de Empleados ---
    case 'get_employees':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/EmployeeController.php';
        (new EmployeeController())->getAll();
        break;
    case 'add_employee':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/EmployeeController.php';
        (new EmployeeController())->create();
        break;
    case 'delete_employee':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/EmployeeController.php';
        (new EmployeeController())->delete();
        break;
    case 'get_employee_stats':
        header('Content-Type: application/json');
        require_once __DIR__ . '/Controllers/EmployeeController.php';
        (new EmployeeController())->getStats();
        break;

    case 'check_session':
        header('Content-Type: application/json');
        echo json_encode([
            'logged_in' => isset($_SESSION['usuario_id']),
            'usuario' => $_SESSION['usuario'] ?? null,
            'user' => [
                'id' => $_SESSION['usuario_id'] ?? null,
                'nombre' => $_SESSION['nombre'] ?? null,
                'rol' => $_SESSION['rol'] ?? null
            ]
        ]);
        break;

    default:
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Endpoint no válido o no especificado.']);
        break;
}
