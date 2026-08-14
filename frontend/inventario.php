<?php
session_start();
require_once __DIR__ . '/includes/permisos.php';
if (!isset($_SESSION['usuario'])) {
    header("Location: login.html");
    exit();
}
if (!tiene_permiso('inventario.ver')) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - La Vicky</title>
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
        <?php $active = 'inventario'; $titulo = 'Gestión de Inventario (Insumos)'; include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <div class="row mb-4">
                <div class="col-md-12">
                    <div id="lowStockAlertContainer"></div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center pt-4 pb-3">
                    <h5 class="card-title m-0">Lista de Insumos Actuales</h5>
                    <div class="btn-group">
                        <?php if (tiene_permiso('proveedores.ver', 'proveedores.gestionar')): ?>
                        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#proveedoresModal" onclick="loadProveedoresTable()">
                            <i class="fas fa-truck"></i> Proveedores
                        </button>
                        <?php endif; ?>
                        <?php if (tiene_permiso('inventario.gestionar')): ?>
                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#registrarCompraModal">
                            <i class="fas fa-shopping-basket"></i> Registrar Compra
                        </button>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addInsumoModal">
                            <i class="fas fa-plus"></i> Nuevo Insumo
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Insumo</th>
                                    <th>Proveedor</th>
                                    <th>Cantidad</th>
                                    <th>Mínimo</th>
                                    <th>Costo Unit.</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="insumosTableBody">
                                <tr>
                                    <td colspan="6" class="text-center">Cargando...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo Insumo -->
    <div class="modal fade" id="addInsumoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addInsumoForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Registrar Nuevo Insumo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre del Insumo</label>
                            <input type="text" name="nombre" class="form-control" required placeholder="Ej. Harina de Trigo">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Unidad de Medida</label>
                            <select name="unidad_medida" class="form-select" required>
                                <option value="Kg">Kilogramos (Kg)</option>
                                <option value="Litros">Litros (L)</option>
                                <option value="Gramos">Gramos (g)</option>
                                <option value="Unidades">Unidades</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Proveedor Predeterminado</label>
                                <select name="proveedor_id" class="form-select provider-select">
                                    <option value="">Seleccione o deje vacío</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Cantidad Inicial</label>
                                <input type="number" step="0.01" name="stock_inicial" class="form-control" value="0">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Stock Mínimo</label>
                                <input type="number" step="0.01" name="stock_minimo" class="form-control" value="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Precio Costo Inicial</label>
                                <input type="number" step="0.01" name="precio_costo" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Registrar Compra -->
    <div class="modal fade" id="registrarCompraModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="registrarCompraForm">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title"><i class="fas fa-shopping-cart"></i> Registrar Compra de Insumos</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Insumo</label>
                            <select name="insumo_id" id="insumoSelectCompra" class="form-select" required>
                                <!-- Se llena dinámicamente -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Proveedor</label>
                            <select name="proveedor_id" class="form-select provider-select" required>
                                <!-- Se llena dinámicamente -->
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Cantidad Comprada</label>
                                <input type="number" step="0.01" name="cantidad" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Precio de Compra (Unitario)</label>
                                <input type="number" step="0.01" name="precio_compra" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning">Finalizar Compra</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Proveedores -->
    <div class="modal fade" id="proveedoresModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Gestión de Proveedores</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if (tiene_permiso('proveedores.gestionar')): ?>
                    <form id="addProveedorForm" class="row g-2 mb-4 border-bottom pb-3">
                        <div class="col-md-4">
                            <input type="text" name="nombre" class="form-control form-control-sm" placeholder="Nombre Empresa" required>
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="contacto" class="form-control form-control-sm" placeholder="Contacto">
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="telefono" class="form-control form-control-sm" placeholder="Teléfono">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-success btn-sm w-100"><i class="fas fa-plus"></i></button>
                        </div>
                    </form>
                    <?php endif; ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Contacto</th>
                                    <th>Teléfono</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="proveedoresTableBody">
                                <!-- Se llena dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/common.js"></script>
    <script>
        let availableInsumos = [];
        let availableProviders = [];

        document.addEventListener('DOMContentLoaded', () => {
            loadInsumos();
            loadProveedores();
        });

        async function loadInsumos() {
            try {
                const response = await fetch('../backend/api.php?route=get_insumos');
                const data = await response.json();
                const tbody = document.getElementById('insumosTableBody');
                const alertContainer = document.getElementById('lowStockAlertContainer');
                const selectCompra = document.getElementById('insumoSelectCompra');
                
                tbody.innerHTML = '';
                alertContainer.innerHTML = '';
                selectCompra.innerHTML = '<option value="" disabled selected>Seleccione insumo...</option>';

                if (data.success && data.data.length > 0) {
                    availableInsumos = data.data;
                    data.data.forEach(insumo => {
                        let isLow = parseFloat(insumo.stock_actual) <= parseFloat(insumo.stock_minimo);
                        let stockClass = isLow ? 'badge bg-danger text-white' : 'badge bg-success';
                        
                        if (isLow) {
                            alertContainer.innerHTML += `
                                <div class="alert alert-danger alert-dismissible fade show mb-2 py-2" role="alert">
                                    <i class="fas fa-exclamation-triangle"></i> <strong>Cantidad Baja:</strong> ${insumo.nombre} tiene solo ${insumo.stock_actual} ${insumo.unidad_medida}.
                                    <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
                                </div>
                            `;
                        }

                        selectCompra.innerHTML += `<option value="${insumo.id}">${insumo.nombre} (${insumo.unidad_medida})</option>`;

                        tbody.innerHTML += `
                            <tr>
                                <td>
                                    <div class="fw-bold">${insumo.nombre}</div>
                                    <small class="text-muted">${insumo.unidad_medida}</small>
                                </td>
                                <td><span class="text-muted small">${insumo.proveedor_nombre || 'No asignado'}</span></td>
                                <td><span class="${stockClass}">${insumo.stock_actual} ${insumo.unidad_medida}</span></td>
                                <td><small>${insumo.stock_minimo}</small></td>
                                <td>$${parseFloat(insumo.precio_costo).toFixed(2)}</td>
                                <td>
                                    <div class="btn-group">
                                        ${tienePermiso('inventario.gestionar') ? `
                                        <button class="btn btn-sm btn-outline-success" onclick="adjustStock(${insumo.id}, 1)"><i class="fas fa-plus"></i></button>
                                        <button class="btn btn-sm btn-outline-warning" onclick="adjustStock(${insumo.id}, -1)"><i class="fas fa-minus"></i></button>` : ''}
                                        ${tienePermiso('inventario.eliminar') ? `
                                        <button class="btn btn-sm btn-outline-danger ms-1" onclick="deleteInsumo(${insumo.id})"><i class="fas fa-trash"></i></button>` : ''}
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No hay insumos registrados.</td></tr>';
                }
            } catch (err) { }
        }

        async function loadProveedores() {
            try {
                const res = await fetch('../backend/api.php?route=get_proveedores');
                const data = await res.json();
                if (data.success) {
                    availableProviders = data.data;
                    const selects = document.querySelectorAll('.provider-select');
                    selects.forEach(sel => {
                        sel.innerHTML = '<option value="">Seleccione proveedor...</option>';
                        availableProviders.forEach(p => {
                            sel.innerHTML += `<option value="${p.id}">${p.nombre}</option>`;
                        });
                    });
                }
            } catch (e) { }
        }

        async function loadProveedoresTable() {
            try {
                const res = await fetch('../backend/api.php?route=get_proveedores');
                const data = await res.json();
                const tbody = document.getElementById('proveedoresTableBody');
                tbody.innerHTML = '';
                if (data.success) {
                    data.data.forEach(p => {
                        tbody.innerHTML += `
                            <tr>
                                <td>${p.nombre}</td>
                                <td>${p.contacto || '-'}</td>
                                <td>${p.telefono || '-'}</td>
                                <td class="text-end">
                                    ${tienePermiso('proveedores.gestionar') ? `<button class="btn btn-sm btn-outline-danger" onclick="deleteProveedor(${p.id})"><i class="fas fa-trash"></i></button>` : ''}
                                </td>
                            </tr>
                        `;
                    });
                }
            } catch (e) { }
        }

        // Simplified Form Handlers
        async function handleForm(formId, route, modalId) {
            document.getElementById(formId).addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(e.target);
                const res = await fetch(`../backend/api.php?route=${route}`, { method: 'POST', body: formData });
                const data = await res.json();
                if (data.success) {
                    if (modalId) bootstrap.Modal.getInstance(document.getElementById(modalId)).hide();
                    e.target.reset();
                    loadInsumos();
                    loadProveedores();
                    if (formId === 'addProveedorForm') loadProveedoresTable();
                    alert(data.message);
                } else alert('Error: ' + data.message);
            });
        }

        handleForm('addInsumoForm', 'add_insumo', 'addInsumoModal');
        handleForm('addProveedorForm', 'add_proveedor', null);
        handleForm('registrarCompraForm', 'registrar_compra_insumo', 'registrarCompraModal');

        async function adjustStock(id, qty) {
            const formData = new FormData();
            formData.append('id', id);
            formData.append('cantidad', qty);
            await fetch('../backend/api.php?route=adjust_stock', { method: 'POST', body: formData });
            loadInsumos();
        }

        async function deleteInsumo(id) {
            if (!confirm('¿Seguro?')) return;
            const formData = new FormData();
            formData.append('id', id);
            await fetch('../backend/api.php?route=delete_insumo', { method: 'POST', body: formData });
            loadInsumos();
        }

        async function deleteProveedor(id) {
            if (!confirm('¿Eliminar proveedor?')) return;
            const formData = new FormData();
            formData.append('id', id);
            const res = await fetch('../backend/api.php?route=delete_proveedor', { method: 'POST', body: formData });
            const data = await res.json();
            if(data.success) loadProveedoresTable();
            else alert(data.message);
            loadProveedores();
        }

        function logout() { fetch('../backend/api.php?route=logout').then(() => window.location.href = 'login.html'); }
    </script>
</body>

</html>
