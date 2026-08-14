<?php
session_start();
require_once __DIR__ . '/includes/permisos.php';
if (!isset($_SESSION['usuario'])) {
    header("Location: login.html");
    exit();
}
if (!tiene_permiso('ventas.ver')) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventas - La Vicky</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background-color: #f4f6f9;
        }

        .sidebar {
            height: 100vh;
            background-color: #685569;
            padding-top: 20px;
            position: fixed;
            width: 16.666667%;
            overflow-y: auto;
        }

        .sidebar a {
            padding: 15px 20px;
            text-decoration: none;
            font-size: 16px;
            color: #d1d8e0;
            display: block;
            transition: 0.3s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            color: #fff;
            background-color: #0d6efd;
        }

        .main-content {
            padding: 30px;
            margin-left: 16.666667%;
        }

        .top-navbar {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-left: 16.666667%;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
        }
    </style>
</head>

<body>
    <div class="container-fluid p-0">
        <?php $active = 'ventas'; $titulo = 'Ventas y Finanzas'; include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <div class="row">
                <!-- Columna Izquierda: Punto de Venta (Cart) -->
                <div class="col-md-5">
                    <div class="card shadow-sm border-0 mb-4 bg-light">
                        <div class="card-header bg-success text-white">
                            <h5 class="m-0"><i class="fas fa-cash-register"></i> Punto de Venta</h5>
                        </d                        <div class="card-body">
                            <label class="form-label fw-bold">Producto a vender</label>
                            <div class="input-group mb-3">
                                <select id="productoSelect" class="form-select">
                                    <option value="" disabled selected>Cargando productos...</option>
                                </select>
                                <button class="btn btn-outline-secondary" type="button" onclick="addToCart()">Añadir</button>
                            </div>

                            <hr>
                            <h6 class="fw-bold">Carrito de Compra:</h6>
                            <ul class="list-group list-group-flush mb-3" id="cartList">
                                <li class="list-group-item text-muted text-center small">El carrito está vacío</li>
                            </ul>

                            <!-- Subtotal, Descuentos e Impuestos -->
                            <div class="bg-white p-3 rounded border mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Subtotal:</span>
                                    <span id="subtotalDisplay">$0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1 text-primary">
                                    <span>Descuento Items:</span>
                                    <span id="itemDiscountDisplay">-$0.00</span>
                                </div>
                                <div class="row mb-1 align-items-center">
                                    <div class="col-7">Descuento Global ($):</div>
                                    <div class="col-5">
                                        <input type="number" id="globalDiscount" class="form-control form-control-sm text-end" value="0.00" oninput="renderCart()">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span id="taxLabel">Impuestos (IVA 15%):</span>
                                    <span id="taxDisplay">$0.00</span>
                                </div>
                                <div class="d-flex justify-content-between fw-bold mt-2 text-danger fs-5">
                                    <span>TOTAL:</span>
                                    <span id="cartTotal">$0.00</span>
                                </div>
                            </div>

                            <!-- Métodos de Pago Múltiples -->
                            <hr>
                            <h6 class="fw-bold">Pagos:</h6>
                            <div id="paymentsList" class="mb-3">
                                <!-- Filas de pago dinámicas -->
                            </div>
                            <div class="row g-2 mb-4">
                                <div class="col-6">
                                    <select id="paymentMethod" class="form-select form-select-sm">
                                        <option value="efectivo">Efectivo</option>
                                        <option value="tarjeta">Tarjeta</option>
                                        <option value="transferencia">Transferencia</option>
                                        <option value="wallet">Billetera Digital</option>
                                    </select>
                                </div>
                                <div class="col-4">
                                    <input type="number" id="paymentAmount" class="form-control form-control-sm" placeholder="Monto">
                                </div>
                                <div class="col-2">
                                    <button class="btn btn-primary btn-sm w-100" onclick="addPaymentRow()"><i class="fas fa-plus"></i></button>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between fw-bold mb-3">
                                <span>Restante por pagar:</span>
                                <span id="balanceDisplay" class="text-danger">$0.00</span>
                            </div>

                            <?php if (tiene_permiso('ventas.gestionar')): ?>
                            <button class="btn btn-success w-100 py-2 fs-5" id="btnProcesar" onclick="procesarVenta()" disabled><i class="fas fa-check-circle"></i> Efectuar Venta</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha: Historial y Stats -->
                <div class="col-md-7">
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3">
                            <div class="stat-card">
                                <h6>Total Ingresos Históricos</h6>
                                <h2 id="totalRevenue">$0.00</h2>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="stat-card" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                                <h6>Total Ganancias</h6>
                                <h2 id="totalProfit">$0.00</h2>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de Ventas -->
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white pt-3 pb-2">
                            <h5 class="card-title m-0">Últimas Ventas</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover m-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th># Venta</th>
                                            <th>Fecha</th>
                                            <th>Total</th>
                                            <th>Estado</th>
                                            <th>Vendedor</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="ventasTableBody">
                                        <tr>
                                            <td colspan="6" class="text-center text-muted p-4">Cargando datos...</td>
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

    <!-- Modal Factura (Opcional, se abre en pestaña nueva por ahora) -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/common.js"></script>
    <script>
        let availableProducts = [];
        let curCart = [];
        let curPayments = [];
        let totals = {
            subtotal: 0,
            itemDiscount: 0,
            globalDiscount: 0,
            tax: 0,
            total: 0,
            paid: 0,
            toPay: 0
        };

        let TAX_RATE = 0.15; // 15% IVA (se actualiza con el perfil de la panadería)

        document.addEventListener('DOMContentLoaded', () => {
            loadProductos();
            loadVentas();
            loadProfile();
        });

        // Carga la tasa de impuesto configurada en el perfil de la panadería.
        async function loadProfile() {
            try {
                const res = await fetch('../backend/api.php?route=get_datos_empresa');
                const json = await res.json();
                if (!json.success || !json.data) return;
                const tasa = parseFloat(json.data.tasa_impuesto);
                if (isNaN(tasa) || tasa < 0) return;
                TAX_RATE = tasa / 100;
                const lbl = document.getElementById('taxLabel');
                if (lbl) lbl.textContent = `Impuestos (IVA ${tasa}%):`;
                if (typeof curCart !== 'undefined' && curCart.length > 0) renderCart();
            } catch (e) { /* Mantener 15% por defecto */ }
        }

        async function loadProductos() {
            try {
                const res = await fetch('../backend/api.php?route=get_productos');
                const data = await res.json();
                if (data.success && data.data) {
                    availableProducts = data.data;
                    const sel = document.getElementById('productoSelect');
                    sel.innerHTML = '<option value="" disabled selected>Seleccione un producto</option>';
                    availableProducts.forEach(p => {
                        let stock_text = p.stock_actual > 0 ? `(Stock: ${p.stock_actual})` : '(Sin stock)';
                        sel.innerHTML += `<option value="${p.id}" data-precio="${p.precio_venta}">${p.nombre} - $${p.precio_venta} ${stock_text}</option>`;
                    });
                }
            } catch (e) { }
        }

        async function loadVentas() {
            try {
                const response = await fetch('../backend/api.php?route=get_ventas');
                const data = await response.json();
                const tbody = document.getElementById('ventasTableBody');
                let totalRev = 0;
                let totalProfit = 0;
                tbody.innerHTML = '';

                if (data.success && data.data.length > 0) {
                    data.data.forEach(v => {
                        if (v.estado !== 'cancelado') {
                            totalRev += parseFloat(v.total);
                            totalProfit += parseFloat(v.ganancias || 0);
                        }
                        
                        let badgeClass = v.estado === 'cancelado' ? 'bg-danger' : 'bg-success';
                        let rowClass = v.estado === 'cancelado' ? 'table-danger opacity-75' : '';
                        let fechaArr = v.fecha_venta.split(' ');

                        tbody.innerHTML += `
                            <tr class="${rowClass}">
                                <td>#${v.id}</td>
                                <td><small>${fechaArr[0]} ${fechaArr[1] || ''}</small></td>
                                <td class="fw-bold">$${v.total}</td>
                                <td><span class="badge ${badgeClass}">${v.estado.toUpperCase()}</span></td>
                                <td><small class="text-muted text-uppercase">${v.vendedor || 'Sistema'}</small></td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-primary" onclick="printInvoice(${v.id})" title="Ver Factura"><i class="fas fa-file-invoice"></i></button>
                                        ${v.estado === 'completado' && tienePermiso('ventas.gestionar') ? `
                                            <button class="btn btn-sm btn-outline-danger" onclick="cancelarVenta(${v.id})" title="Cancelar Venta"><i class="fas fa-times-circle"></i></button>
                                        ` : ''}
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                    document.getElementById('totalRevenue').textContent = '$' + totalRev.toFixed(2);
                    document.getElementById('totalProfit').textContent = '$' + totalProfit.toFixed(2);
                } else {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted p-4">No hay ventas registradas.</td></tr>';
                }
            } catch (err) { }
        }

        function addToCart() {
            const sel = document.getElementById('productoSelect');
            if (!sel.value) return;

            const prodText = sel.options[sel.selectedIndex].text;
            const prodPrecio = parseFloat(sel.options[sel.selectedIndex].getAttribute('data-precio'));

            const exist = curCart.find(item => item.producto_id == sel.value);
            if (exist) {
                exist.cantidad++;
            } else {
                curCart.push({
                    producto_id: sel.value,
                    nombre: prodText.split(' - ')[0],
                    precio: prodPrecio,
                    cantidad: 1,
                    descuento: 0
                });
            }
            renderCart();
        }

        function updateItemQty(idx, qty) {
            curCart[idx].cantidad = parseInt(qty) || 1;
            renderCart();
        }

        function updateItemDiscount(idx, desc) {
            curCart[idx].descuento = parseFloat(desc) || 0;
            renderCart();
        }

        function renderCart() {
            const list = document.getElementById('cartList');
            list.innerHTML = '';

            if (curCart.length === 0) {
                list.innerHTML = '<li class="list-group-item text-muted text-center small">El carrito está vacío</li>';
                resetTotals();
                return;
            }

            let subtotal = 0;
            let itemDiscount = 0;

            curCart.forEach((item, index) => {
                let brut = item.precio * item.cantidad;
                subtotal += brut;
                itemDiscount += item.descuento;

                list.innerHTML += `
                    <li class="list-group-item p-2">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-bold">${item.nombre}</span>
                            <span class="text-success fw-bold">$${brut.toFixed(2)}</span>
                        </div>
                        <div class="row g-2 align-items-center">
                            <div class="col-4">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Cant.</span>
                                    <input type="number" class="form-control" value="${item.cantidad}" onchange="updateItemQty(${index}, this.value)">
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Desc.$</span>
                                    <input type="number" class="form-control" value="${item.descuento}" onchange="updateItemDiscount(${index}, this.value)">
                                </div>
                            </div>
                            <div class="col-3 text-end">
                                <button class="btn btn-sm btn-outline-danger" onclick="remCart(${index})"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    </li>
                `;
            });

            totals.subtotal = subtotal;
            totals.itemDiscount = itemDiscount;
            totals.globalDiscount = parseFloat(document.getElementById('globalDiscount').value) || 0;
            
            // Cálculo de Impuestos y Total
            let baseImponible = subtotal - itemDiscount - totals.globalDiscount;
            if (baseImponible < 0) baseImponible = 0;
            
            totals.tax = baseImponible * TAX_RATE;
            totals.total = baseImponible + totals.tax;

            document.getElementById('subtotalDisplay').textContent = '$' + subtotal.toFixed(2);
            document.getElementById('itemDiscountDisplay').textContent = '-$' + itemDiscount.toFixed(2);
            document.getElementById('taxDisplay').textContent = '$' + totals.tax.toFixed(2);
            document.getElementById('cartTotal').textContent = '$' + totals.total.toFixed(2);

            updatePaymentsUI();
        }

        function resetTotals() {
            totals = { subtotal: 0, itemDiscount: 0, globalDiscount: 0, tax: 0, total: 0, paid: 0, toPay: 0 };
            document.getElementById('subtotalDisplay').textContent = '$0.00';
            document.getElementById('itemDiscountDisplay').textContent = '-$0.00';
            document.getElementById('taxDisplay').textContent = '$0.00';
            document.getElementById('cartTotal').textContent = '$0.00';
            document.getElementById('globalDiscount').value = '0.00';
            curPayments = [];
            updatePaymentsUI();
        }

        function remCart(idx) {
            curCart.splice(idx, 1);
            renderCart();
        }

        function addPaymentRow() {
            const method = document.getElementById('paymentMethod').value;
            const amount = parseFloat(document.getElementById('paymentAmount').value) || 0;
            const ref = ""; // Placeholder para referencia si fuera necesariov

            if (amount <= 0) return;

            curPayments.push({
                metodo: method,
                monto: amount,
                referencia: ref
            });

            document.getElementById('paymentAmount').value = '';
            updatePaymentsUI();
        }

        function removePaymentRow(idx) {
            curPayments.splice(idx, 1);
            updatePaymentsUI();
        }

        function updatePaymentsUI() {
            const list = document.getElementById('paymentsList');
            list.innerHTML = '';
            let paid = 0;

            curPayments.forEach((p, index) => {
                paid += p.monto;
                list.innerHTML += `
                    <div class="d-flex justify-content-between align-items-center bg-light p-2 border-bottom small">
                        <span><i class="fas fa-credit-card"></i> ${p.metodo.toUpperCase()}</span>
                        <div>
                            <span class="fw-bold">$${p.monto.toFixed(2)}</span>
                            <i class="fas fa-times text-danger ms-2" style="cursor:pointer" onclick="removePaymentRow(${index})"></i>
                        </div>
                    </div>
                `;
            });

            totals.paid = paid;
            totals.toPay = totals.total - paid;
            
            const bal = document.getElementById('balanceDisplay');
            const btnProcesar = document.getElementById('btnProcesar');
            if (totals.toPay <= 0 && totals.total > 0) {
                bal.textContent = 'PAGADO (Cambio: $' + Math.abs(totals.toPay).toFixed(2) + ')';
                bal.className = 'text-success';
                if (btnProcesar) btnProcesar.disabled = false;
            } else {
                bal.textContent = '$' + (totals.toPay > 0 ? totals.toPay.toFixed(2) : '0.00');
                bal.className = 'text-danger';
                if (btnProcesar) btnProcesar.disabled = true;
            }
        }

        async function procesarVenta() {
            if (curCart.length === 0) return;
            
            const payload = {
                subtotal: totals.subtotal,
                impuestos: totals.tax,
                descuento: totals.itemDiscount + totals.globalDiscount,
                total: totals.total,
                detalles: curCart,
                pagos: curPayments
            };

            try {
                const res = await fetch('../backend/api.php?route=add_venta_directa', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    curCart = [];
                    curPayments = [];
                    resetTotals();
                    loadVentas();
                    loadProductos();
                    if(data.venta_id) printInvoice(data.venta_id);
                } else alert('Error: ' + data.message);
            } catch (e) { alert('Error de conexión'); }
        }

        async function cancelarVenta(id) {
            if (!confirm('¿Está seguro de cancelar esta venta? El inventario será revertido.')) return;

            try {
                const res = await fetch('../backend/api.php?route=cancel_venta', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    loadVentas();
                    loadProductos();
                } else alert('Error: ' + data.message);
            } catch (e) { alert('Error al procesar solicitud'); }
        }

        function printInvoice(id) {
            window.open(`factura.php?id=${id}`, '_blank', 'width=450,height=600');
        }

        function logout() { fetch('../backend/api.php?route=logout').then(() => window.location.href = 'login.html'); }
    </script>
</body>

</html>
    </script>
</body>

</html>
