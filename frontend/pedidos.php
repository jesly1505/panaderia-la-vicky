<?php
// frontend/pedidos.php
session_start();
require_once __DIR__ . '/includes/permisos.php';

if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

if (!tiene_permiso('pedidos.ver')) {
    header("Location: index.php");
    exit();
}

$pageTitle = "Pedidos";
$pageHeader = "Gestión de Pedidos";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include 'includes/head.php'; ?>
    <style>
        .cart-item { transition: var(--transition); border-left: 3px solid transparent; }
        .cart-item:hover { background-color: var(--primary-light); border-left-color: var(--primary); }
        .badge-status { font-size: 0.75rem; padding: 0.4em 0.8em; }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
          <div class="main-content">
            <?php include 'includes/navbar.php'; ?>

            <div class="container-fluid p-4 animate-fade-in">
                <div class="row g-4">
                    

                    <!-- Orders History -->
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-history me-2 text-primary"></i>Historial de Pedidos</h5>
                                <div class="d-flex gap-2">
    <button class="btn btn-sm btn-outline-secondary" onclick="loadPedidos()">
        <i class="fas fa-sync-alt"></i> Actualizar
    </button>
    <button class="btn btn-sm btn-primary" id="btnNuevoPedidoEspecial"><i class="fas fa-plus"></i> + Nuevo Pedido Especial</button>
