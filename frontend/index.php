<?php
// frontend/index.php
session_start();
require_once __DIR__ . '/includes/permisos.php';

if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

if (!tiene_permiso('dashboard.ver')) {
    header("Location: ventas.php");
    exit();
}

require_once __DIR__ . '/../backend/Helpers/DateFilterHelper.php';

$filter = $_GET['filter'] ?? 'today';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

$pageTitle = "Dashboard";
$pageHeader = "Panel de Control";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include 'includes/head.php'; ?>
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'includes/navbar.php'; ?>

            <div class="container-fluid p-4 animate-fade-in">
                
                <!-- Filtro de fechas -->
                <?php echo \App\Helpers\DateFilterHelper::getFilterUI($filter, $startDate, $endDate, 'index.php'); ?>

                <!-- Tarjetas Principales de Estadísticas -->
                <div class="row g-3 mb-4">
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="stat-card shadow-sm h-100 bg-primary-gradient">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-uppercase mb-1 fw-semibold small opacity-75">Ventas Periodo</p>
                                    <h2 class="mb-0 fw-bold" id="ventas-hoy">$0.00</h2>
                                </div>
                                <div class="bg-white bg-opacity-25 rounded-circle p-3">
                                    <i class="fas fa-chart-line fs-4 opacity-100 position-static"></i>
                                </div>
                            </div>
                            <p class="mt-3 mb-0 small opacity-90"><i class="fas fa-cash-register me-1"></i> Total recaudado</p>
                            <i class="fas fa-chart-line stat-bg-icon"></i>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="stat-card shadow-sm h-100 bg-success-gradient">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-uppercase mb-1 fw-semibold small opacity-75">Ganancias</p>
                                    <h2 class="mb-0 fw-bold" id="ganancias-hoy">$0.00</h2>
                                </div>
                                <div class="bg-white bg-opacity-25 rounded-circle p-3">
                                    <i class="fas fa-coins fs-4 opacity-100 position-static"></i>
                                </div>
                            </div>
                            <p class="mt-3 mb-0 small opacity-90"><i class="fas fa-hand-holding-usd me-1"></i> Utilidad neta estimada</p>
                            <i class="fas fa-coins stat-bg-icon"></i>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="stat-card shadow-sm h-100 bg-warning-gradient">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-uppercase mb-1 fw-semibold small opacity-75">Pedidos Pendientes</p>
                                    <h2 class="mb-0 fw-bold" id="pedidos-pendientes">0</h2>
                                </div>
                                <div class="bg-white bg-opacity-25 rounded-circle p-3">
                                    <i class="fas fa-clock fs-4 opacity-100 position-static"></i>
                                </div>
                            </div>
                            <p class="mt-3 mb-0 small opacity-90"><i class="fas fa-truck-loading me-1"></i> Por procesar / entregar</p>
                            <i class="fas fa-clock stat-bg-icon"></i>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="stat-card shadow-sm h-100 bg-info-gradient">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-uppercase mb-1 fw-semibold small opacity-75">Catálogo Activo</p>
                                    <h2 class="mb-0 fw-bold" id="productos-catalogo">0</h2>
                                </div>
                                <div class="bg-white bg-opacity-25 rounded-circle p-3">
                                    <i class="fas fa-bread-slice fs-4 opacity-100 position-static"></i>
                                </div>
                            </div>
                            <p class="mt-3 mb-0 small opacity-90"><i class="fas fa-boxes me-1"></i> Productos en venta</p>
                            <i class="fas fa-bread-slice stat-bg-icon"></i>
                        </div>
                    </div>
                </div>

                <!-- KPIs Operativos / CMMI -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="card p-3 text-center border-0 shadow-sm">
                            <i class="fas fa-users text-primary fs-4 mb-2"></i>
                            <div class="fw-bold fs-5 text-dark" id="clientes-registrados">0</div>
                            <small class="text-muted">Clientes</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="card p-3 text-center border-0 shadow-sm">
                            <i class="fas fa-user-check text-success fs-4 mb-2"></i>
                            <div class="fw-bold fs-5 text-dark" id="kpi-usuarios">0</div>
                            <small class="text-muted">Usuarios Activos</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="card p-3 text-center border-0 shadow-sm">
                            <i class="fas fa-receipt text-info fs-4 mb-2"></i>
                            <div class="fw-bold fs-5 text-dark" id="kpi-ventas-qty">0</div>
                            <small class="text-muted">Tickets Emitidos</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="card p-3 text-center border-0 shadow-sm">
                            <i class="fas fa-industry text-warning fs-4 mb-2"></i>
                            <div class="fw-bold fs-5 text-dark" id="kpi-produccion-qty">0</div>
                            <small class="text-muted">Lotes Horneados</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="card p-3 text-center border-0 shadow-sm">
                            <i class="fas fa-clipboard-list text-secondary fs-4 mb-2"></i>
                            <div class="fw-bold fs-5 text-dark" id="kpi-eventos">0</div>
                            <small class="text-muted">Eventos Bitácora</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="card p-3 text-center border-0 shadow-sm">
                            <i class="fas fa-exclamation-circle text-danger fs-4 mb-2"></i>
                            <div class="fw-bold fs-5 text-dark" id="kpi-errores">0</div>
                            <small class="text-muted">Incidencias</small>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Tabla de Últimos Pedidos -->
                    <div class="col-12 col-xl-8">
                        <div class="card shadow-sm h-100">
                            <div class="card-header d-flex justify-content-between align-items-center bg-white">
                                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-shopping-cart text-primary me-2"></i>Pedidos Recientes</h5>
                                <a href="pedidos.php" class="btn btn-sm btn-outline-primary">Ver todos</a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>N.º</th>
                                                <th>Cliente</th>
                                                <th>Fecha</th>
                                                <th>Estado</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody id="ultimos-pedidos-body">
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">
                                                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                                    Cargando pedidos...
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Alertas de Stock -->
                    <div class="col-12 col-xl-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-exclamation-triangle text-danger me-2"></i>Alertas de Stock</h5>
                                <a href="inventario.php" class="btn btn-sm btn-link text-decoration-none p-0 text-muted small">Ver inventario</a>
                            </div>
                            <div class="card-body" id="alertas-stock-container">
                                <div class="text-center py-4 text-muted">
                                    <div class="spinner-border spinner-border-sm text-danger me-2" role="status"></div>
                                    Verificando inventario...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Scripts -->
    <?php include 'includes/footer.php'; ?>
    <script src="../assets/js/app.js"></script>
</body>
</html>
