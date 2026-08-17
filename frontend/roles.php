<?php
// frontend/roles.php
session_start();
require_once __DIR__ . '/includes/permisos.php';

if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

if (!tiene_permiso('permisos.gestionar')) {
    header("Location: index.php");
    exit();
}

$pageTitle = "Roles y Permisos";
$pageHeader = "Gestión de Roles";
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
                <div id="resultAlert" class="alert d-none mb-4 shadow-sm"></div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-user-shield me-2 text-primary"></i>Roles del Sistema
                        </h5>
                        <button class="btn btn-primary btn-sm shadow-sm fw-bold px-3" data-bs-toggle="modal" data-bs-target="#crearRolModal">
                            <i class="fas fa-plus me-1"></i> Nuevo Rol
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Nombre del Rol</th>
                                        <th>Descripción</th>
                                        <th class="text-center">Permisos</th>
                                        <th class="text-end pe-4">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="rolesTableBody">
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <div class="spinner-border spinner-border-sm me-2 text-primary"></div>Cargando roles...
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

    <!-- Modal Nuevo Rol -->
    <div class="modal fade" id="crearRolModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <form id="crearRolForm">
                    <div class="modal-header bg-primary text-white border-0">
                        <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2"></i>Crear Nuevo Rol</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Nombre del Rol <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control py-2" maxlength="50" required placeholder="Ej. Encargado de Bodega">
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="2" maxlength="255" placeholder="Describe el alcance del rol"></textarea>
                        </div>
                        <div class="form-text mt-2">Después de crearlo podrás asignarle permisos del catálogo.</div>
                    </div>
                    <div class="modal-footer border-0 p-3 bg-light">
                        <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">Crear Rol</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Rol -->
    <div class="modal fade" id="editarRolModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <form id="editarRolForm">
                    <div class="modal-header bg-secondary text-white border-0">
                        <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Editar Rol</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <input type="hidden" name="id">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Nombre del Rol <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control py-2" maxlength="50" required placeholder="Ej. Encargado de Bodega">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="2" maxlength="255" placeholder="Describe el alcance del rol"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-3 bg-light">
                        <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-secondary px-4 fw-bold shadow-sm">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Asignar Permisos -->
    <div class="modal fade" id="permisosRolModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-success text-white border-0">
                    <h5 class="modal-title fw-bold" id="permisosModalTitle">
                        <i class="fas fa-key me-2"></i>Permisos del Rol
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="permisosContainer">
                    <div class="text-center text-muted py-4">Cargando permisos...</div>
                </div>
                <div class="modal-footer border-0 p-3 bg-light">
                    <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success px-4 fw-bold shadow-sm" onclick="savePermisosRol()">
                        <i class="fas fa-save me-1"></i> Guardar Permisos
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script>
        let rolPermisosActual = null;

        function escapeHtml(s) {
            return String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        }

        function mostrarAlerta(tipo, html) {
            const el = document.getElementById('resultAlert');
            el.className = `alert alert-${tipo} alert-dismissible fade show shadow-sm`;
            el.innerHTML = html + '<button type="button" class="btn-close" onclick="this.parentElement.classList.add(\'d-none\')"></button>';
            el.classList.remove('d-none');
            if (tipo === 'success') {
                setTimeout(() => el.classList.add('d-none'), 5000);
            }
        }

        // 1. Load roles table
        async function loadRoles() {
            const tbody = document.getElementById('rolesTableBody');
            try {
                const res = await fetch('../backend/api.php?route=get_roles');
                const json = await res.json();
                if (!json.success) {
                    tbody.innerHTML = `<tr><td colspan="4" class="text-center text-danger py-4">${escapeHtml(json.message)}</td></tr>`;
                    return;
                }
                if (!json.data || json.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">No hay roles registrados.</td></tr>';
                    return;
                }
                tbody.innerHTML = json.data.map(r => `
                    <tr>
                        <td class="ps-4 fw-bold text-dark">${escapeHtml(r.nombre)}</td>
                        <td class="text-muted small">${escapeHtml(r.descripcion || '')}</td>
                        <td class="text-center">
                            <span class="badge bg-primary rounded-pill">${r.permisos_count || 0} permisos</span>
                        </td>
                        <td class="text-end pe-4">
                            <button class="btn btn-sm btn-outline-success me-1" onclick="openPermisosModal(${r.id}, '${escapeHtml(r.nombre)}')" title="Asignar permisos">
                                <i class="fas fa-key me-1"></i>Permisos
                            </button>
                            <button class="btn btn-sm btn-outline-secondary me-1" onclick="openEditarModal(${r.id}, '${escapeHtml(r.nombre)}', '${escapeHtml(r.descripcion || '')}')" title="Editar rol">
                                <i class="fas fa-edit"></i>
                            </button>
                            ${r.id != 1 ? `
                                <button class="btn btn-sm btn-outline-danger" onclick="eliminarRol(${r.id}, '${escapeHtml(r.nombre)}')" title="Eliminar rol">
                                    <i class="fas fa-trash"></i>
                                </button>
                            ` : '<span class="badge bg-light text-muted border ms-1 small">Protegido</span>'}
                        </td>
                    </tr>
                `).join('');
            } catch (e) {
                tbody.innerHTML = `<tr><td colspan="4" class="text-center text-danger py-4">Error de conexión.</td></tr>`;
            }
        }

        // 2. Permissions assignment
        async function openPermisosModal(id, nombre) {
            rolPermisosActual = id;
            document.getElementById('permisosModalTitle').innerHTML = `<i class="fas fa-key me-2"></i>Permisos de: ${escapeHtml(nombre)}`;
            const container = document.getElementById('permisosContainer');
            container.innerHTML = '<div class="text-center text-muted py-4"><div class="spinner-border spinner-border-sm me-2"></div>Cargando permisos...</div>';
            try {
                const [permRes, rolRes] = await Promise.all([
                    fetch('../backend/api.php?route=get_permisos'),
                    fetch(`../backend/api.php?route=get_permisos_rol&rol_id=${id}`)
                ]);
                const permData = await permRes.json();
                const rolData = await rolRes.json();
                renderPermisos(permData.success ? permData.data : [], rolData.success ? rolData.data : []);
                bootstrap.Modal.getOrCreateInstance(document.getElementById('permisosRolModal')).show();
            } catch (e) {
                container.innerHTML = '<div class="text-center text-danger py-4">Error de conexión.</div>';
            }
        }

        function renderPermisos(catalogo, asignados) {
            const container = document.getElementById('permisosContainer');
            const porModulo = {};
            catalogo.forEach(p => { (porModulo[p.modulo] = porModulo[p.modulo] || []).push(p); });
            let html = '';
            for (const [modulo, items] of Object.entries(porModulo)) {
                html += `<div class="mb-4">
                    <h6 class="text-uppercase fw-bold text-muted border-bottom pb-1 mb-3" style="font-size:.75rem;letter-spacing:.06em;">${escapeHtml(modulo)}</h6>
                    <div class="row g-2">`;
                items.forEach(p => {
                    const checked = asignados.includes(p.codigo) ? 'checked' : '';
                    html += `<div class="col-md-4 col-sm-6">
                        <div class="form-check form-check-sm">
                            <input class="form-check-input permiso-cb" type="checkbox" value="${escapeHtml(p.codigo)}" id="perm_${p.id}" ${checked}>
                            <label class="form-check-label small" for="perm_${p.id}" title="${escapeHtml(p.descripcion || '')}">${escapeHtml(p.nombre)}</label>
                        </div>
                    </div>`;
                });
                html += `</div></div>`;
            }
            container.innerHTML = html || '<div class="text-center text-muted py-4">No hay permisos en el catálogo.</div>';
        }

        async function savePermisosRol() {
            if (!rolPermisosActual) return;
            const permisos = Array.from(document.querySelectorAll('.permiso-cb:checked')).map(cb => cb.value);
            try {
                const res = await fetch('../backend/api.php?route=set_permisos_rol', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ rol_id: rolPermisosActual, permisos: permisos })
                });
                const json = await res.json();
                mostrarAlerta(json.success ? 'success' : 'danger', json.message || 'Error al guardar permisos.');
                if (json.success) {
                    bootstrap.Modal.getInstance(document.getElementById('permisosRolModal')).hide();
                    loadRoles();
                }
            } catch (e) {
                mostrarAlerta('danger', 'Error de conexión con el servidor.');
            }
        }

        // 3. CRUD operations
        document.getElementById('crearRolForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            try {
                const res = await fetch('../backend/api.php?route=crear_rol', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ nombre: form.nombre.value, descripcion: form.descripcion.value })
                });
                const json = await res.json();
                mostrarAlerta(json.success ? 'success' : 'danger', json.message || 'Error al crear el rol.');
                if (json.success) {
                    bootstrap.Modal.getInstance(document.getElementById('crearRolModal')).hide();
                    form.reset();
                    loadRoles();
                }
            } catch (err) { mostrarAlerta('danger', 'Error de conexión con el servidor.'); }
        });

        function openEditarModal(id, nombre, descripcion) {
            const form = document.getElementById('editarRolForm');
            form.id.value = id;
            form.nombre.value = nombre;
            form.descripcion.value = descripcion;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('editarRolModal')).show();
        }

        document.getElementById('editarRolForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            try {
                const res = await fetch('../backend/api.php?route=editar_rol', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: form.id.value, nombre: form.nombre.value, descripcion: form.descripcion.value })
                });
                const json = await res.json();
                mostrarAlerta(json.success ? 'success' : 'danger', json.message || 'Error al actualizar el rol.');
                if (json.success) {
                    bootstrap.Modal.getInstance(document.getElementById('editarRolModal')).hide();
                    loadRoles();
                }
            } catch (err) { mostrarAlerta('danger', 'Error de conexión con el servidor.'); }
        });

        async function eliminarRol(id, nombre) {
            if (!confirm(`¿Eliminar el rol "${nombre}"?\nEsta acción es irreversible.`)) return;
            try {
                const res = await fetch('../backend/api.php?route=eliminar_rol', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });
                const json = await res.json();
                mostrarAlerta(json.success ? 'success' : 'danger', json.message || 'Error al eliminar el rol.');
                if (json.success) loadRoles();
            } catch (err) { mostrarAlerta('danger', 'Error de conexión con el servidor.'); }
        }

        document.addEventListener('DOMContentLoaded', loadRoles);
    </script>
</body>
</html>
