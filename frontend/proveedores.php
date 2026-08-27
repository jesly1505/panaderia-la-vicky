<?php
// frontend/proveedores.php
require_once __DIR__ . '/includes/auth_guard.php';

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
                    <div class="card-header bg-white py-3">
                        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                            <div>
                                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-truck me-2 text-success"></i>Directorio de Proveedores</h5>
                                <p class="text-muted small mb-0">Proveedores de materias primas e insumos</p>
                            </div>
                            <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2">
                                <!-- Buscador de Proveedores -->
                                <div class="input-group input-group-sm" style="min-width: 260px; max-width: 340px;">
                                    <span class="input-group-text bg-light border-end-0 text-muted">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" id="proveedorSearchInput" class="form-control border-start-0 border-end-0 ps-0" placeholder="Buscar proveedor..." oninput="handleProveedorSearch()" autocomplete="off">
                                    <button class="btn btn-outline-secondary border-start-0" type="button" id="btnClearSearch" onclick="clearProveedorSearch()" title="Limpiar búsqueda" style="display: none;">
                                        <i class="fas fa-times me-1"></i> Limpiar
                                    </button>
                                </div>

                                <!-- Botón Nuevo Proveedor -->
                                <?php if (tiene_permiso('proveedores.gestionar')): ?>
                                    <button class="btn btn-success shadow-sm fw-bold px-3 text-nowrap" data-bs-toggle="modal" data-bs-target="#addProveedorModal">
                                        <i class="fas fa-plus me-1"></i>Nuevo Proveedor
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
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
                        <!-- Paginación de Proveedores -->
                        <div id="proveedoresPagination" class="p-3 border-top d-flex justify-content-end"></div>
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
        let currentPage = 1;
        let currentSearch = '';
        let searchTimeout = null;
        const itemsPerPage = 10;

        document.addEventListener('DOMContentLoaded', () => {
            loadProveedores(1);
        });

        function handleProveedorSearch() {
            const input = document.getElementById('proveedorSearchInput');
            const query = (input.value || '').trim();
            const btnClear = document.getElementById('btnClearSearch');
            if (btnClear) {
                btnClear.style.display = input.value.length > 0 ? 'inline-block' : 'none';
            }

            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                currentSearch = query;
                loadProveedores(1);
            }, 250);
        }

        function clearProveedorSearch() {
            const input = document.getElementById('proveedorSearchInput');
            input.value = '';
            const btnClear = document.getElementById('btnClearSearch');
            if (btnClear) btnClear.style.display = 'none';
            currentSearch = '';
            loadProveedores(1);
            input.focus();
        }

        async function loadProveedores(page = 1) {
            currentPage = page;
            try {
                const searchParam = encodeURIComponent(currentSearch);
                const res = await fetch(`../backend/api.php?route=get_proveedores_paginated&page=${page}&limit=${itemsPerPage}&search=${searchParam}`);
                const data = await res.json();
                const tbody = document.getElementById('proveedoresTableBody');
                tbody.innerHTML = '';

                if (data.success && data.data && data.data.length > 0) {
                    proveedoresData = data.data;
                    const puedeGestionar = (typeof tienePermiso === 'function' ? tienePermiso('proveedores.gestionar') : true);
                    const offset = (page - 1) * itemsPerPage;

                    data.data.forEach((p, index) => {
                        const rowNumber = offset + index + 1;
                        tbody.innerHTML += `
                            <tr>
                                <td class="ps-4 text-muted fw-bold">${rowNumber}</td>
                                <td class="fw-bold text-dark">${escapeHtml(p.nombre)}</td>
                                <td class="small">${escapeHtml(p.contacto || 'N/A')}</td>
                                <td class="small">${escapeHtml(p.telefono || 'N/A')}</td>
                                <td class="small text-muted">${escapeHtml(p.email || 'Sin email')}</td>
                                <td class="text-end pe-4">
                                    ${puedeGestionar ? TA.edit(`openEditModal(${p.id})`) + TA.remove(`deleteProveedor(${p.id})`) : ''}
                                </td>
                            </tr>
                        `;
                    });

                    renderPagination(data.total, itemsPerPage, page, 'proveedoresPagination', 'loadProveedores');
                } else {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-muted">No se encontraron proveedores.</td></tr>';
                    const nav = document.getElementById('proveedoresPagination');
                    if (nav) nav.innerHTML = '';
                }
            } catch (e) {
                console.error(e);
            }
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
                    loadProveedores(1);
                    showAlert('Proveedor registrado correctamente', 'success');
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
                    loadProveedores(currentPage);
                    showAlert('Proveedor actualizado correctamente', 'success');
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
                if (data.success) { 
                    loadProveedores(currentPage); 
                    showAlert('Proveedor eliminado correctamente', 'success'); 
                }
                else showAlert(data.message || 'Error al eliminar.', 'error');
            } catch (e) { console.error(e); }
        }
    </script>
</body>
</html>
