<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos - La Vicky</title>
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
    </style>
</head>

<body>
    <div class="container-fluid p-0">
        <!-- Sidebar -->
        <div class="col-md-2 sidebar d-none d-md-block">
            <div class="text-center mb-4">
                <h3 class="text-white">🥖 La Vicky</h3>
            </div>
            <a href="index.php"><i class="fas fa-home me-2"></i> Dashboard</a>
            <a href="inventario.php"><i class="fas fa-box me-2"></i> Inventario</a>
            <a href="productos.php"><i class="fas fa-bread-slice me-2"></i> Productos</a>
            <a href="produccion_manual.php"><i class="fas fa-industry me-2"></i> Prod. Manual</a>
            <a href="pedidos.php" class="active"><i class="fas fa-shopping-cart me-2"></i> Pedidos</a>
            <a href="ventas.php"><i class="fas fa-chart-line me-2"></i> Ventas</a>
            <a href="clientes.php"><i class="fas fa-users me-2"></i> Clientes</a>
            <a href="reportes.php"><i class="fas fa-file-alt me-2"></i> Reportes</a>
            <a href="configuracion.php"><i class="fas fa-cog me-2"></i> Configuración</a>
        </div>

        <!-- Top Navbar -->
        <div class="top-navbar">
            <div>
                <h4 class="m-0">Gestión de Pedidos</h4>
            </div>
            <div>
                <span class="me-3"><i class="fas fa-user-circle"></i> Administrador</span>
                <a href="#" class="btn btn-outline-danger btn-sm" onclick="logout()"><i class="fas fa-sign-out-alt"></i>
                    Salir</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="row">
                <div class="col-md-4">
                    <!-- Crear Pedido (POS View) -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="m-0"><i class="fas fa-cart-plus"></i> Nuevo Pedido</h5>
                        </div>
                        <div class="card-body">
                            <label class="form-label">Cliente (Opcional)</label>
                            <select id="clienteSelect" class="form-select mb-3">
                                <option value="">Consumidor Final</option>
                            </select>

                            <label class="form-label">Fecha de Entrega (Opcional)</label>
                            <input type="date" id="fechaEntrega" class="form-control mb-3">

                            <label class="form-label">Hora de Entrega (Opcional)</label>
                            <input type="time" id="horaEntrega" class="form-control mb-3">

                            <label class="form-label">Producto a añadir</label>
                            <div class="input-group mb-3">
                                <select id="productoSelect" class="form-select">
                                    <option value="" disabled selected>Cargando productos...</option>
                                </select>
                                <button class="btn btn-outline-secondary" type="button"
                                    onclick="addToCart()">Añadir</button>
                            </div>

                            <hr>
                            <h6>Carrito:</h6>
                            <ul class="list-group list-group-flush mb-3" id="cartList">
                                <li class="list-group-item text-muted text-center small">El carrito está vacío</li>
                            </ul>

                            <div class="d-flex justify-content-between fw-bold mb-3 fs-5">
                                <span>TOTAL:</span>
                                <span id="cartTotal">$0.00</span>
                            </div>

                            <button class="btn btn-success w-100" onclick="procesarPedido()"><i
                                    class="fas fa-check"></i> Finalizar Pedido</button>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <!-- Lista de Pedidos -->
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white pt-3 pb-2">
                            <h5 class="m-0">Historial de Pedidos</h5>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover m-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID / Fecha</th>
                                        <th>Cliente</th>
                                        <th>Estado</th>
                                        <th>Total</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="pedidosTableBody">
                                    <tr>
                                        <td colspan="5" class="text-center p-4">Cargando historial...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detalle Pedido -->
    <div class="modal fade" id="detallePedidoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalle del Pedido #<span id="detPedidoId"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cant.</th>
                                <th>Precio</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="detPedidosTableBody"></tbody>
                    </table>
                    <div class="text-end fw-bold fs-5">
                        Total: <span id="detPedidoTotal" class="text-success"></span>
                    </div>
                    <div class="text-end text-muted small">
                        Atendido por: <span id="detPedidoVendedor"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Registrar Entrega -->
    <div class="modal fade" id="registrarEntregaModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Entrega</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="entregaPedidoId">
                    <label class="form-label">Hora de Entrega Real</label>
                    <input type="time" id="horaEntregaReal" class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary w-100" onclick="confirmarEntrega()">Confirmar Entrega</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/common.js"></script>
    <script>
        let availableProducts = [];
        let curCart = [];

        document.addEventListener('DOMContentLoaded', () => {
            loadClientes();
            loadProductos();
            loadPedidos();
        });

        async function loadClientes() {
            try {
                const res = await fetch('../backend/api.php?route=get_clientes');
                const data = await res.json();
                if (data.success && data.data) {
                    const sel = document.getElementById('clienteSelect');
                    data.data.forEach(c => {
                        sel.innerHTML += `<option value="${c.id}">${c.nombre}</option>`;
                    });
                }
            } catch (e) { }
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
                        sel.innerHTML += `<option value="${p.id}" data-precio="${p.precio_venta}">${p.nombre} - $${p.precio_venta}</option>`;
                    });
                }
            } catch (e) { }
        }

        async function loadPedidos() {
            try {
                const res = await fetch('../backend/api.php?route=get_pedidos');
                const data = await res.json();
                const tbody = document.getElementById('pedidosTableBody');
                tbody.innerHTML = '';

                if (data.success && data.data.length > 0) {
                    data.data.forEach(p => {
                        let colorEstado = 'bg-secondary';
                        if (p.estado === 'en_proceso') colorEstado = 'bg-warning text-dark';
                        if (p.estado === 'entregado') colorEstado = 'bg-success';

                        // Parsear la fecha un poco para que sea más legible
                        let fechaArr = p.fecha_pedido.split(' ');
                        let entrega = p.fecha_entrega ? `<br><small class="text-info">Entrega: ${p.fecha_entrega} ${p.hora_entrega || ''}</small>` : '';

                        tbody.innerHTML += `
                            <tr>
                                <td>
                                    <strong>#${p.id}</strong><br>
                                    <small class="text-muted">${fechaArr[0]}</small>
                                    ${entrega}
                                </td>
                                <td>${p.cliente_nombre || '<span class="text-muted">Consumidor Final</span>'}</td>
                                <td>
                                    <select class="form-select form-select-sm badge ${colorEstado} border-0" onchange="cambiarEstadoPedido(${p.id}, this.value)">
                                        <option value="pendiente" class="bg-white text-dark" ${p.estado === 'pendiente' ? 'selected' : ''}>Pendiente</option>
                                        <option value="en_proceso" class="bg-white text-dark" ${p.estado === 'en_proceso' ? 'selected' : ''}>En Proceso</option>
                                        <option value="entregado" class="bg-white text-dark" ${p.estado === 'entregado' ? 'selected' : ''}>Entregado</option>
                                    </select>
                                </td>
                                <td class="fw-bold text-success">$${p.total}</td>
                                <td><small class="text-muted"><i class="fas fa-user-tag me-1"></i>${p.vendedor || 'Sistema'}</small></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-info" title="Ver detalle" onclick="verDetalle(${p.id}, '${p.total}', '${p.vendedor || 'Sistema'}')"><i class="fas fa-eye"></i></button>
                                    <button class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="eliminarPedido(${p.id})"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted p-4">No hay pedidos registrados</td></tr>';
                }
            } catch (e) { }
        }

        function addToCart() {
            const sel = document.getElementById('productoSelect');
            if (!sel.value) return;

            const prodText = sel.options[sel.selectedIndex].text;
            const prodPrecio = parseFloat(sel.options[sel.selectedIndex].getAttribute('data-precio'));

            const exist = curCart.find(item => item.producto_id == sel.value);
            if (exist) exist.cantidad++;
            else {
                curCart.push({
                    producto_id: sel.value,
                    nombre: prodText.split(' - ')[0],
                    precio: prodPrecio,
                    cantidad: 1
                });
            }
            renderCart();
        }

        function renderCart() {
            const list = document.getElementById('cartList');
            const totalE = document.getElementById('cartTotal');
            list.innerHTML = '';

            if (curCart.length === 0) {
                list.innerHTML = '<li class="list-group-item text-muted text-center small">El carrito está vacío</li>';
                totalE.textContent = '$0.00';
                return;
            }

            let total = 0;
            curCart.forEach((item, index) => {
                let subtotal = item.precio * item.cantidad;
                total += subtotal;
                list.innerHTML += `
                    <li class="list-group-item d-flex justify-content-between align-items-center p-2">
                        <div>
                            <span class="badge bg-primary rounded-pill me-2">${item.cantidad}</span>
                            ${item.nombre}
                        </div>
                        <div class="text-end">
                            <span class="text-success me-2">$${subtotal.toFixed(2)}</span>
                            <i class="fas fa-trash text-danger" style="cursor:pointer;" onclick="remCart(${index})"></i>
                        </div>
                    </li>
                `;
            });
            totalE.textContent = '$' + total.toFixed(2);
        }

        function remCart(idx) {
            curCart.splice(idx, 1);
            renderCart();
        }

        async function procesarPedido() {
            if (curCart.length === 0) return alert('El carrito está vacío');

            let total = curCart.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
            let cid = document.getElementById('clienteSelect').value;

            const payload = {
                cliente_id: cid === "" ? null : parseInt(cid),
                total: total,
                fecha_entrega: document.getElementById('fechaEntrega').value || null,
                hora_entrega: document.getElementById('horaEntrega').value || null,
                detalles: curCart
            };

            try {
                const res = await fetch('../backend/api.php?route=add_pedido', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    curCart = [];
                    renderCart();
                    loadPedidos();
                    alert(data.message);
                } else alert('Error: ' + data.message);
            } catch (e) { alert('Error de conexión'); }
        }

        async function cambiarEstadoPedido(id, nuevoEstado) {
            if (nuevoEstado === 'entregado') {
                document.getElementById('entregaPedidoId').value = id;
                document.getElementById('horaEntregaReal').value = new Date().toLocaleTimeString('it-IT').substring(0, 5);
                const modal = new bootstrap.Modal(document.getElementById('registrarEntregaModal'));
                modal.show();
                return;
            }

            try {
                const res = await fetch('../backend/api.php?route=update_pedido_estado', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id, estado: nuevoEstado })
                });
                const data = await res.json();
                if (data.success) {
                    loadPedidos();
                } else alert('Error: ' + data.message);
            } catch (e) { alert('Error de conexión'); }
        }

        async function confirmarEntrega() {
            const id = document.getElementById('entregaPedidoId').value;
            const hora = document.getElementById('horaEntregaReal').value;
            if (!hora) return alert('Debe ingresar la hora de entrega');

            try {
                const res = await fetch('../backend/api.php?route=update_pedido_estado', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id, estado: 'entregado', hora_entrega_real: hora })
                });
                const data = await res.json();
                if (data.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('registrarEntregaModal'));
                    modal.hide();
                    loadPedidos();
                } else alert('Error: ' + data.message);
            } catch (e) { alert('Error de conexión'); }
        }

        async function verDetalle(id, total, vendedor) {
            document.getElementById('detPedidoId').innerText = id;
            document.getElementById('detPedidoTotal').innerText = '$' + total;
            document.getElementById('detPedidoVendedor').innerText = vendedor;
            const tbody = document.getElementById('detPedidosTableBody');
            tbody.innerHTML = '<tr><td colspan="4" class="text-center">Cargando...</td></tr>';
            
            const modal = new bootstrap.Modal(document.getElementById('detallePedidoModal'));
            modal.show();

            try {
                const res = await fetch('../backend/api.php?route=get_pedido_detalles&id=' + id);
                const data = await res.json();
                if (data.success) {
                    tbody.innerHTML = '';
                    data.data.forEach(d => {
                        tbody.innerHTML += `
                            <tr>
                                <td>${d.producto_nombre}</td>
                                <td>${d.cantidad}</td>
                                <td>$${d.precio_unitario}</td>
                                <td class="text-end">$${d.subtotal}</td>
                            </tr>
                        `;
                    });
                }
            } catch (e) { tbody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error al cargar</td></tr>'; }
        }

        async function eliminarPedido(id) {
            if (!confirm('¿Está seguro de eliminar este pedido? Esta acción no se puede deshacer.')) return;
            try {
                const res = await fetch('../backend/api.php?route=delete_pedido', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });
                const data = await res.json();
                if (data.success) {
                    loadPedidos();
                } else alert('Error: ' + data.message);
            } catch (e) { alert('Error de conexión'); }
        }

        function logout() { fetch('../backend/api.php?route=logout').then(() => window.location.href = 'login.html'); }
    </script>
</body>

</html>
