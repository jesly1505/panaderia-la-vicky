<?php
// frontend/inventario.php
session_start();
require_once __DIR__ . '/includes/permisos.php';

if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

if (!tiene_permiso('inventario.ver')) {
    header("Location: index.php");
    exit();
}

$pageTitle = "Inventario";
$pageHeader = "Inventario";
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
                <!-- Alerts Container -->
                <div id="lowStockAlertContainer" class="mb-4"></div>

                <div class="card shadow-sm border-0">
                    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 bg-white py-3">
                        <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-boxes me-2 text-primary"></i>Lista de Insumos y Materias Primas</h5>
                        <div class="d-flex flex-wrap gap-2">
                            <?php if (tiene_permiso('inventario.gestionar')): ?>
                                <button class="btn btn-sm btn-warning shadow-sm text-dark" data-bs-toggle="modal" data-bs-target="#registrarCompraModal">
                                    <i class="fas fa-shopping-basket me-1"></i> Registrar Compra
                                </button>
                                <button class="btn btn-sm btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addInsumoModal">
                                    <i class="fas fa-plus me-1"></i> Nuevo Insumo
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Insumo</th>
                                        <th>Proveedor</th>
                                        <th>Cantidad</th>
                                        <th>Mínimo</th>
                                        <th>Costo Unit.</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="insumosTableBody">
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                            Cargando insumos...
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

    <!-- Modal Nuevo Insumo -->
    <div class="modal fade" id="addInsumoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <form id="addInsumoForm">
                    <div class="modal-header bg-primary text-white border-0">
                        <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2"></i>Registrar Nuevo Insumo</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Nombre del Insumo</label>
                            <input type="text" name="nombre" class="form-control py-2" required maxlength="100" placeholder="Ej. Harina de Trigo">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Unidad de Medida</label>
                            <select name="unidad_medida" class="form-select py-2" required>
                                <option value="Kg">Kilogramos (Kg)</option>
                                <option value="Litros">Litros (L)</option>
                                <option value="Gramos">Gramos (g)</option>
                                <option value="Unidades">Unidades</option>
                            </select>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Proveedor Predet.</label>
                                <select name="proveedor_id" class="form-select py-2 provider-select">
                                    <option value="">Seleccione...</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Stock Inicial</label>
                                <input type="number" step="0.01" min="0" max="999999.99" name="stock_inicial" class="form-control py-2" value="0">
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Stock Mínimo</label>
                                <input type="number" step="0.01" min="0" max="999999.99" name="stock_minimo" class="form-control py-2" value="5">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Precio Costo</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" min="0.01" max="999999.99" name="precio_costo" class="form-control py-2" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-3 bg-light">
                        <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">Guardar Insumo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Registrar Compra -->
    <div class="modal fade" id="registrarCompraModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <form id="registrarCompraForm">
                    <div class="modal-header bg-warning border-0">
                        <h5 class="modal-title fw-bold text-dark"><i class="fas fa-shopping-cart me-2"></i>Registrar Compra de Insumo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Insumo</label>
                            <select name="insumo_id" id="insumoSelectCompra" class="form-select py-2" required></select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Proveedor</label>
                            <select name="proveedor_id" class="form-select py-2 provider-select" required></select>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Cantidad Comprada</label>
                                <input type="number" step="0.01" min="0.01" max="999999.99" name="cantidad" class="form-control py-2" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Costo Unitario ($)</label>
                                <input type="number" step="0.01" min="0.01" max="999999.99" name="costo_unitario" class="form-control py-2" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-3 bg-light">
                        <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning text-dark fw-bold px-4 shadow-sm">Registrar Compra</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Ajustar Stock -->
    <div class="modal fade" id="adjustStockModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg">
                <form id="adjustStockForm">
                    <input type="hidden" name="insumo_id" id="adjustInsumoId">
                    <div class="modal-header bg-light border-0">
                        <h5 class="modal-title fw-bold" id="adjustInsumoNombre">Ajustar Stock</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4 text-center">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-semibold">Cantidad a Añadir (+)</label>
                            <input type="number" step="0.01" min="0.01" max="999999.99" name="cantidad" class="form-control form-control-lg text-center fw-bold" placeholder="0.00" required>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-3 bg-light justify-content-center">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Insumo -->
    <div class="modal fade" id="editInsumoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <form id="editInsumoForm">
                    <input type="hidden" name="id">
                    <div class="modal-header bg-warning border-0">
                        <h5 class="modal-title fw-bold text-dark"><i class="fas fa-pen me-2"></i>Editar Insumo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Nombre del Insumo</label>
                            <input type="text" name="nombre" class="form-control py-2" required maxlength="100" placeholder="Ej. Harina de Trigo">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Unidad de Medida</label>
                            <select name="unidad_medida" class="form-select py-2" required>
                                <option value="Unidades">Unidades</option>
                                <option value="Kg">Kg</option>
                                <option value="Litros">Litros</option>
                                <option value="Metros">Metros</option>
                            </select>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Proveedor Predet.</label>
                                <select name="proveedor_id" class="form-select py-2 provider-select">
                                    <option value="">Seleccione...</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Stock Mínimo</label>
                                <input type="number" step="0.01" min="0" max="999999.99" name="stock_minimo" class="form-control py-2" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Precio Costo</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0.01" max="999999.99" name="precio_costo" class="form-control py-2" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-3 bg-light">
                        <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning text-dark fw-bold px-4 shadow-sm">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <?php include 'includes/footer.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            await loadInsumos();
            await loadProveedores();
            await loadAlerts();
        });

        async function loadAlerts() {
            try {
                const res = await fetch('../backend/api.php?route=get_low_stock_alerts');
                const data = await res.json();
                const container = document.getElementById('lowStockAlertContainer');
                if (data.success && data.data && data.data.length > 0) {
                    let items = data.data.map(i => `<b>${i.nombre}</b> (${i.stock_actual} ${i.unidad_medida})`).join(', ');
                    container.innerHTML = `
                        <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-0">
                            <i class="fas fa-exclamation-triangle fs-4 me-3 text-warning"></i>
                            <div>
                                <h6 class="alert-heading fw-bold mb-1">¡Alerta de Insumos con Stock Bajo!</h6>
                                <p class="mb-0 small">${items}</p>
                            </div>
                        </div>
                    `;
                } else {
                    container.innerHTML = '';
                }
            } catch (e) {
                console.error(e);
            }
        }

        let insumosData = [];

        async function loadInsumos() {
            try {
                const res = await fetch('../backend/api.php?route=get_insumos');
                const data = await res.json();
                const tbody = document.getElementById('insumosTableBody');
                const selectCompra = document.getElementById('insumoSelectCompra');
                tbody.innerHTML = '';
                if (selectCompra) selectCompra.innerHTML = '<option value="" disabled selected>Seleccione...</option>';
                insumosData = (data.success && data.data) ? data.data : [];

                if (data.success && data.data && data.data.length > 0) {
                    const puedeGestionar = (typeof tienePermiso === 'function' ? tienePermiso('inventario.gestionar') : true);
                    const puedeEliminar = (typeof tienePermiso === 'function' ? tienePermiso('inventario.eliminar') : true);

                    data.data.forEach(i => {
                        if (selectCompra) {
                            selectCompra.innerHTML += `<option value="${i.id}">${i.nombre}</option>`;
                        }

                        let stockClass = 'text-dark fw-bold';
                        let badge = '';
                        if (parseFloat(i.stock_actual) <= parseFloat(i.stock_minimo)) {
                            stockClass = 'text-danger fw-bold';
                            badge = '<span class="badge bg-danger ms-2">Bajo</span>';
                        }

                        tbody.innerHTML += `
                            <tr>
                                <td class="fw-semibold">${i.nombre}</td>
                                <td>${i.proveedor_nombre || '<span class="text-muted small">No asignado</span>'}</td>
                                <td><span class="${stockClass}">${parseFloat(i.stock_actual).toFixed(2)} ${i.unidad_medida}</span> ${badge}</td>
                                <td><span class="text-muted small">${parseFloat(i.stock_minimo).toFixed(2)} ${i.unidad_medida}</span></td>
                                <td>${formatCurrency(i.precio_costo)}</td>
                                <td class="text-end">
                                    ${puedeGestionar ? `
                                        <button class="btn btn-sm btn-outline-success me-1" onclick="openAdjustModal(${i.id}, '${escapeHtml(i.nombre)}')">
                                            <i class="fas fa-plus"></i> Ajustar
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary me-1" onclick="openEditInsumoModal(${i.id})">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                    ` : ''}
                                    ${puedeEliminar ? `
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteInsumo(${i.id})">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    ` : ''}
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    tbody.innerHTML = `<tr><td colspan="6" class="text-center py-5 text-muted">No hay insumos registrados.</td></tr>`;
                }
            } catch (e) {
                console.error('Error fetching insumos:', e);
            }
        }

        async function loadProveedores() {
            try {
                const res = await fetch('../backend/api.php?route=get_proveedores');
                const data = await res.json();
                const selects = document.querySelectorAll('.provider-select');
                selects.forEach(select => {
                    select.innerHTML = '<option value="">Seleccione proveedor...</option>';
                    if (data.success && data.data) {
                        data.data.forEach(p => {
                            select.innerHTML += `<option value="${p.id}">${p.nombre}</option>`;
                        });
                    }
                });
            } catch (e) {
                console.error(e);
            }
        }

        document.getElementById('addInsumoForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const payload = Object.fromEntries(formData);
            
            try {
                const res = await fetch('../backend/api.php?route=add_insumo', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('addInsumoModal')).hide();
                    e.target.reset();
                    await loadInsumos();
                    await loadAlerts();
                } else {
                    alert(data.message || 'Error al guardar insumo');
                }
            } catch (err) {
                console.error(err);
            }
        });

        document.getElementById('registrarCompraForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const payload = Object.fromEntries(formData);

            try {
                const res = await fetch('../backend/api.php?route=registrar_compra_insumo', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('registrarCompraModal')).hide();
                    e.target.reset();
                    await loadInsumos();
                    await loadAlerts();
                } else {
                    alert(data.message || 'Error al registrar la compra');
                }
            } catch (err) {
                console.error(err);
            }
        });

        function openAdjustModal(id, nombre) {
            document.getElementById('adjustInsumoId').value = id;
            document.getElementById('adjustInsumoNombre').textContent = `Ajustar: ${nombre}`;
            const modal = new bootstrap.Modal(document.getElementById('adjustStockModal'));
            modal.show();
        }

        function openEditInsumoModal(id) {
            const insumo = insumosData.find(x => parseInt(x.id) === parseInt(id));
            if (!insumo) {
                alert('No se encontró el insumo seleccionado.');
                return;
            }
            const form = document.getElementById('editInsumoForm');
            form.elements['id'].value = insumo.id;
            form.elements['nombre'].value = insumo.nombre;

            const unidadSelect = form.elements['unidad_medida'];
            if (![...unidadSelect.options].some(o => o.value === insumo.unidad_medida)) {
                const opt = document.createElement('option');
                opt.value = insumo.unidad_medida;
                opt.textContent = insumo.unidad_medida;
                unidadSelect.appendChild(opt);
            }
            unidadSelect.value = insumo.unidad_medida;

            form.elements['proveedor_id'].value = insumo.proveedor_id || '';
            form.elements['stock_minimo'].value = insumo.stock_minimo;
            form.elements['precio_costo'].value = insumo.precio_costo;
            new bootstrap.Modal(document.getElementById('editInsumoModal')).show();
        }

        document.getElementById('editInsumoForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const payload = Object.fromEntries(formData);

            try {
                const res = await fetch('../backend/api.php?route=update_insumo', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('editInsumoModal')).hide();
                    await loadInsumos();
                    await loadAlerts();
                } else {
                    alert(data.message || 'Error al actualizar el insumo');
                }
            } catch (err) {
                console.error(err);
            }
        });

        document.getElementById('adjustStockForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('adjustInsumoId').value;
            const cant = e.target.querySelector('input[name="cantidad"]').value;

            try {
                const res = await fetch('../backend/api.php?route=adjust_stock', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ insumo_id: id, cantidad: cant })
                });
                const data = await res.json();
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('adjustStockModal')).hide();
                    e.target.reset();
                    await loadInsumos();
                    await loadAlerts();
                } else {
                    alert(data.message || 'Error al ajustar stock');
                }
            } catch (err) {
                console.error(err);
            }
        });

        async function deleteInsumo(id) {
            if (!confirm('¿Está seguro de eliminar este insumo?')) return;
            try {
                const res = await fetch('../backend/api.php?route=delete_insumo', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });
                const data = await res.json();
                if (data.success) {
                    await loadInsumos();
                    await loadAlerts();
                } else {
                    alert(data.message || 'Error al eliminar insumo');
                }
            } catch (e) {
                console.error(e);
            }
        }

        function escapeHtml(text) {
            if (!text) return '';
            return text.replace(/'/g, "\\'").replace(/"/g, '&quot;');
        }
    </script>
</body>
</html>
