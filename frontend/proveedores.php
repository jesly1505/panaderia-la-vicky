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
                                        <th class="ps-4">ID</th>
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

        async function loadProveedores() {
            try {
                const res = await fetch('../backend/api.php?route=get_proveedores');
                const data = await res.json();
                const tbody = document.getElementById('proveedoresTableBody');
                tbody.innerHTML = '';

                if (data.success && data.data && data.data.length > 0) {
                    proveedoresData = data.data;
                    const puedeGestionar = (typeof tienePermiso === 'function' ? tienePermiso('proveedores.gestionar') : true);

                    data.data.forEach(p => {
                        tbody.innerHTML += `
                            <tr>
                                <td class="ps-4 text-muted fw-bold">#${p.id}</td>
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
                } else {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-muted">No hay proveedores registrados.</td></tr>';
                }
            } catch (e) { console.error(e); }
        }

        document.getElementById('addProveedorForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const payload = {
                nombre: e.target.nombre.value.trim(),
                contacto: e.target.contacto.value.trim(),
                telefono: e.target.telefono.value.trim(),
                email: e.target.email.value.trim()
            };
            if (!payload.nombre) { alert('El nombre es obligatorio'); return; }
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
                    alert(data.message || 'Error al guardar.');
                }
            } catch (err) { alert('Error de red'); }
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
            if (!payload.nombre) { alert('El nombre es obligatorio'); return; }
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
                    alert(data.message || 'Error al actualizar.');
                }
            } catch (err) { alert('Error de red'); }
        });

        async function deleteProveedor(id) {
            if (!confirm('¿Está seguro de eliminar este proveedor?')) return;
            try {
                const res = await fetch('../backend/api.php?route=delete_proveedor', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });
                const data = await res.json();
                if (data.success) loadProveedores();
                else alert(data.message || 'Error al eliminar.');
            } catch (e) { console.error(e); }
        }
    </script>
</body>
</html>