</div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>N.º / Fecha</th>
                                                <th>Cliente</th>
                                                
                                                <th>Estado</th>
                                                <th>Total</th>
                                                <th class="text-end">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="pedidosTableBody">
                                            <tr>
                                                <td colspan="6" class="text-center py-5 text-muted">
                                                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                                    Cargando historial...
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

    <!-- Modals -->
    <!-- New Order Modal -->
    <div class="modal fade" id="nuevoPedidoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 py-3">
                    <h5 class="modal-title fw-bold"><i class="fas fa-cart-plus me-2"></i>Nuevo Pedido Especial</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-uppercase text-muted">Cliente</label>
                        <select id="clienteSelect" class="form-select py-2">
                            <option value="">Consumidor Final</option>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Fecha Entrega</label>
                            <input type="date" id="fechaEntrega" class="form-control py-2" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Hora Entrega</label>
                            <input type="time" id="horaEntrega" class="form-control py-2" required>
                        </div>
                    </div>
                    <hr class="my-4 opacity-10">
                    <div class="mb-4">
                        <label class="form-label fw-semibold small text-uppercase text-muted">Añadir Producto</label>
                        <div class="input-group">
                            <select id="productoSelect" class="form-select py-2">
                                <option value="" disabled selected>Cargando productos...</option>
                            </select>
                            <button class="btn btn-primary px-3" type="button" onclick="addToCart()">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold small text-uppercase text-muted d-block mb-2">Carrito</label>
                        <div class="bg-light rounded p-2" style="max-height: 250px; overflow-y: auto;">
                            <ul class="list-group list-group-flush border-0" id="cartList">
                                <li class="list-group-item bg-transparent text-muted text-center py-4 small">El carrito está vacío</li>
                            </ul>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-primary bg-opacity-10 rounded text-primary">
                        <span class="fw-bold text-uppercase small">Total a Pagar:</span>
                        <h3 class="mb-0 fw-bold" id="cartTotal">$0.00</h3>
                    </div>
                    <button class="btn btn-primary w-100 py-3 fw-bold shadow-sm" id="btnProcesarPedido" onclick="procesarPedido()">
                        <i class="fas fa-check-circle me-2"></i>FINALIZAR PEDIDO
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editPedidoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 py-3">
                    <h5 class="modal-title fw-bold"><i class="fas fa-pen me-2"></i>Editar Pedido #<span id="editPedidoId"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="editPedidoForm">
                    <input type="hidden" id="editPedidoIdInput">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Cliente</label>
                            <select id="editPedidoCliente" class="form-select py-2">
                                <option value="">Consumidor Final</option>
                            </select>
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold small">Fecha Entrega</label>
                                <input type="date" id="editPedidoFecha" class="form-control py-2" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold small">Hora Entrega</label>
                                <input type="time" id="editPedidoHora" class="form-control py-2" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary fw-bold"><i class="fas fa-save me-1"></i>Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detallePedidoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Detalle del Pedido #<span id="detPedidoId"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="table-responsive rounded border mb-3">
                        <table class="table table-sm table-borderless align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3">Producto</th>
                                    <th>Cant.</th>
                                    <th class="text-end pe-3">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="detPedidosTableBody"></tbody>
                        </table>
                    </div>
                    <div class="text-end pe-3">
                        <small class="text-muted text-uppercase">Total Pedido:</small>
                        <h4 class="fw-bold text-primary mb-0" id="detPedidoTotal">$0.00</h4>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <?php include 'includes/footer.php'; ?>
    <script>
        let availableProducts = [];
        // Open new order modal handler
        document.getElementById('btnNuevoPedidoEspecial')?.addEventListener('click', () => {
            // Reset form fields and cart
            document.getElementById('clienteSelect').value = '';
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            document.getElementById('fechaEntrega').value = tomorrow.toISOString().split('T')[0];
            document.getElementById('horaEntrega').value = '10:00';
            cart = [];
            renderCart();
            new bootstrap.Modal(document.getElementById('nuevoPedidoModal')).show();
        });
        let cart = [];

        document.addEventListener('DOMContentLoaded', async () => {
            await loadClientes();
            await loadProductos();
            await loadPedidos();

            // Set default delivery date to tomorrow
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            document.getElementById('fechaEntrega').value = tomorrow.toISOString().split('T')[0];
            document.getElementById('horaEntrega').value = '10:00';

            if (typeof tienePermiso === 'function' && !tienePermiso('pedidos.gestionar')) {
                const btn = document.getElementById('btnProcesarPedido');
                if (btn) btn.disabled = true;
            }
        });

        async function loadClientes() {
            try {
                const res = await fetch('../backend/api.php?route=get_clientes');
                const data = await res.json();
                const select = document.getElementById('clienteSelect');
                const editSelect = document.getElementById('editPedidoCliente');
                if (data.success && data.data) {
                    data.data.forEach(c => {
                        select.innerHTML += `<option value="${c.id}">${c.nombre}</option>`;
                        editSelect.innerHTML += `<option value="${c.id}">${c.nombre}</option>`;
                    });
                }
            } catch (e) {
                console.error('Error fetching clients:', e);
            }
        }

        async function loadProductos() {
            try {
                const res = await fetch('../backend/api.php?route=get_productos');
                const data = await res.json();
                const select = document.getElementById('productoSelect');
                select.innerHTML = '<option value="" disabled selected>Seleccione producto...</option>';
                if (data.success && data.data) {
                    availableProducts = data.data;
                    data.data.forEach(p => {
                        select.innerHTML += `<option value="${p.id}">${p.nombre} - ${formatCurrency(p.precio_venta)}</option>`;
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

            const product = availableProducts.find(p => p.id === prodId);
            if (!product) return;

            const existing = cart.find(item => item.id === prodId);
            if (existing) {
                existing.cantidad++;
            } else {
                cart.push({
                    id: product.id,
                    nombre: product.nombre,
                    precio: parseFloat(product.precio_venta),
                    cantidad: 1
                });
            }

            renderCart();
        }

        function updateCartQty(index, delta) {
            cart[index].cantidad += delta;
            if (cart[index].cantidad <= 0) {
                cart.splice(index, 1);
            }
            renderCart();
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            renderCart();
        }

        function renderCart() {
            const list = document.getElementById('cartList');
            list.innerHTML = '';
            let total = 0;

            if (cart.length === 0) {
                list.innerHTML = `<li class="list-group-item bg-transparent text-muted text-center py-4 small">El carrito está vacío</li>`;
            } else {
                cart.forEach((item, index) => {
                    const subtotal = item.precio * item.cantidad;
                    total += subtotal;

                    list.innerHTML += `
                        <li class="list-group-item bg-white d-flex justify-content-between align-items-center mb-1 rounded cart-item p-2">
                            <div>
                                <div class="fw-semibold small text-dark">${item.nombre}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">${formatCurrency(item.precio)} c/u</div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <div class="input-group input-group-sm" style="width: 80px;">
                                    <button class="btn btn-outline-secondary p-1" onclick="updateCartQty(${index}, -1)">-</button>
                                    <span class="input-group-text p-1 text-center justify-content-center bg-light flex-grow-1">${item.cantidad}</span>
                                    <button class="btn btn-outline-secondary p-1" onclick="updateCartQty(${index}, 1)">+</button>
                                </div>
                                <button class="btn btn-sm btn-link text-danger p-0 ms-1" onclick="removeFromCart(${index})">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </li>
                    `;
                });
            }

            document.getElementById('cartTotal').textContent = formatCurrency(total);
        }

        async function procesarPedido() {
            if (typeof tienePermiso === 'function' && !tienePermiso('pedidos.gestionar')) {
                alert('No dispone de permisos para registrar pedidos.');
                return;
            }

            if (cart.length === 0) {
                alert('Debe añadir al menos un producto al carrito');
                return;
            }

            const clienteId = document.getElementById('clienteSelect').value;
            const fecha = document.getElementById('fechaEntrega').value;
            const hora = document.getElementById('horaEntrega').value;

            if (!fecha || !hora) {
                alert('Debe seleccionar fecha y hora de entrega');
                return;
            }

            const total = cart.reduce((acc, item) => acc + (item.precio * item.cantidad), 0);

            const payload = {
                cliente_id: clienteId ? parseInt(clienteId) : null,
                fecha_entrega: `${fecha} ${hora}:00`,
                total: total,
                detalles: cart.map(i => ({
                    producto_id: i.id,
                    cantidad: i.cantidad,
                    precio_unitario: i.precio,
                    subtotal: i.precio * i.cantidad
                }))
            };

            try {
                const res = await fetch('../backend/api.php?route=add_pedido', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    alert('Pedido registrado exitosamente');
                    cart = [];
                    renderCart();
                    await loadPedidos();
                } else {
                    alert(data.message || 'Error al procesar el pedido');
                }
            } catch (err) {
                console.error(err);
                alert('Error de conexión');
            }
        }

        async function loadPedidos() {
            try {
                const res = await fetch('../backend/api.php?route=get_pedidos');
                const data = await res.json();
                const tbody = document.getElementById('pedidosTableBody');
                tbody.innerHTML = '';

                if (data.success && data.data && data.data.length > 0) {
                    const puedeGestionar = (typeof tienePermiso === 'function' ? tienePermiso('pedidos.gestionar') : true);

                    data.data.forEach((p, index) => {
                        const rowNumber = index + 1;
                        let badge = 'bg-secondary';
                        let estadoTexto = p.estado;
                        if (p.estado === 'pendiente') { badge = 'bg-warning text-dark'; estadoTexto = 'Pendiente'; }
                        else if (p.estado === 'en_proceso') { badge = 'bg-info text-white'; estadoTexto = 'En Proceso'; }
                        else if (p.estado === 'entregado') { badge = 'bg-success'; estadoTexto = 'Entregado'; }
                        else if (p.estado === 'cancelado') { badge = 'bg-danger'; estadoTexto = 'Cancelado'; }

                        tbody.innerHTML += `
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">${rowNumber}</div>
                                    <small class="text-muted"><i class="far fa-clock me-1"></i>${p.fecha_entrega}</small>
                                </td>
                                <td>${p.cliente_nombre || '<span class="text-muted">Consumidor Final</span>'}</td>
                                
                                <td><span class="badge ${badge} badge-status">${estadoTexto}</span></td>
                                <td class="fw-bold text-dark">${formatCurrency(p.total)}</td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-info me-1" onclick="viewDetails(${p.id}, ${p.total})" title="Detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    ${p.estado === 'pendiente' && puedeGestionar ? `
                                        <button class="btn btn-sm btn-outline-primary me-1" onclick="openEditPedidoModal(${p.id}, '${p.cliente_nombre || ''}', '${escapeHtml(p.fecha_entrega || '')}', '${escapeHtml(p.hora_entrega || '')}')" title="Editar">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                    ` : ''}
                                    ${p.estado !== 'entregado' && p.estado !== 'cancelado' && puedeGestionar ? `
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                Estado
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item" href="#" onclick="updateStatus(${p.id}, 'en_proceso')">En Proceso</a></li>
                                                <li><a class="dropdown-item" href="#" onclick="updateStatus(${p.id}, 'entregado')">Entregado</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#" onclick="updateStatus(${p.id}, 'cancelado')">Cancelar</a></li>
                                            </ul>
                                        </div>
                                    ` : ''}
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    tbody.innerHTML = `<tr><td colspan="5" class="text-center py-5 text-muted">No se encontraron pedidos registrados.</td></tr>`;
                }
            } catch (e) {
                console.error('Error fetching orders:', e);
            }
        }

        async function viewDetails(pedidoId, total) {
            try {
                const res = await fetch(`../backend/api.php?route=get_pedido_detalles&id=${pedidoId}`);
                const data = await res.json();
                const tbody = document.getElementById('detPedidosTableBody');
                tbody.innerHTML = '';

                document.getElementById('detPedidoId').textContent = pedidoId;
                document.getElementById('detPedidoTotal').textContent = formatCurrency(total);

                if (data.success && data.data) {
                    data.data.forEach(d => {
                        tbody.innerHTML += `
                            <tr>
                                <td class="ps-3 fw-semibold">${d.producto_nombre}</td>
                                <td>${d.cantidad}</td>
                                <td class="text-end pe-3">${formatCurrency(d.subtotal)}</td>
                            </tr>
                        `;
                    });
                }

                new bootstrap.Modal(document.getElementById('detallePedidoModal')).show();
            } catch (e) {
                console.error(e);
            }
        }

        async function updateStatus(pedidoId, nuevoEstado) {
            try {
                const res = await fetch('../backend/api.php?route=update_pedido_estado', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ pedido_id: pedidoId, estado: nuevoEstado })
                });
                const data = await res.json();
                if (data.success) {
                    await loadPedidos();
                } else {
                    alert(data.message || 'Error al actualizar el estado');
                }
            } catch (e) {
                console.error(e);
            }
        }

        function openEditPedidoModal(id, clienteNombre, fecha, hora) {
            document.getElementById('editPedidoIdInput').value = id;
            document.getElementById('editPedidoId').textContent = id;
            document.getElementById('editPedidoFecha').value = fecha.split(' ')[0];
            document.getElementById('editPedidoHora').value = hora || '';
            const select = document.getElementById('editPedidoCliente');
            let found = false;
            for (let opt of select.options) {
                if (opt.text === clienteNombre) { opt.selected = true; found = true; break; }
            }
            if (!found) select.value = '';
            new bootstrap.Modal(document.getElementById('editPedidoModal')).show();
        }

        document.getElementById('editPedidoForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = parseInt(document.getElementById('editPedidoIdInput').value);
            const cliente_id = document.getElementById('editPedidoCliente').value;
            const fecha = document.getElementById('editPedidoFecha').value;
            const hora = document.getElementById('editPedidoHora').value;
            if (!fecha || !hora) { alert('Complete fecha y hora de entrega'); return; }

            try {
                const res = await fetch('../backend/api.php?route=update_pedido', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id: id,
                        cliente_id: cliente_id ? parseInt(cliente_id) : null,
                        fecha_entrega: fecha,
                        hora_entrega: hora
                    })
                });
                const data = await res.json();
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('editPedidoModal')).hide();
                    await loadPedidos();
                } else {
                    alert(data.message || 'Error al actualizar pedido');
                }
            } catch (err) {
                console.error(err);
                alert('Error de conexión');
            }
        });
    </script>
</body>
</html>
