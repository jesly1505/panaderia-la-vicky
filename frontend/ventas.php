<?php
// frontend/ventas.php
session_start();
require_once __DIR__ . '/includes/permisos.php';

if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

if (!tiene_permiso('ventas.ver')) {
    header("Location: index.php");
    exit();
}

$pageTitle = "Ventas";
$pageHeader = "Punto de Venta y Finanzas";

require_once __DIR__ . '/../backend/Helpers/DateFilterHelper.php';
$filter = $_GET['filter'] ?? 'all';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include 'includes/head.php'; ?>
    <style>
        .stat-card-premium { border: none; border-radius: var(--radius-md); overflow: hidden; position: relative; color: white; transition: var(--transition); }
        .stat-card-premium:hover { transform: translateY(-3px); }
        .stat-card-premium .card-body { padding: 1.5rem; z-index: 1; position: relative; }
        .stat-card-premium i { position: absolute; right: 1rem; bottom: 1rem; font-size: 3rem; opacity: 0.15; }
        .bg-income { background: linear-gradient(135deg, #c0560f 0%, #e07a34 100%); }
        .bg-profit { background: linear-gradient(135deg, #1d976c 0%, #38ef7d 100%); }
        
        .pos-card { border: none; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); }
        .cart-list-container { max-height: 380px; overflow-y: auto; background: var(--light); border-radius: var(--radius-sm); }
        .payment-pill { font-size: 0.75rem; font-weight: 600; padding: 0.4em 0.8em; border-radius: 4px; border: 1px solid transparent; }
        .client-item-hover:hover { background-color: #f8f9fa; cursor: pointer; }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <div class="main-content">
            <?php include 'includes/navbar.php'; ?>

            <div class="container-fluid p-4 animate-fade-in">
                <!-- Stats Row -->
                <div class="row g-4 mb-4">
                    <div class="col-12 col-md-6">
                        <div class="card stat-card-premium bg-income shadow-sm">
                            <div class="card-body">
                                <h6 class="text-uppercase small fw-bold opacity-75 mb-2">Ingresos del Periodo</h6>
                                <h2 class="mb-0 fw-bold" id="totalRevenue">$0.00</h2>
                                <i class="fas fa-hand-holding-usd"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="card stat-card-premium bg-profit shadow-sm">
                            <div class="card-body">
                                <h6 class="text-uppercase small fw-bold opacity-75 mb-2">Ganancias Estimadas</h6>
                                <h2 class="mb-0 fw-bold" id="totalProfit">$0.00</h2>
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sales History Section (Full Width Main Component) -->
                <div class="row g-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-history me-2 text-primary"></i>Historial de Ventas</h5>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-secondary shadow-sm text-nowrap" onclick="loadSalesHistory(currentPage)">
                                            <i class="fas fa-sync-alt me-1"></i> Actualizar
                                        </button>
                                        <?php if (tiene_permiso('ventas.gestionar')): ?>
                                            <button class="btn btn-sm btn-primary fw-bold px-3 shadow-sm text-nowrap" onclick="openClientSelectionModal()">
                                                <i class="fas fa-plus me-1"></i> + Nueva Venta
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Buscador y Filtros -->
                                <div class="d-flex flex-wrap align-items-center gap-2 pt-2 border-top">
                                    <!-- Buscador -->
                                    <div class="input-group input-group-sm" style="min-width: 220px; max-width: 280px;">
                                        <span class="input-group-text bg-light border-end-0 text-muted">
                                            <i class="fas fa-search"></i>
                                        </span>
                                        <input type="text" id="ventaSearchInput" class="form-control border-start-0 border-end-0 ps-0" placeholder="Buscar venta..." oninput="handleVentaSearch()" autocomplete="off">
                                        <button class="btn btn-outline-secondary border-start-0 text-muted" type="button" id="btnClearVentaSearch" onclick="clearVentaSearch()" title="Limpiar búsqueda" style="display: none;">
                                            ✕
                                        </button>
                                    </div>

                                    <!-- Filtro Período -->
                                    <div style="min-width: 140px;">
                                        <select id="filterPeriodo" class="form-select form-select-sm" onchange="onPeriodoChange()">
                                            <option value="all">Período: Todo el historial</option>
                                            <option value="hoy">Período: Hoy</option>
                                            <option value="semana">Período: Esta semana</option>
                                            <option value="mes">Período: Este mes</option>
                                            <option value="custom">Período: Personalizado</option>
                                        </select>
                                    </div>

                                    <!-- Fechas personalizadas (ocultas por defecto) -->
                                    <div id="customDateRange" class="d-flex align-items-center gap-1" style="display: none !important;">
                                        <input type="date" id="filterStartDate" class="form-control form-control-sm" style="width: 130px;" onchange="applyFilters()">
                                        <span class="text-muted small">a</span>
                                        <input type="date" id="filterEndDate" class="form-control form-control-sm" style="width: 130px;" onchange="applyFilters()">
                                    </div>

                                    <!-- Filtro Estado -->
                                    <div style="min-width: 130px;">
                                        <select id="filterEstado" class="form-select form-select-sm" onchange="applyFilters()">
                                            <option value="all">Estado: Todos</option>
                                            <option value="completado">Completada</option>
                                            <option value="cancelado">Anulada</option>
                                            <option value="pendiente">Pendiente</option>
                                        </select>
                                    </div>

                                    <!-- Filtro Tipo de Pago -->
                                    <div style="min-width: 140px;">
                                        <select id="filterTipoPago" class="form-select form-select-sm" onchange="applyFilters()">
                                            <option value="all">Tipo de pago: Todos</option>
                                            <option value="efectivo">Efectivo</option>
                                            <option value="tarjeta">Tarjeta</option>
                                            <option value="transferencia">Transferencia</option>
                                            <option value="wallet">Wallet</option>
                                        </select>
                                    </div>

                                    <!-- Filtro Vendedor -->
                                    <div style="min-width: 140px;">
                                        <select id="filterVendedor" class="form-select form-select-sm" onchange="applyFilters()">
                                            <option value="all">Vendedor: Todos</option>
                                        </select>
                                    </div>

                                    <!-- Botón Limpiar Filtros -->
                                    <div>
                                        <button type="button" class="btn btn-sm btn-outline-secondary text-nowrap" onclick="resetAllFilters()">
                                            <i class="fas fa-undo me-1"></i> Limpiar filtros
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="ps-4">N.º</th>
                                                <th>Fecha y Hora</th>
                                                <th>Cliente</th>
                                                <th>Vendedor</th>
                                                <th>Tipo Pago</th>
                                                <th>Total</th>
                                                <th>Ganancia</th>
                                                <th>Estado</th>
                                                <th class="text-end pe-4">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="salesTableBody">
                                            <tr>
                                                <td colspan="9" class="text-center py-5 text-muted">
                                                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                                    Cargando ventas...
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- Pagination container -->
                                <div class="p-3 border-top d-flex justify-content-end" id="salesPagination"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Seleccionar Cliente Previo a la Venta -->
    <div class="modal fade" id="seleccionarClienteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 py-3">
                    <h5 class="modal-title fw-bold"><i class="fas fa-user-check me-2"></i>Seleccionar Cliente para la Venta</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Opción Consumidor Final -->
                    <div class="card border-primary border-opacity-25 bg-primary bg-opacity-10 mb-4 cursor-pointer p-3 client-item-hover rounded shadow-sm" onclick="selectClientForSale(null)">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-primary text-white p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                    <i class="fas fa-user-tag fs-5"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold text-primary">Consumidor Final</h6>
                                    <small class="text-muted">Venta rápida al público general sin asociar cliente registrado</small>
                                </div>
                            </div>
                            <span class="btn btn-sm btn-primary fw-semibold px-3">Seleccionar &raquo;</span>
                        </div>
                    </div>

                    <!-- Clientes Registrados Header & Buscador -->
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-2 gap-2">
                        <label class="fw-bold small text-uppercase text-muted mb-0">Clientes Registrados</label>
                        <button type="button" class="btn btn-sm btn-outline-success fw-semibold" id="btnToggleNewClient" onclick="toggleNewClientForm()">
                            <i class="fas fa-user-plus me-1"></i> + Agregar nuevo cliente
                        </button>
                    </div>

                    <div class="input-group mb-3 shadow-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="clientSearchInput" class="form-control border-start-0 py-2" placeholder="Buscar por Nombre, DNI o Teléfono..." oninput="filterClientList()">
                    </div>

                    <!-- Formulario Integrado: Agregar Nuevo Cliente (Colapsable) -->
                    <div id="newClientFormCard" class="card border-success border-opacity-50 mb-3 bg-light shadow-sm" style="display: none;">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                <h6 class="fw-bold text-success mb-0"><i class="fas fa-user-plus me-1"></i> Registrar Nuevo Cliente</h6>
                                <button type="button" class="btn-close btn-sm" onclick="toggleNewClientForm(false)" aria-label="Cerrar"></button>
                            </div>
                            <form id="quickAddClientForm" onsubmit="saveQuickClient(event)">
                                <div class="row g-2 mb-2">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-semibold">Nombre Completo *</label>
                                        <input type="text" name="nombre" class="form-control form-control-sm" required maxlength="100">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-semibold">DNI / Identificación *</label>
                                        <input type="text" name="dni" class="form-control form-control-sm" required maxlength="20">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-semibold">Teléfono</label>
                                        <input type="text" name="telefono" class="form-control form-control-sm" maxlength="30">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-semibold">Correo Electrónico</label>
                                        <input type="email" name="email" class="form-control form-control-sm" maxlength="100">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">Dirección</label>
                                        <input type="text" name="direccion" class="form-control form-control-sm" maxlength="255">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end gap-2 mt-3">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleNewClientForm(false)">Cancelar</button>
                                    <button type="submit" class="btn btn-sm btn-success fw-bold" id="btnSaveClient">
                                        <i class="fas fa-check me-1"></i> Guardar y Seleccionar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Lista de Clientes Registrados -->
                    <div class="card border rounded p-0 overflow-hidden">
                        <div class="list-group list-group-flush" id="clientsListGroup" style="max-height: 280px; overflow-y: auto;">
                            <div class="text-center py-4 text-muted">
                                <div class="spinner-border spinner-border-sm me-1"></div> Cargando clientes...
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 p-3">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nueva Venta Directa -->
    <div class="modal fade" id="nuevaVentaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 py-3">
                    <h5 class="modal-title fw-bold"><i class="fas fa-cash-register me-2"></i>Nueva Venta Directa</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Banner del Cliente Seleccionado -->
                    <div class="card bg-light border p-2 px-3 mb-3 d-flex flex-row justify-content-between align-items-center rounded shadow-sm">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-sm bg-primary text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark mb-0" id="saleClientName">Consumidor Final</div>
                                <small class="text-muted" id="saleClientDetails">Venta Directa</small>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary fw-semibold" onclick="openClientSelectionModal()">
                            <i class="fas fa-exchange-alt me-1"></i> Cambiar Cliente
                        </button>
                    </div>

                    <div class="row g-4">
                        <!-- Left Column: Product Selection & Cart -->
                        <div class="col-12 col-lg-6">
                            <!-- Product Picker -->
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-uppercase text-muted">Añadir al Carrito</label>
                                <div class="input-group shadow-sm">
                                    <select id="productoSelect" class="form-select py-2">
                                        <option value="" disabled selected>Seleccione producto...</option>
                                    </select>
                                    <button class="btn btn-primary px-3" type="button" onclick="addToCart()">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Cart List -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold small text-uppercase text-muted">Detalle de Compra</span>
                                    <span class="badge bg-light text-dark border" id="itemCount">0 items</span>
                                </div>
                                <div class="cart-list-container border rounded p-2" style="max-height: 320px; overflow-y: auto;">
                                    <ul class="list-group list-group-flush border-0" id="cartList">
                                        <li class="list-group-item bg-transparent text-muted text-center py-5 small italic">
                                            No hay productos en el carrito
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Summary & Payments -->
                        <div class="col-12 col-lg-6">
                            <!-- Summary -->
                            <div class="bg-light p-3 rounded mb-3 border">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small">Subtotal:</span>
                                    <span class="fw-bold" id="subtotalDisplay">$0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2 text-primary">
                                    <span class="small">Descuentos por Ítem:</span>
                                    <span class="fw-bold" id="itemDiscountDisplay">-$0.00</span>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-7"><span class="text-muted small">Descuento Global ($):</span></div>
                                    <div class="col-5">
                                        <input type="number" id="globalDiscount" class="form-control form-control-sm text-end fw-bold" value="0.00" step="0.01" min="0" oninput="renderCart()">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small" id="taxLabel">IVA (15%):</span>
                                    <span class="fw-bold" id="taxDisplay">$0.00</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top border-2 border-primary border-opacity-10">
                                    <span class="fw-bold text-dark h5 mb-0">TOTAL:</span>
                                    <h3 class="mb-0 fw-bold text-primary" id="cartTotal">$0.00</h3>
                                </div>
                            </div>

                            <!-- Payments -->
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-uppercase text-muted d-block mb-2">Método de Pago</label>
                                <div id="paymentsList" class="mb-2"></div>
                                
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <select id="paymentMethod" class="form-select py-2">
                                            <option value="efectivo">💵 Efectivo</option>
                                            <option value="tarjeta">💳 Tarjeta</option>
                                            <option value="transferencia">🏦 Transferencia</option>
                                            <option value="otro">📱 Otro</option>
                                        </select>
                                    </div>
                                    <div class="col-4">
                                        <input type="number" id="paymentAmount" class="form-control py-2" placeholder="0.00" step="0.01">
                                    </div>
                                    <div class="col-2">
                                        <button class="btn btn-outline-primary w-100 py-2" onclick="addPaymentRow()">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>

                                <div id="balanceContainer" class="p-2 bg-white border rounded mb-2" style="display: none;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="small fw-bold text-muted" id="balanceLabel">Cambio / Vuelto:</span>
                                        <span class="fw-bold text-success fs-5" id="balanceDisplay">$0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-light rounded-bottom d-flex justify-content-between">
                    <button type="button" class="btn btn-link link-secondary text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-primary px-4 py-2 fw-bold text-uppercase shadow-sm" id="btnCheckout" onclick="processCheckout()">
                        <i class="fas fa-check-circle me-2"></i> Cobrar y Emitir Factura
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Sale Details Modal -->
    <div class="modal fade" id="saleDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="saleDetailsTitle">Detalles de Venta #</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block text-uppercase fw-bold">Fecha</small>
                            <span id="dtFecha" class="fw-semibold text-dark">-</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block text-uppercase fw-bold">Cliente</small>
                            <span id="dtCliente" class="fw-semibold text-dark">-</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block text-uppercase fw-bold">Vendedor</small>
                            <span id="dtVendedor" class="fw-semibold text-dark">-</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block text-uppercase fw-bold">Estado</small>
                            <span id="dtEstado" class="badge bg-success">-</span>
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3 border-bottom pb-2">Artículos Vendidos</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-sm align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-center">Cant.</th>
                                    <th class="text-end">P. Unit</th>
                                    <th class="text-end">Desc.</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="dtProductsList"></tbody>
                        </table>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <h6 class="fw-bold mb-2">Desglose de Pagos</h6>
                            <ul class="list-group list-group-flush" id="dtPaymentsList"></ul>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light p-3 rounded">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-muted">Subtotal:</small>
                                    <span id="dtSubtotal" class="fw-semibold">$0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-muted">Descuento:</small>
                                    <span id="dtDescuento" class="text-danger fw-semibold">-$0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-muted">Impuestos:</small>
                                    <span id="dtImpuestos" class="fw-semibold">$0.00</span>
                                </div>
                                <div class="d-flex justify-content-between pt-2 border-top">
                                    <strong class="text-dark">Total:</strong>
                                    <strong id="dtTotal" class="text-primary fs-5">$0.00</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <a href="#" id="btnPrintInvoice" target="_blank" class="btn btn-outline-primary">
                        <i class="fas fa-print me-1"></i> Imprimir Factura
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <?php include 'includes/footer.php'; ?>
    <script>
        let availableProducts = [];
        let allClients = [];
        let selectedClient = null;
        let cart = [];
        let payments = [];
        let currentTaxRate = 0.15;
        let currentPage = 1;
        const itemsPerPage = 10;

        let searchTimeout = null;

        document.addEventListener('DOMContentLoaded', async () => {
            await fetchCompanyInfo();
            await loadProducts();
            await loadClients();
            await loadVendedoresSelect();
            await loadSalesHistory(1);

            if (typeof tienePermiso === 'function' && !tienePermiso('ventas.gestionar')) {
                const btnCheckout = document.getElementById('btnCheckout');
                if (btnCheckout) {
                    btnCheckout.disabled = true;
                    btnCheckout.title = 'No dispone de permisos para registrar ventas.';
                }
            }
        });

        async function fetchCompanyInfo() {
            try {
                const res = await fetch('../backend/api.php?route=get_datos_empresa');
                const data = await res.json();
                if (data.success && data.data) {
                    if (data.data.impuesto_porcentaje !== undefined) {
                        currentTaxRate = parseFloat(data.data.impuesto_porcentaje) / 100;
                        const taxLabel = document.getElementById('taxLabel');
                        if (taxLabel) taxLabel.textContent = `IVA (${parseFloat(data.data.impuesto_porcentaje)}%):`;
                    }
                }
            } catch (e) {
                console.log('Using default tax settings');
            }
        }

        async function loadProducts() {
            try {
                const res = await fetch('../backend/api.php?route=get_productos');
                const data = await res.json();
                if (data.success) {
                    availableProducts = data.data;
                    const select = document.getElementById('productoSelect');
                    select.innerHTML = '<option value="" disabled selected>Seleccione producto...</option>';
                    availableProducts.forEach(p => {
                        select.innerHTML += `<option value="${p.id}">${escapeHtml(p.nombre)} - ${formatCurrency(p.precio_venta)} (Stock: ${p.stock_actual})</option>`;
                    });
                }
            } catch (e) {
                console.error('Error fetching products:', e);
            }
        }

        async function loadClients() {
            try {
                const res = await fetch('../backend/api.php?route=get_clientes&limit=500');
                const data = await res.json();
                if (data.success) {
                    allClients = data.data || [];
                    renderClientsList(allClients);
                }
            } catch (e) {
                console.error('Error loading clients:', e);
            }
        }

        function renderClientsList(clients) {
            const listGroup = document.getElementById('clientsListGroup');
            if (!listGroup) return;
            listGroup.innerHTML = '';

            if (!clients || clients.length === 0) {
                listGroup.innerHTML = `
                    <div class="text-center py-4 text-muted small">
                        <i class="fas fa-user-slash fa-2x mb-2 d-block opacity-50"></i>
                        No se encontraron clientes que coincidan con la búsqueda.
                    </div>
                `;
                return;
            }

            clients.forEach(c => {
                const dniBadge = c.dni ? `<span class="badge bg-light text-dark border me-2">DNI: ${escapeHtml(c.dni)}</span>` : '';
                const tel = c.telefono ? `<small class="text-muted me-3"><i class="fas fa-phone-alt me-1"></i>${escapeHtml(c.telefono)}</small>` : '';
                const email = c.email ? `<small class="text-muted"><i class="fas fa-envelope me-1"></i>${escapeHtml(c.email)}</small>` : '';

                listGroup.innerHTML += `
                    <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-3 client-item-hover border-bottom" onclick="selectClientForSale(${c.id})">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-secondary bg-opacity-10 text-secondary p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark mb-1">${escapeHtml(c.nombre)}</div>
                                <div class="d-flex flex-wrap align-items-center">
                                    ${dniBadge}
                                    ${tel}
                                    ${email}
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary fw-semibold px-3" onclick="event.stopPropagation(); selectClientForSale(${c.id})">
                            Seleccionar
                        </button>
                    </div>
                `;
            });
        }

        function filterClientList() {
            const query = (document.getElementById('clientSearchInput').value || '').trim().toLowerCase();
            if (!query) {
                renderClientsList(allClients);
                return;
            }

            const filtered = allClients.filter(c => {
                const name = (c.nombre || '').toLowerCase();
                const dni = (c.dni || '').toLowerCase();
                const tel = (c.telefono || '').toLowerCase();
                return name.includes(query) || dni.includes(query) || tel.includes(query);
            });

            renderClientsList(filtered);
        }

        function toggleNewClientForm(show = null) {
            const formCard = document.getElementById('newClientFormCard');
            const btnToggle = document.getElementById('btnToggleNewClient');
            if (show === null) {
                show = formCard.style.display === 'none';
            }

            if (show) {
                formCard.style.display = 'block';
                btnToggle.innerHTML = '<i class="fas fa-times me-1"></i> Cancelar registro';
                btnToggle.className = 'btn btn-sm btn-outline-danger fw-semibold';
                const firstInput = formCard.querySelector('input[name="nombre"]');
                if (firstInput) firstInput.focus();
            } else {
                formCard.style.display = 'none';
                btnToggle.innerHTML = '<i class="fas fa-user-plus me-1"></i> + Agregar nuevo cliente';
                btnToggle.className = 'btn btn-sm btn-outline-success fw-semibold';
                document.getElementById('quickAddClientForm').reset();
            }
        }

        async function saveQuickClient(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const payload = Object.fromEntries(formData);

            const btn = document.getElementById('btnSaveClient');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';

            try {
                const res = await fetch('../backend/api.php?route=add_cliente', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    showAlert('Cliente registrado con éxito', 'success');
                    await loadClients();
                    toggleNewClientForm(false);
                    const newId = data.cliente_id || data.id;
                    const createdClient = allClients.find(c => c.id == newId) || {
                        id: newId,
                        nombre: payload.nombre,
                        dni: payload.dni,
                        telefono: payload.telefono,
                        email: payload.email
                    };
                    selectClientForSale(createdClient);
                } else {
                    showAlert(data.message || 'Error al registrar cliente', 'error');
                }
            } catch (err) {
                console.error(err);
                showAlert('Error de conexión al registrar cliente', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check me-1"></i> Guardar y Seleccionar';
            }
        }

        function openClientSelectionModal() {
            const nuevaVentaModalEl = document.getElementById('nuevaVentaModal');
            const nvModal = bootstrap.Modal.getInstance(nuevaVentaModalEl);
            if (nvModal) nvModal.hide();

            document.getElementById('clientSearchInput').value = '';
            toggleNewClientForm(false);
            renderClientsList(allClients);

            const clientModal = new bootstrap.Modal(document.getElementById('seleccionarClienteModal'));
            clientModal.show();
        }

        function selectClientForSale(clientOrId) {
            if (clientOrId === null) {
                selectedClient = null;
            } else if (typeof clientOrId === 'object') {
                selectedClient = clientOrId;
            } else {
                selectedClient = allClients.find(c => c.id == clientOrId) || null;
            }

            const nameEl = document.getElementById('saleClientName');
            const detailsEl = document.getElementById('saleClientDetails');

            if (selectedClient) {
                nameEl.textContent = selectedClient.nombre;
                const details = [];
                if (selectedClient.dni) details.push(`DNI: ${selectedClient.dni}`);
                if (selectedClient.telefono) details.push(`Tel: ${selectedClient.telefono}`);
                detailsEl.textContent = details.length > 0 ? details.join(' | ') : 'Cliente Registrado';
            } else {
                nameEl.textContent = 'Consumidor Final';
                detailsEl.textContent = 'Venta Directa';
            }

            const clientModalEl = document.getElementById('seleccionarClienteModal');
            const clientModal = bootstrap.Modal.getInstance(clientModalEl);
            if (clientModal) clientModal.hide();

            const nvModal = new bootstrap.Modal(document.getElementById('nuevaVentaModal'));
            nvModal.show();
        }

        function addToCart() {
            const select = document.getElementById('productoSelect');
            const prodId = parseInt(select.value);
            if (!prodId) return;

            const product = availableProducts.find(p => p.id == prodId);
            if (!product) return;

            if (product.stock_actual <= 0) {
                showAlert('¡El producto seleccionado no tiene stock disponible!', 'warning');
                return;
            }

            const existing = cart.find(item => item.id == prodId);
            if (existing) {
                if (existing.cantidad + 1 > product.stock_actual) {
                    showAlert('No hay suficiente stock para añadir más unidades.', 'warning');
                    return;
                }
                existing.cantidad++;
            } else {
                cart.push({
                    id: product.id,
                    nombre: product.nombre,
                    precio: parseFloat(product.precio_venta),
                    cantidad: 1,
                    descuento: 0,
                    stock_max: product.stock_actual
                });
            }

            renderCart();
        }

        function updateCartQty(index, delta) {
            const item = cart[index];
            if (!item) return;

            const newQty = item.cantidad + delta;
            if (newQty <= 0) {
                cart.splice(index, 1);
            } else if (newQty > item.stock_max) {
                showAlert('Stock máximo alcanzado para este producto.', 'warning');
                return;
            } else {
                item.cantidad = newQty;
            }
            renderCart();
        }

        function updateCartDiscount(index, discountVal) {
            const item = cart[index];
            if (!item) return;
            item.descuento = Math.max(0, parseFloat(discountVal) || 0);
            renderCart();
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            renderCart();
        }

        function renderCart() {
            const list = document.getElementById('cartList');
            const itemCount = document.getElementById('itemCount');
            list.innerHTML = '';

            let subtotal = 0;
            let totalItemDiscounts = 0;
            let totalItems = 0;

            if (cart.length === 0) {
                list.innerHTML = `<li class="list-group-item bg-transparent text-muted text-center py-5 small italic">No hay productos en el carrito</li>`;
                itemCount.textContent = '0 items';
            } else {
                cart.forEach((item, index) => {
                    const itemSubtotal = (item.precio * item.cantidad) - item.descuento;
                    subtotal += item.precio * item.cantidad;
                    totalItemDiscounts += item.descuento;
                    totalItems += item.cantidad;

                    list.innerHTML += `
                        <li class="list-group-item bg-white border-bottom p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-semibold text-dark">${escapeHtml(item.nombre)}</span>
                                <span class="fw-bold text-primary">${formatCurrency(itemSubtotal)}</span>
                            </div>
                            <div class="row g-2 align-items-center">
                                <div class="col-5">
                                    <div class="input-group input-group-sm">
                                        <button class="btn btn-outline-secondary" onclick="updateCartQty(${index}, -1)">-</button>
                                        <input type="text" class="form-control text-center bg-light" value="${item.cantidad}" readonly>
                                        <button class="btn btn-outline-secondary" onclick="updateCartQty(${index}, 1)">+</button>
                                    </div>
                                </div>
                                <div class="col-5">
                                    <input type="number" class="form-control form-control-sm" placeholder="Desc $" value="${item.descuento || ''}" onchange="updateCartDiscount(${index}, this.value)">
                                </div>
                                <div class="col-2 text-end">
                                    <button class="btn btn-sm btn-link text-danger p-0" onclick="removeFromCart(${index})">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </li>
                    `;
                });
                itemCount.textContent = `${totalItems} item(s)`;
            }

            const globalDiscount = Math.max(0, parseFloat(document.getElementById('globalDiscount').value) || 0);
            const taxableSubtotal = Math.max(0, subtotal - totalItemDiscounts - globalDiscount);
            const taxes = taxableSubtotal * currentTaxRate;
            const total = taxableSubtotal + taxes;

            document.getElementById('subtotalDisplay').textContent = formatCurrency(subtotal);
            document.getElementById('itemDiscountDisplay').textContent = `-${formatCurrency(totalItemDiscounts)}`;
            document.getElementById('taxDisplay').textContent = formatCurrency(taxes);
            document.getElementById('cartTotal').textContent = formatCurrency(total);

            updateBalance(total);
        }

        function addPaymentRow() {
            const methodSelect = document.getElementById('paymentMethod');
            const amountInput = document.getElementById('paymentAmount');
            const method = methodSelect.value;
            const amount = parseFloat(amountInput.value);

            if (!amount || amount <= 0) {
                showAlert('Ingrese un monto válido', 'warning');
                return;
            }

            payments.push({ metodo: method, monto: amount });
            amountInput.value = '';
            renderPayments();
        }

        function removePayment(index) {
            payments.splice(index, 1);
            renderPayments();
        }

        function renderPayments() {
            const list = document.getElementById('paymentsList');
            list.innerHTML = '';
            payments.forEach((p, idx) => {
                list.innerHTML += `
                    <div class="d-flex justify-content-between align-items-center bg-white border p-2 rounded mb-2">
                        <span class="payment-pill bg-light border text-uppercase">${escapeHtml(p.metodo)}</span>
                        <span class="fw-bold">${formatCurrency(p.monto)}</span>
                        <button class="btn btn-link text-danger p-0 ms-2" onclick="removePayment(${idx})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
            });

            const currentTotal = getCartTotal();
            updateBalance(currentTotal);
        }

        function getCartTotal() {
            const subtotal = cart.reduce((acc, item) => acc + (item.precio * item.cantidad), 0);
            const itemDiscounts = cart.reduce((acc, item) => acc + item.descuento, 0);
            const globalDiscount = Math.max(0, parseFloat(document.getElementById('globalDiscount').value) || 0);
            const taxableSubtotal = Math.max(0, subtotal - itemDiscounts - globalDiscount);
            const taxes = taxableSubtotal * currentTaxRate;
            return taxableSubtotal + taxes;
        }

        function updateBalance(total) {
            const paidTotal = payments.reduce((acc, p) => acc + p.monto, 0);
            const balanceContainer = document.getElementById('balanceContainer');
            const balanceLabel = document.getElementById('balanceLabel');
            const balanceDisplay = document.getElementById('balanceDisplay');

            if (payments.length > 0) {
                balanceContainer.style.display = 'block';
                const diff = paidTotal - total;
                if (diff >= 0) {
                    balanceLabel.textContent = 'Cambio / Vuelto:';
                    balanceLabel.className = 'small fw-bold text-success';
                    balanceDisplay.textContent = formatCurrency(diff);
                    balanceDisplay.className = 'fw-bold text-success fs-5';
                } else {
                    balanceLabel.textContent = 'Pendiente de Pago:';
                    balanceLabel.className = 'small fw-bold text-danger';
                    balanceDisplay.textContent = formatCurrency(Math.abs(diff));
                    balanceDisplay.className = 'fw-bold text-danger fs-5';
                }
            } else {
                balanceContainer.style.display = 'none';
            }
        }

        async function processCheckout() {
            if (typeof tienePermiso === 'function' && !tienePermiso('ventas.gestionar')) {
                showAlert('No cuenta con el permiso requerido para registrar ventas.', 'warning');
                return;
            }

            if (cart.length === 0) {
                showAlert('El carrito está vacío', 'warning');
                return;
            }

            const total = getCartTotal();
            let totalPaid = payments.reduce((acc, p) => acc + p.monto, 0);

            if (payments.length === 0) {
                payments.push({ metodo: 'efectivo', monto: total });
                totalPaid = total;
            }

            if (totalPaid < total) {
                showAlert(`El monto pagado (${formatCurrency(totalPaid)}) es menor que el total de la venta (${formatCurrency(total)}).`, 'warning');
                return;
            }

            const subtotal = cart.reduce((acc, item) => acc + (item.precio * item.cantidad), 0);
            const itemDiscounts = cart.reduce((acc, item) => acc + item.descuento, 0);
            const globalDiscount = Math.max(0, parseFloat(document.getElementById('globalDiscount').value) || 0);
            const taxes = (subtotal - itemDiscounts - globalDiscount) * currentTaxRate;

            const payload = {
                cliente_id: selectedClient ? selectedClient.id : null,
                subtotal: subtotal,
                descuento: itemDiscounts + globalDiscount,
                impuestos: taxes,
                total: total,
                detalles: cart.map(item => ({
                    producto_id: item.id,
                    cantidad: item.cantidad,
                    precio_unitario: item.precio,
                    descuento: item.descuento,
                    subtotal: (item.precio * item.cantidad) - item.descuento
                })),
                pagos: payments
            };

            const btn = document.getElementById('btnCheckout');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';

            try {
                const res = await fetch('../backend/api.php?route=add_venta_directa', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();

                if (data.success) {
                    showAlert('¡Venta realizada con éxito!', 'success');
                    window.open(`factura.php?id=${data.venta_id}`, '_blank');
                    
                    const modalEl = document.getElementById('nuevaVentaModal');
                    if (modalEl) {
                        const modalInstance = bootstrap.Modal.getInstance(modalEl);
                        if (modalInstance) modalInstance.hide();
                    }

                    cart = [];
                    payments = [];
                    selectedClient = null;
                    document.getElementById('saleClientName').textContent = 'Consumidor Final';
                    document.getElementById('saleClientDetails').textContent = 'Venta Directa';
                    document.getElementById('globalDiscount').value = '0.00';
                    renderCart();
                    renderPayments();
                    await loadProducts();
                    await loadSalesHistory(1);
                } else {
                    showAlert(data.message || 'Error procesando la venta.', 'error');
                }
            } catch (e) {
                console.error(e);
                showAlert('Error de conexión.', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check-circle me-2"></i> Cobrar y Emitir Factura';
            }
        }

        async function loadVendedoresSelect() {
            try {
                const res = await fetch('../backend/api.php?route=get_vendedores');
                const data = await res.json();
                const select = document.getElementById('filterVendedor');
                if (select && data.success && data.data) {
                    select.innerHTML = '<option value="all">Vendedor: Todos</option>';
                    data.data.forEach(u => {
                        select.innerHTML += `<option value="${u.id}">${escapeHtml(u.nombre)}</option>`;
                    });
                }
            } catch (e) {
                console.error('Error fetching vendedores:', e);
            }
        }

        function onPeriodoChange() {
            const periodo = document.getElementById('filterPeriodo').value;
            const customRange = document.getElementById('customDateRange');
            if (periodo === 'custom') {
                customRange.style.removeProperty('display');
                customRange.style.display = 'flex';
            } else {
                customRange.style.display = 'none';
                applyFilters();
            }
        }

        function handleVentaSearch() {
            const input = document.getElementById('ventaSearchInput');
            const query = (input.value || '').trim();
            const btnClear = document.getElementById('btnClearVentaSearch');
            if (btnClear) {
                btnClear.style.display = query.length > 0 ? 'inline-block' : 'none';
            }

            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                applyFilters();
            }, 250);
        }

        function clearVentaSearch() {
            const input = document.getElementById('ventaSearchInput');
            input.value = '';
            const btnClear = document.getElementById('btnClearVentaSearch');
            if (btnClear) btnClear.style.display = 'none';
            applyFilters();
            input.focus();
        }

        function applyFilters() {
            loadSalesHistory(1);
        }

        function resetAllFilters() {
            document.getElementById('ventaSearchInput').value = '';
            const btnClear = document.getElementById('btnClearVentaSearch');
            if (btnClear) btnClear.style.display = 'none';

            document.getElementById('filterPeriodo').value = 'all';
            document.getElementById('filterEstado').value = 'all';
            document.getElementById('filterTipoPago').value = 'all';
            document.getElementById('filterVendedor').value = 'all';
            document.getElementById('filterStartDate').value = '';
            document.getElementById('filterEndDate').value = '';
            document.getElementById('customDateRange').style.display = 'none';

            loadSalesHistory(1);
        }

        async function loadSalesHistory(page = 1) {
            currentPage = page;
            const search = (document.getElementById('ventaSearchInput')?.value || '').trim();
            const periodo = document.getElementById('filterPeriodo')?.value || 'all';
            const estado = document.getElementById('filterEstado')?.value || 'all';
            const tipoPago = document.getElementById('filterTipoPago')?.value || 'all';
            const vendedor = document.getElementById('filterVendedor')?.value || 'all';
            const startDate = document.getElementById('filterStartDate')?.value || '';
            const endDate = document.getElementById('filterEndDate')?.value || '';

            const params = new URLSearchParams({
                route: 'get_ventas',
                filter: periodo,
                start_date: startDate,
                end_date: endDate,
                search: search,
                estado: estado,
                tipo_pago: tipoPago,
                vendedor: vendedor,
                limit: itemsPerPage,
                page: page
            });

            try {
                const res = await fetch(`../backend/api.php?${params.toString()}`);
                const data = await res.json();
                const tbody = document.getElementById('salesTableBody');
                tbody.innerHTML = '';

                if (data.success && data.data && data.data.length > 0) {
                    const offset = (page - 1) * itemsPerPage;
                    data.data.forEach((v, index) => {
                        const rowNumber = offset + index + 1;
                        let statusBadge = '<span class="badge bg-success">Completada</span>';
                        if (v.estado === 'cancelado') statusBadge = '<span class="badge bg-danger">Cancelada</span>';
                        else if (v.estado === 'pendiente') statusBadge = '<span class="badge bg-warning text-dark">Pendiente</span>';

                        const puedeGestionar = (typeof tienePermiso === 'function' ? tienePermiso('ventas.gestionar') : true);

                        tbody.innerHTML += `
                            <tr>
                                <td class="ps-4 fw-bold text-dark">${rowNumber}</td>
                                <td><small class="text-muted">${v.fecha_venta}</small></td>
                                <td><span class="fw-semibold text-dark">${escapeHtml(v.cliente_nombre || 'Consumidor Final')}</span></td>
                                <td><small class="text-muted">${escapeHtml(v.vendedor || 'Sistema')}</small></td>
                                <td><span class="badge bg-light text-dark border small">${escapeHtml(v.tipo_pago || 'N/A')}</span></td>
                                <td class="fw-bold text-dark">${formatCurrency(v.total)}</td>
                                <td class="text-success fw-bold">${formatCurrency(v.ganancias)}</td>
                                <td>${statusBadge}</td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-info me-1" onclick="viewSaleDetails(${v.id})" title="Ver Detalle">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="factura.php?id=${v.id}" target="_blank" class="btn btn-sm btn-outline-primary me-1" title="Factura">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    ${v.estado !== 'cancelado' && puedeGestionar ? `
                                        <button class="btn btn-sm btn-outline-danger" onclick="cancelSale(${v.id})" title="Anular">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    ` : ''}
                                </td>
                            </tr>
                        `;
                    });

                    // Totales del período
                    if (data.totales_periodo) {
                        document.getElementById('totalRevenue').textContent = formatCurrency(data.totales_periodo.total_ingresos);
                        document.getElementById('totalProfit').textContent = formatCurrency(data.totales_periodo.total_ganancias);
                    }

                    renderPagination(data.total, itemsPerPage, page);
                } else {
                    tbody.innerHTML = `<tr><td colspan="9" class="text-center py-5 text-muted">No se encontraron ventas.</td></tr>`;
                    if (data.totales_periodo) {
                        document.getElementById('totalRevenue').textContent = formatCurrency(data.totales_periodo.total_ingresos);
                        document.getElementById('totalProfit').textContent = formatCurrency(data.totales_periodo.total_ganancias);
                    } else {
                        document.getElementById('totalRevenue').textContent = formatCurrency(0);
                        document.getElementById('totalProfit').textContent = formatCurrency(0);
                    }
                    const nav = document.getElementById('salesPagination');
                    if (nav) nav.innerHTML = '';
                }
            } catch (e) {
                console.error('Error fetching sales history:', e);
            }
        }

        function renderPagination(total, limit, page) {
            const totalPages = Math.ceil(total / limit);
            const nav = document.getElementById('salesPagination');
            if (!nav) return;
            nav.innerHTML = '';
            if (totalPages <= 1) return;

            let html = '<nav aria-label="Paginación de ventas"><ul class="pagination justify-content-end mb-0">';

            // Primera
            const firstDisabled = page <= 1 ? ' disabled' : '';
            html += `<li class="page-item${firstDisabled}"><a class="page-link" href="#" onclick="loadSalesHistory(1); return false;">Primera</a></li>`;

            // Anterior
            const prevDisabled = page <= 1 ? ' disabled' : '';
            html += `<li class="page-item${prevDisabled}"><a class="page-link" href="#" onclick="loadSalesHistory(${page - 1}); return false;">Anterior</a></li>`;

            // Páginas numeradas
            if (totalPages <= 7) {
                for (let i = 1; i <= totalPages; i++) {
                    const active = i === page ? ' active' : '';
                    html += `<li class="page-item${active}"><a class="page-link" href="#" onclick="loadSalesHistory(${i}); return false;">${i}</a></li>`;
                }
            } else {
                let startPage = Math.max(1, page - 2);
                let endPage = Math.min(totalPages, page + 2);

                if (page <= 3) {
                    startPage = 1;
                    endPage = 5;
                } else if (page >= totalPages - 2) {
                    startPage = totalPages - 4;
                    endPage = totalPages;
                }

                if (startPage > 1) {
                    html += `<li class="page-item"><a class="page-link" href="#" onclick="loadSalesHistory(1); return false;">1</a></li>`;
                    if (startPage > 2) {
                        html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                    }
                }

                for (let i = startPage; i <= endPage; i++) {
                    const active = i === page ? ' active' : '';
                    html += `<li class="page-item${active}"><a class="page-link" href="#" onclick="loadSalesHistory(${i}); return false;">${i}</a></li>`;
                }

                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) {
                        html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                    }
                    html += `<li class="page-item"><a class="page-link" href="#" onclick="loadSalesHistory(${totalPages}); return false;">${totalPages}</a></li>`;
                }
            }

            // Siguiente
            const nextDisabled = page >= totalPages ? ' disabled' : '';
            html += `<li class="page-item${nextDisabled}"><a class="page-link" href="#" onclick="loadSalesHistory(${page + 1}); return false;">Siguiente</a></li>`;

            // Última
            const lastDisabled = page >= totalPages ? ' disabled' : '';
            html += `<li class="page-item${lastDisabled}"><a class="page-link" href="#" onclick="loadSalesHistory(${totalPages}); return false;">Última</a></li>`;

            html += '</ul></nav>';
            nav.innerHTML = html;
        }

        async function viewSaleDetails(id) {
            try {
                const res = await fetch(`../backend/api.php?route=get_venta_detalles&id=${id}`);
                const data = await res.json();
                if (data.success && data.data) {
                    const v = data.data.venta || data.data;
                    const details = data.data.detalles || [];
                    const payments = data.data.pagos || [];

                    document.getElementById('saleDetailsTitle').textContent = `Detalles de Venta #${v.id}`;
                    document.getElementById('dtFecha').textContent = v.fecha_venta;
                    document.getElementById('dtCliente').textContent = v.cliente_nombre || 'Consumidor Final';
                    document.getElementById('dtVendedor').textContent = v.vendedor || 'Sistema';
                    document.getElementById('dtEstado').textContent = v.estado;
                    document.getElementById('dtEstado').className = `badge ${v.estado === 'cancelado' ? 'bg-danger' : 'bg-success'}`;

                    const prodBody = document.getElementById('dtProductsList');
                    prodBody.innerHTML = '';
                    details.forEach(d => {
                        prodBody.innerHTML += `
                            <tr>
                                <td>${escapeHtml(d.producto_nombre)}</td>
                                <td class="text-center">${d.cantidad}</td>
                                <td class="text-end">${formatCurrency(d.precio_unitario)}</td>
                                <td class="text-end">${formatCurrency(d.descuento || 0)}</td>
                                <td class="text-end fw-bold">${formatCurrency(d.subtotal)}</td>
                            </tr>
                        `;
                    });

                    const payList = document.getElementById('dtPaymentsList');
                    payList.innerHTML = '';
                    payments.forEach(p => {
                        payList.innerHTML += `
                            <li class="list-group-item d-flex justify-content-between align-items-center py-1 px-0 bg-transparent">
                                <span class="text-uppercase small">${escapeHtml(p.metodo_pago)}</span>
                                <span class="fw-bold">${formatCurrency(p.monto)}</span>
                            </li>
                        `;
                    });

                    document.getElementById('dtSubtotal').textContent = formatCurrency(v.subtotal);
                    document.getElementById('dtDescuento').textContent = '-' + formatCurrency(v.descuento || 0);
                    document.getElementById('dtImpuestos').textContent = formatCurrency(v.impuestos || 0);
                    document.getElementById('dtTotal').textContent = formatCurrency(v.total);

                    document.getElementById('btnPrintInvoice').href = `factura.php?id=${v.id}`;

                    const modal = new bootstrap.Modal(document.getElementById('saleDetailsModal'));
                    modal.show();
                }
            } catch (e) {
                console.error('Error fetching sale details:', e);
            }
        }

        async function cancelSale(id) {
            if (typeof tienePermiso === 'function' && !tienePermiso('ventas.gestionar')) {
                showAlert('No dispone de permisos para anular ventas.', 'warning');
                return;
            }

            if (!(await showConfirm(`¿Está seguro de anular la venta #${id}? Esta acción revertirá el stock de los productos.`))) return;

            try {
                const res = await fetch('../backend/api.php?route=cancel_venta', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ venta_id: id })
                });
                const data = await res.json();
                if (data.success) {
                    showAlert('Venta anulada exitosamente.', 'success');
                    await loadProducts();
                    await loadSalesHistory(currentPage);
                } else {
                    showAlert(data.message || 'Error al anular la venta.', 'error');
                }
            } catch (e) {
                console.error(e);
                showAlert('Error de conexión.', 'error');
            }
        }
    </script>
</body>
</html>
