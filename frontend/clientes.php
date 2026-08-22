<?php
// frontend/clientes.php
session_start();
require_once __DIR__ . '/includes/permisos.php';

if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

if (!tiene_permiso('clientes.ver')) {
    header("Location: index.php");
    exit();
}

$pageTitle = "Clientes";
$pageHeader = "Gestión de Clientes";
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
                <!-- Directory Card -->
                <div class="card shadow-sm border-0 border-top border-4 border-primary">
                    <div
                        class="card-header bg-white d-flex flex-column flex-md-row justify-content-between align-items-md-center py-3 gap-3">
                        <div>
                            <h5 class="mb-0 fw-bold text-dark"><i
                                    class="fas fa-address-book me-2 text-primary"></i>Directorio de Clientes</h5>
                            <p class="text-muted small mb-0">Base de datos de clientes y fidelidad</p>
                        </div>
                        <?php if (tiene_permiso('clientes.gestionar')): ?>
                            <button class="btn btn-primary shadow-sm fw-bold px-4" data-bs-toggle="modal"
                                data-bs-target="#addClienteModal">
                                <i class="fas fa-user-plus me-2"></i>NUEVO CLIENTE
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">N.º</th>
                                        <th>Cliente</th>
                                        <th>DNI</th>
                                        <th>Contacto</th>
                                        <th>Email</th>
                                        <th>Dirección</th>


                                        <th class="text-end pe-4">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="clientesTableBody">
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                                            Cargando clientes...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div id="clientPagination" class="my-3 d-flex justify-content-center"></div>


                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Historial -->
    <div class="modal fade" id="historialModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-history me-2"></i>Historial de Compras</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Fecha</th>
                                    <th>Ref ID</th>
                                    <th>Total</th>
                                    <th class="text-end pe-4">Ganancia Est.</th>
                                </tr>
                            </thead>
                            <tbody id="historialTableBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo Cliente -->
    <div class="modal fade" id="addClienteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <form id="addClienteForm">
                    <div class="modal-header bg-primary text-white border-0">
                        <h5 class="modal-title fw-bold"><i class="fas fa-user-plus me-2"></i>Registrar Cliente</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Nombre Completo</label>
                            <input type="text" name="nombre" class="form-control" required maxlength="100"
                                placeholder="Ej. Juan Pérez">
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted text-uppercase">Email</label>
                                <input type="email" name="email" class="form-control" maxlength="100"
                                    placeholder="juan@ejemplo.com">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted text-uppercase">DNI</label>
                                <input type="text" name="dni" class="form-control" maxlength="20" placeholder="12345678"
                                    required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted text-uppercase">Teléfono</label>
                                <input type="tel" name="telefono" class="form-control" maxlength="30"
                                    placeholder="0000-0000">
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-bold small text-muted text-uppercase">Dirección</label>
                            <textarea name="direccion" class="form-control" rows="2" maxlength="255"
                                placeholder="Dirección domiciliar..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-3 bg-light rounded-bottom">
                        <button type="button" class="btn btn-link link-secondary text-decoration-none"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">GUARDAR CLIENTE</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Cliente -->
    <div class="modal fade" id="editClienteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <form id="editClienteForm">
                    <div class="modal-header bg-warning border-0 text-dark">
                        <h5 class="modal-title fw-bold"><i class="fas fa-user-edit me-2"></i>Editar Información</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <input type="hidden" name="id" id="editClientId">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Nombre Completo</label>
                            <input type="text" name="nombre" id="editClientNombre" class="form-control" required
                                maxlength="100">
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted text-uppercase">Email</label>
                                <input type="email" name="email" id="editClientEmail" class="form-control"
                                    maxlength="100">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted text-uppercase">DNI</label>
                                <input type="text" name="dni" id="editClientDni" class="form-control" maxlength="20"
                                    placeholder="12345678" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted text-uppercase">Teléfono</label>
                                <input type="tel" name="telefono" id="editClientTelefono" class="form-control"
                                    maxlength="30">
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-bold small text-muted text-uppercase">Dirección</label>
                            <textarea name="direccion" id="editClientDireccion" class="form-control" rows="2"
                                maxlength="255"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-3 bg-light rounded-bottom">
                        <button type="button" class="btn btn-link link-secondary text-decoration-none"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit"
                            class="btn btn-warning text-dark px-4 fw-bold shadow-sm">ACTUALIZAR</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <?php include 'includes/footer.php'; ?>
    <script>
        let clientsData = [];
        let currentPage = 1;
        const itemsPerPage = 10;

        document.addEventListener('DOMContentLoaded', () => {
            loadClientes(1);
        });

        async function loadClientes(page = 1) {
            currentPage = page;
            try {
                const offset = (page - 1) * itemsPerPage;
                const res = await fetch(`../backend/api.php?route=get_clientes&limit=${itemsPerPage}&offset=${offset}`);
                const data = await res.json();
                const tbody = document.getElementById('clientesTableBody');
                tbody.innerHTML = '';
                if (data.success && data.data && data.data.length > 0) {
                    clientsData = data.data;
                    const puedeGestionar = (typeof tienePermiso === 'function' ? tienePermiso('clientes.gestionar') : true);
                    data.data.forEach((c, index) => {
                        const rowNumber = offset + index + 1;
                        let badge = '<span class="badge bg-secondary">Casual</span>';
                        if (c.puntos_fidelidad > 50) badge = '<span class="badge bg-warning text-dark"><i class="fas fa-crown me-1"></i>Oro</span>';
                        else if (c.puntos_fidelidad > 20) badge = '<span class="badge bg-info text-dark">Plata</span>';
                        else if (c.puntos_fidelidad > 0) badge = '<span class="badge bg-light text-dark border">Bronce</span>';
                        tbody.innerHTML += `
                    <tr>
                        <td class="ps-4 text-muted fw-bold">${rowNumber}</td>
                        <td><div class="fw-bold text-dark">${c.nombre}</div></td>
                        <td>${c.dni || ''}</td>
                        <td class="small">${c.telefono || '<span class="text-muted">N/A</span>'}</td>
                        <td class="small text-muted">${c.email || ''}</td>
                        <td class="small text-muted">${c.direccion || 'No registrada'}</td>
                        <td class="text-end pe-4">
                            <button class="btn btn-sm btn-outline-info me-1" onclick="viewHistory(${c.id})" title="Historial"><i class="fas fa-history"></i></button>
                            ${puedeGestionar ? `
                                <button class="btn btn-sm btn-outline-secondary me-1" onclick="openEditModal(${c.id})" title="Editar"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteClient(${c.id})" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                            ` : ''}
                        </td>
                    </tr>
                `;
                    });
                    const totalPages = Math.ceil(data.total / itemsPerPage);
                    renderPagination(data.total, itemsPerPage, page);
                } else {
                    tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-muted">No se encontraron clientes registrados.</td></tr>`;
                    document.getElementById('clientPagination').innerHTML = '';
                }
            } catch (e) {
                console.error(e);
            }
        }

        function renderPagination(total, limit, page) {
            const totalPages = Math.ceil(total / limit);
            const nav = document.getElementById('clientPagination');
            nav.innerHTML = '';
            if (totalPages <= 1) return;
            let html = '<ul class="pagination justify-content-center">';
            const prevDisabled = page <= 1 ? ' disabled' : '';
            html += `<li class="page-item${prevDisabled}"><a class="page-link" href="#" onclick="loadClientes(${page - 1}); return false;">&laquo;</a></li>`;
            const maxVisible = 5;
            let start = Math.max(1, page - Math.floor(maxVisible / 2));
            let end = Math.min(totalPages, start + maxVisible - 1);
            if (end - start < maxVisible - 1) {
                start = Math.max(1, end - maxVisible + 1);
            }
            for (let i = start; i <= end; i++) {
                const active = i === page ? ' active' : '';
                html += `<li class="page-item${active}"><a class="page-link" href="#" onclick="loadClientes(${i}); return false;">${i}</a></li>`;
            }
            const nextDisabled = page >= totalPages ? ' disabled' : '';
            html += `<li class="page-item${nextDisabled}"><a class="page-link" href="#" onclick="loadClientes(${page + 1}); return false;">&raquo;</a></li>`;
            html += '</ul>';
            nav.innerHTML = html;
        }

        async function viewHistory(id) {
            try {
                const res = await fetch(`../backend/api.php?route=get_cliente_historial&id=${id}`);
                const data = await res.json();
                const tbody = document.getElementById('historialTableBody');
                tbody.innerHTML = '';

                if (data.success && data.data && data.data.length > 0) {
                    data.data.forEach(h => {
                        tbody.innerHTML += `
                            <tr>
                                <td class="ps-4">${h.fecha_venta}</td>
                                <td class="fw-bold text-primary">#${h.id}</td>
                                <td class="fw-bold">${formatCurrency(h.total)}</td>
                                <td class="text-end pe-4 text-success fw-bold">${formatCurrency(h.ganancias)}</td>
                            </tr>
                        `;
                    });
                } else {
                    tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-4">Este cliente no registra compras históricas aún.</td></tr>`;
                }

                new bootstrap.Modal(document.getElementById('historialModal')).show();
            } catch (e) {
                console.error(e);
            }
        }

        function openEditModal(id) {
            const client = clientsData.find(c => c.id == id);
            if (!client) return;

            document.getElementById('editClientId').value = client.id;
            document.getElementById('editClientNombre').value = client.nombre;
            document.getElementById('editClientEmail').value = client.email || '';
            document.getElementById('editClientTelefono').value = client.telefono || '';
            document.getElementById('editClientDireccion').value = client.direccion || '';

            new bootstrap.Modal(document.getElementById('editClienteModal')).show();
        }

        document.getElementById('addClienteForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const payload = Object.fromEntries(formData);

            try {
                const res = await fetch('../backend/api.php?route=add_cliente', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('addClienteModal')).hide();
                    e.target.reset();
                    await loadClientes();
                } else {
                    showAlert(data.message || 'Error al guardar', 'error');
                }
            } catch (err) {
                console.error(err);
            }
        });

        document.getElementById('editClienteForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const payload = Object.fromEntries(formData);

            try {
                const res = await fetch('../backend/api.php?route=update_cliente', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('editClienteModal')).hide();
                    await loadClientes();
                } else {
                    showAlert(data.message || 'Error al actualizar', 'error');
                }
            } catch (err) {
                console.error(err);
            }
        });

        async function deleteClient(id) {
            if (!(await showConfirm('¿Desea eliminar a este cliente?'))) return;
            try {
                const res = await fetch('../backend/api.php?route=delete_cliente', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });
                const data = await res.json();
                if (data.success) {
                    await loadClientes();
                } else {
                    showAlert(data.message || 'Error al eliminar', 'error');
                }
            } catch (err) {
                console.error(err);
            }
        }
    </script>
</body>

</html>