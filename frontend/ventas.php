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
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <div class="main-content">
            <?php include 'includes/navbar.php'; ?>

            <div class="container-fluid p-4 animate-fade-in">
                <?php echo \App\Helpers\DateFilterHelper::getFilterUI($filter, $start_date, $end_date, 'ventas.php'); ?>
                
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

                <div class="row g-4">
                    <!-- POS View -->
                    <div class="col-12 col-lg-5">
                        <div class="card pos-card border-0 shadow-sm">
                            <div class="card-header bg-primary text-white border-0 py-3">
                                <h5 class="mb-0 fw-bold"><i class="fas fa-cash-register me-2"></i>Nueva Venta Directa</h5>
                            </div>
                            <div class="card-body p-4">
                                <!-- Product Picker -->
                                <div class="mb-4">
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
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-bold small text-uppercase text-muted">Detalle de Compra</span>
                                        <span class="badge bg-light text-dark border" id="itemCount">0 items</span>
                                    </div>
                                    <div class="cart-list-container">
                                        <ul class="list-group list-group-flush border-0" id="cartList">
                                            <li class="list-group-item bg-transparent text-muted text-center py-5 small italic">
                                                No hay productos en el carrito
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Summary -->
                                <div class="bg-light p-4 rounded mb-4">
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
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-muted small" id="taxLabel">IVA (15%):</span>
                                        <span class="fw-bold" id="taxDisplay">$0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center pt-3 border-top border-2 border-primary border-opacity-10">
                                        <span class="fw-bold text-dark h5 mb-0">TOTAL:</span>
                                        <h3 class="mb-0 fw-bold text-primary" id="cartTotal">$0.00</h3>
                                    </div>
                                </div>

                                <!-- Payments -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold small text-uppercase text-muted d-block mb-2">Método de Pago</label>
                                    <div id="paymentsList" class="mb-3"></div>
                                    
                                    <div class="row g-2 mb-3">
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

                                    <div id="balanceContainer" class="p-3 bg-white border rounded mb-3" style="display: none;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="small fw-bold text-muted" id="balanceLabel">Cambio / Vuelto:</span>
                                            <span class="fw-bold text-success fs-5" id="balanceDisplay">$0.00</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Checkout Action -->
                                <button class="btn btn-primary w-100 py-3 fw-bold text-uppercase shadow-sm" id="btnCheckout" onclick="processCheckout()">
                                    <i class="fas fa-check-circle me-2"></i> Cobrar y Emitir Factura
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- History Table -->
                    <div class="col-12 col-lg-7">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-history me-2 text-secondary"></i>Historial de Ventas</h5>
                                <button class="btn btn-sm btn-outline-secondary" onclick="loadSalesHistory()">
                                    <i class="fas fa-sync-alt me-1"></i> Actualizar
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>ID</th>
                                                <th>Fecha y Hora</th>
                                                <th>Vendedor</th>
                                                <th>Total</th>
                                                <th>Ganancia</th>
                                                <th>Estado</th>
                                                <th class="text-end">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="salesTableBody">
                                            <tr>
                                                <td colspan="7" class="text-center py-5 text-muted">
                                                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                                    Cargando ventas...
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
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
                            <small class="text-muted d-block text-uppercase fw-bold">Vendedor</small>
                            <span id="dtVendedor" class="fw-semibold text-dark">-</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block text-uppercase fw-bold">Tipo Venta</small>
                            <span id="dtTipo" class="badge bg-light text-dark border">-</span>
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
        let cart = [];
        let payments = [];
        let currentTaxRate = 0.15;
        let currencySymbol = '$';

        document.addEventListener('DOMContentLoaded', async () => {
            await fetchCompanyInfo();
            await loadProducts();
            await loadSalesHistory();

            // Deshabilitar botón de checkout si el usuario no tiene permisos de gestión
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
                    if (data.data.moneda) {
                        currencySymbol = data.data.moneda;
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
                        select.innerHTML += `<option value="${p.id}">${p.nombre} - ${currencySymbol}${parseFloat(p.precio_venta).toFixed(2)} (Stock: ${p.stock_actual})</option>`;
                    });
                }
            } catch (e) {
                console.error('Error fetching products:', e);
            }
        }

        function addToCart() {
            const select = document.getElementById('productoSelect');
            const prodId = parseInt(select.value);
            if (!prodId) return;

            const product = availableProducts.find(p => p.id == prodId);
            if (!product) return;

            if (product.stock_actual <= 0) {
                alert('¡El producto seleccionado no tiene stock disponible!');
                return;
            }

            const existing = cart.find(item => item.id == prodId);
            if (existing) {
                if (existing.cantidad + 1 > product.stock_actual) {
                    alert('No hay suficiente stock para añadir más unidades.');
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
                alert('Stock máximo alcanzado para este producto.');
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
                                <span class="fw-semibold text-dark">${item.nombre}</span>
                                <span class="fw-bold text-primary">${currencySymbol}${itemSubtotal.toFixed(2)}</span>
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

            document.getElementById('subtotalDisplay').textContent = `${currencySymbol}${subtotal.toFixed(2)}`;
            document.getElementById('itemDiscountDisplay').textContent = `-${currencySymbol}${totalItemDiscounts.toFixed(2)}`;
            document.getElementById('taxDisplay').textContent = `${currencySymbol}${taxes.toFixed(2)}`;
            document.getElementById('cartTotal').textContent = `${currencySymbol}${total.toFixed(2)}`;

            updateBalance(total);
        }

        function addPaymentRow() {
            const methodSelect = document.getElementById('paymentMethod');
            const amountInput = document.getElementById('paymentAmount');
            const method = methodSelect.value;
            const amount = parseFloat(amountInput.value);

            if (!amount || amount <= 0) {
                alert('Ingrese un monto válido');
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
                        <span class="payment-pill bg-light border text-uppercase">${p.metodo}</span>
                        <span class="fw-bold">${currencySymbol}${p.monto.toFixed(2)}</span>
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
                    balanceDisplay.textContent = `${currencySymbol}${diff.toFixed(2)}`;
                    balanceDisplay.className = 'fw-bold text-success fs-5';
                } else {
                    balanceLabel.textContent = 'Pendiente de Pago:';
                    balanceLabel.className = 'small fw-bold text-danger';
                    balanceDisplay.textContent = `${currencySymbol}${Math.abs(diff).toFixed(2)}`;
                    balanceDisplay.className = 'fw-bold text-danger fs-5';
                }
            } else {
                balanceContainer.style.display = 'none';
            }
        }

        async function processCheckout() {
            if (typeof tienePermiso === 'function' && !tienePermiso('ventas.gestionar')) {
                alert('No cuenta con el permiso requerido para registrar ventas.');
                return;
            }

            if (cart.length === 0) {
                alert('El carrito está vacío');
                return;
            }

            const total = getCartTotal();
            let totalPaid = payments.reduce((acc, p) => acc + p.monto, 0);

            if (payments.length === 0) {
                payments.push({ metodo: 'efectivo', monto: total });
                totalPaid = total;
            }

            if (totalPaid < total) {
                alert(`El monto pagado (${currencySymbol}${totalPaid.toFixed(2)}) es menor que el total de la venta (${currencySymbol}${total.toFixed(2)}).`);
                return;
            }

            const subtotal = cart.reduce((acc, item) => acc + (item.precio * item.cantidad), 0);
            const itemDiscounts = cart.reduce((acc, item) => acc + item.descuento, 0);
            const globalDiscount = Math.max(0, parseFloat(document.getElementById('globalDiscount').value) || 0);
            const taxes = (subtotal - itemDiscounts - globalDiscount) * currentTaxRate;

            const payload = {
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
                    alert('¡Venta realizada con éxito!');
                    window.open(`factura.php?id=${data.venta_id}`, '_blank');
                    cart = [];
                    payments = [];
                    document.getElementById('globalDiscount').value = '0.00';
                    renderCart();
                    renderPayments();
                    await loadProducts();
                    await loadSalesHistory();
                } else {
                    alert(data.message || 'Error procesando la venta.');
                }
            } catch (e) {
                console.error(e);
                alert('Error de conexión.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check-circle me-2"></i> Cobrar y Emitir Factura';
            }
        }

        async function loadSalesHistory() {
            const urlParams = new URLSearchParams(window.location.search);
            const filter = urlParams.get('filter') || 'all';
            const startDate = urlParams.get('start_date') || '';
            const endDate = urlParams.get('end_date') || '';

            let url = `../backend/api.php?route=get_ventas&filter=${encodeURIComponent(filter)}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;

            try {
                const res = await fetch(url);
                const data = await res.json();
                const tbody = document.getElementById('salesTableBody');
                tbody.innerHTML = '';

                if (data.success && data.data && data.data.length > 0) {
                    let rev = 0;
                    let prof = 0;

                    data.data.forEach(v => {
                        if (v.estado !== 'cancelado') {
                            rev += parseFloat(v.total || 0);
                            prof += parseFloat(v.ganancias || 0);
                        }

                        let statusBadge = '<span class="badge bg-success">Completada</span>';
                        if (v.estado === 'cancelado') statusBadge = '<span class="badge bg-danger">Cancelada</span>';

                        const puedeGestionar = (typeof tienePermiso === 'function' ? tienePermiso('ventas.gestionar') : true);

                        tbody.innerHTML += `
                            <tr>
                                <td class="fw-bold">#${v.id}</td>
                                <td><small>${v.fecha_venta}</small></td>
                                <td>${v.vendedor || 'Sistema'}</td>
                                <td class="fw-bold text-dark">${currencySymbol}${parseFloat(v.total).toFixed(2)}</td>
                                <td class="text-success fw-bold">${currencySymbol}${parseFloat(v.ganancias).toFixed(2)}</td>
                                <td>${statusBadge}</td>
                                <td class="text-end">
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

                    document.getElementById('totalRevenue').textContent = `${currencySymbol}${rev.toFixed(2)}`;
                    document.getElementById('totalProfit').textContent = `${currencySymbol}${prof.toFixed(2)}`;
                } else {
                    tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-muted">No hay registros de ventas para el periodo seleccionado</td></tr>`;
                    document.getElementById('totalRevenue').textContent = `${currencySymbol}0.00`;
                    document.getElementById('totalProfit').textContent = `${currencySymbol}0.00`;
                }
            } catch (e) {
                console.error('Error fetching sales history:', e);
            }
        }

        async function viewSaleDetails(id) {
            try {
                const res = await fetch(`../backend/api.php?route=get_venta_detalles&id=${id}`);
                const data = await res.json();
                if (data.success && data.data) {
                    const v = data.data.venta;
                    const details = data.data.detalles || [];
                    const payments = data.data.pagos || [];

                    document.getElementById('saleDetailsTitle').textContent = `Detalles de Venta #${v.id}`;
                    document.getElementById('dtFecha').textContent = v.fecha_venta;
                    document.getElementById('dtVendedor').textContent = v.vendedor || 'Sistema';
                    document.getElementById('dtTipo').textContent = v.pedido_id ? `Pedido #${v.pedido_id}` : 'Venta Directa';
                    document.getElementById('dtEstado').textContent = v.estado;
                    document.getElementById('dtEstado').className = `badge ${v.estado === 'cancelado' ? 'bg-danger' : 'bg-success'}`;

                    const prodBody = document.getElementById('dtProductsList');
                    prodBody.innerHTML = '';
                    details.forEach(d => {
                        prodBody.innerHTML += `
                            <tr>
                                <td>${d.producto_nombre}</td>
                                <td class="text-center">${d.cantidad}</td>
                                <td class="text-end">${currencySymbol}${parseFloat(d.precio_unitario).toFixed(2)}</td>
                                <td class="text-end">${currencySymbol}${parseFloat(d.descuento || 0).toFixed(2)}</td>
                                <td class="text-end fw-bold">${currencySymbol}${parseFloat(d.subtotal).toFixed(2)}</td>
                            </tr>
                        `;
                    });

                    const payList = document.getElementById('dtPaymentsList');
                    payList.innerHTML = '';
                    payments.forEach(p => {
                        payList.innerHTML += `
                            <li class="list-group-item d-flex justify-content-between align-items-center py-1 px-0 bg-transparent">
                                <span class="text-uppercase small">${p.metodo_pago}</span>
                                <span class="fw-bold">${currencySymbol}${parseFloat(p.monto).toFixed(2)}</span>
                            </li>
                        `;
                    });

                    document.getElementById('dtSubtotal').textContent = `${currencySymbol}${parseFloat(v.subtotal).toFixed(2)}`;
                    document.getElementById('dtDescuento').textContent = `-${currencySymbol}${parseFloat(v.descuento || 0).toFixed(2)}`;
                    document.getElementById('dtImpuestos').textContent = `${currencySymbol}${parseFloat(v.impuestos || 0).toFixed(2)}`;
                    document.getElementById('dtTotal').textContent = `${currencySymbol}${parseFloat(v.total).toFixed(2)}`;

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
                alert('No dispone de permisos para anular ventas.');
                return;
            }

            if (!confirm(`¿Está seguro de anular la venta #${id}? Esta acción revertirá el stock de los productos.`)) return;

            try {
                const res = await fetch('../backend/api.php?route=cancel_venta', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ venta_id: id })
                });
                const data = await res.json();
                if (data.success) {
                    alert('Venta anulada exitosamente.');
                    await loadProducts();
                    await loadSalesHistory();
                } else {
                    alert(data.message || 'Error al anular la venta.');
                }
            } catch (e) {
                console.error(e);
                alert('Error de conexión.');
            }
        }
    </script>
</body>
</html>
