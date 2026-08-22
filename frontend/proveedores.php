<?php
// frontend/proveedores.php
session_start();
require_once __DIR__ . '/includes/permisos.php';

if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

if (!tiene_permiso('proveedores.ver')) {
    header("Location: index.php");
    exit();
}

$pageTitle = "Proveedores";
$pageHeader = "Gestión de Proveedores";
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
                <div class="card shadow-sm border-0 border-top border-4 border-success">
                    <div class="card-header bg-white d-flex flex-column flex-md-row justify-content-between align-items-md-center py-3 gap-3">
                        <div>
                            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-truck me-2 text-success"></i>Directorio de Proveedores</h5>
                            <p class="text-muted small mb-0">Proveedores de materias primas e insumos</p>
                        </div>
                        <?php if (tiene_permiso('proveedores.gestionar')): ?>
                            <button class="btn btn-success shadow-sm fw-bold px-4" data-bs-toggle="modal" data-bs-target="#addProveedorModal">
                                <i class="fas fa-plus me-2"></i>NUEVO PROVEEDOR
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">N.º</th>
                                        <th>Nombre</th>
                                        <th>Contacto</th>
                                        <th>Teléfono</th>
                                        <th>Email</th>
                                        <th class="text-end pe-4">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="proveedoresTableBody">
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <div class="spinner-border spinner-border-sm text-success me-2"></div> Cargando proveedores...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination container -->
                        <div id="clientPagination" class="my-3 d-flex justify-content-center"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo Proveedor -->
    <div class="modal fade" id="addProveedorModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <form id="addProveedorForm">
                    <div class="modal-header bg-success text-white border-0">
                        <h5 class="modal-title fw-bold"><i class="fas fa-truck me-2"></i>Registrar Proveedor</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Nombre *</label>
                            <input type="text" name="nombre" class="form-control" required maxlength="100" placeholder="Nombre del proveedor">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Contacto</label>
                            <input type="text" name="contacto" class="form-control" maxlength="100" placeholder="Nombre de contacto">
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted text-uppercase">Teléfono</label>
                                <input type="tel" name="telefono" class="form-control" maxlength="30" placeholder="0000-0000">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted text-uppercase">Email</label>
                                <input type="email" name="email" class="form-control" maxlength="100" placeholder="proveedor@ejemplo.com">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-3 bg-light rounded-bottom">
                        <button type="button" class="btn btn-link link-secondary text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success px-4 fw-bold shadow-sm">GUARDAR PROVEEDOR</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Proveedor -->
    <div class="modal fade" id="editProveedorModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <form id="editProveedorForm">
                    <div class="modal-header bg-warning border-0 text-dark">
                        <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Editar Proveedor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <input type="hidden" name="id" id="editProveedorId">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Nombre *</label>
                            <input type="text" name="nombre" id="editProveedorNombre" class="form-control" required maxlength="100">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Contacto</label>
                            <input type="text" name="contacto" id="editProveedorContacto" class="form-control" maxlength="100">
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted text-uppercase">Teléfono</label>
                                <input type="tel" name="telefono" id="editProveedorTelefono" class="form-control" maxlength="30">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted text-uppercase">Email</label>
                                <input type="email" name="email" id="editProveedorEmail" class="form-control" maxlength="100">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-3 bg-light rounded-bottom">
                        <button type="button" class="btn btn-link link-secondary text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning text-dark px-4 fw-bold shadow-sm">ACTUALIZAR</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script>
        let proveedoresData = [];

        document.addEventListener('DOMContentLoaded', () => {
            loadProveedores();
        });

        async function loadProveedores(page = 1) {
            try {
                const limit = 10;
                const res = await fetch(`../backend/api.php?route=get_proveedores_paginated&page=${page}&limit=${limit}`);
                const data = await res.json();
                const tbody = document.getElementById('proveedoresTableBody');
                tbody.innerHTML = '';

                if (data.success && data.data && data.data.length > 0) {
                    proveedoresData = data.data;
                    const puedeGestionar = (typeof tienePermiso === 'function' ? tienePermiso('proveedores.gestionar') : true);
                    const offset = (page - 1) * limit;

                    data.data.forEach((p, index) => {
                        const rowNumber = offset + index + 1;
                        tbody.innerHTML += `
                            <tr>
                                <td class="ps-4 text-muted fw-bold">${rowNumber}</td>
                                <td class="fw-bold text-dark">${escapeHtml(p.nombre)}</td>
                                <td class="small">${escapeHtml(p.contacto || '<span class="text-muted">N/A</span>')}</td>
                                <td class="small">${escapeHtml(p.telefono || '<span class="text-muted">N/A</span>')}</td>
                                <td class="small text-muted">${escapeHtml(p.email || 'Sin email')}</td>
                                <td class="text-end pe-4">
                                    ${puedeGestionar ? `
                                        <button class="btn btn-sm btn-outline-warning me-1" onclick="openEditModal(${p.id})" title="Editar">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                        <button class="btn btn-sm btn-link text-danger p-0" onclick="deleteProveedor(${p.id})" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    ` : ''}
                                </td>
                            </tr>
                        `;
                    });

                    // Render pagination using shared function
                    renderPagination(data.total, data.limit, page);
                } else {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-muted">No hay proveedores registrados.</td></tr>';
                    document.getElementById('clientPagination').innerHTML = '';
                }
            } catch (e) { console.error(e); }
        }

        // Render pagination (shared with clientes)
        function renderPagination(total, limit, page) {
            const totalPages = Math.ceil(total / limit);
            const nav = document.getElementById('clientPagination');
            nav.innerHTML = '';
            if (totalPages <= 1) return;
            let html = '<ul class="pagination justify-content-center">';
            const prevDisabled = page <= 1 ? ' disabled' : '';
            html += `<li class="page-item${prevDisabled}"><a class="page-link" href="#" onclick="loadProveedores(${page - 1}); return false;">&laquo;</a></li>`;
            const maxVisible = 5;
            let start = Math.max(1, page - Math.floor(maxVisible / 2));
            let end = Math.min(totalPages, start + maxVisible - 1);
            if (end - start < maxVisible - 1) {
                start = Math.max(1, end - maxVisible + 1);
            }
            for (let i = start; i <= end; i++) {
                const active = i === page ? ' active' : '';
                html += `<li class="page-item${active}"><a class="page-link" href="#" onclick="loadProveedores(${i}); return false;">${i}</a></li>`;
            }
            const nextDisabled = page >= totalPages ? ' disabled' : '';
            html += `<li class="page-item${nextDisabled}"><a class="page-link" href="#" onclick="loadProveedores(${page + 1}); return false;">&raquo;</a></li>`;
            html += '</ul>';
            nav.innerHTML = html;
        }

        document.getElementById('addProveedorForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const payload = {
                nombre: e.target.nombre.value.trim(),
                contacto: e.target.contacto.value.trim(),
                telefono: e.target.telefono.value.trim(),
                email: e.target.email.value.trim()
            };
            if (!payload.nombre) { showAlert('El nombre es obligatorio', 'warning'); return; }
            try {
                const res = await fetch('../backend/api.php?route=add_proveedor', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('addProveedorModal')).hide();
                    e.target.reset();
                    loadProveedores();
                } else {
                    showAlert(data.message || 'Error al guardar.', 'error');
                }
            } catch (err) { showAlert('Error de red', 'error'); }
        });

        function openEditModal(id) {
            const p = proveedoresData.find(x => x.id == id);
            if (!p) return;
            document.getElementById('editProveedorId').value = p.id;
            document.getElementById('editProveedorNombre').value = p.nombre || '';
            document.getElementById('editProveedorContacto').value = p.contacto || '';
            document.getElementById('editProveedorTelefono').value = p.telefono || '';
            document.getElementById('editProveedorEmail').value = p.email || '';
            new bootstrap.Modal(document.getElementById('editProveedorModal')).show();
        }

        document.getElementById('editProveedorForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const payload = {
                id: parseInt(e.target.id.value),
                nombre: e.target.nombre.value.trim(),
                contacto: e.target.contacto.value.trim(),
                telefono: e.target.telefono.value.trim(),
                email: e.target.email.value.trim()
            };
            if (!payload.nombre) { showAlert('El nombre es obligatorio', 'warning'); return; }
            try {
                const res = await fetch('../backend/api.php?route=update_proveedor', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('editProveedorModal')).hide();
                    loadProveedores();
                } else {
                    showAlert(data.message || 'Error al actualizar.', 'error');
                }
            } catch (err) { showAlert('Error de red', 'error'); }
        });

        async function deleteProveedor(id) {
            if (!(await showConfirm('¿Está seguro de eliminar este proveedor?'))) return;
            try {
                const res = await fetch('../backend/api.php?route=delete_proveedor', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });
                const data = await res.json();
                if (data.success) loadProveedores();
                else showAlert(data.message || 'Error al eliminar.', 'error');
            } catch (e) { console.error(e); }
        }
    </script>
</body>
</html>
