<?php
session_start();
require_once __DIR__ . '/includes/permisos.php';
if (!isset($_SESSION['usuario'])) {
    header("Location: login.html");
    exit();
}
if (!tiene_permiso('permisos.gestionar')) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roles y Permisos - La Vicky</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background-color: #f4f6f9; }
        .sidebar { height: 100vh; background-color: #685569; padding-top: 20px; position: fixed; width: 16.666667%; overflow-y: auto; }
        .sidebar a { padding: 15px 20px; text-decoration: none; font-size: 16px; color: #d1d8e0; display: block; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { color: #fff; background-color: #0d6efd; }
        .main-content { padding: 30px; margin-left: 16.666667%; }
        .top-navbar { background-color: #fff; box-shadow: 0 2px 4px rgba(0,0,0,.1); padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; margin-left: 16.666667%; }
        .table-card { background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,.05); padding: 20px; }
    </style>
</head>

<body>
    <div class="container-fluid p-0">
        <?php $active = 'roles'; $titulo = 'Roles y Permisos'; include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">

            <!-- RESULT ALERT -->
            <div id="resultAlert" class="alert d-none"></div>

            <div class="table-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="m-0"><i class="fas fa-user-shield text-primary me-2"></i>Roles del Sistema</h5>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#crearRolModal">
                        <i class="fas fa-plus"></i> Nuevo Rol
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th class="text-center">Permisos</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="rolesTableBody">
                            <tr><td colspan="4" class="text-center text-muted">Cargando roles...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo Rol -->
    <div class="modal fade" id="crearRolModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="crearRolForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Crear Nuevo Rol</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nombre del Rol <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control" maxlength="50" required placeholder="Ej. Encargado de Bodega">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="2" maxlength="255" placeholder="Describe el alcance del rol"></textarea>
                        </div>
                        <div class="form-text">Después de crearlo podrás asignarle permisos del catálogo.</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Crear Rol</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Rol -->
    <div class="modal fade" id="editarRolModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editarRolForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Rol</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nombre del Rol <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control" maxlength="50" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="2" maxlength="255"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Asignar Permisos -->
    <div class="modal fade" id="permisosRolModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="permisosModalTitle">Permisos del Rol</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="permisosContainer">
                    <div class="text-center text-muted py-4">Cargando permisos...</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" onclick="savePermisosRol()">
                        <i class="fas fa-save"></i> Guardar permisos
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let rolPermisosActual = null;

        // ── 1. Carga de roles ──────────────────────────────────────────────
        async function loadRoles() {
            const tbody = document.getElementById('rolesTableBody');
            try {
                const res = await fetch('../backend/api.php?route=get_roles');
                const json = await res.json();
                if (!json.success) {
                    tbody.innerHTML = `<tr><td colspan="4" class="text-center text-danger">${escapeHtml(json.message)}</td></tr>`;
                    return;
                }
                if (json.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No hay roles registrados.</td></tr>';
                    return;
                }
                tbody.innerHTML = json.data.map(r => `
                    <tr>
                        <td class="fw-bold">${escapeHtml(r.nombre)}</td>
                        <td class="text-muted small">${escapeHtml(r.descripcion || '')}</td>
                        <td class="text-center">
                            <span class="badge bg-primary">${r.permisos_count} permisos</span>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary" onclick="openPermisosModal(${r.id}, '${escapeHtml(r.nombre)}')" title="Asignar permisos">
                                <i class="fas fa-key"></i> Permisos
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" onclick="openEditarModal(${r.id}, '${escapeHtml(r.nombre)}', '${escapeHtml(r.descripcion || '')}')" title="Editar rol">
                                <i class="fas fa-edit"></i>
                            </button>
                            ${r.id !== 1 ? `
                            <button class="btn btn-sm btn-outline-danger" onclick="eliminarRol(${r.id}, '${escapeHtml(r.nombre)}')" title="Eliminar rol">
                                <i class="fas fa-trash"></i>
                            </button>` : ''}
                        </td>
                    </tr>`).join('');
            } catch (e) {
                tbody.innerHTML = `<tr><td colspan="4" class="text-center text-danger">Error de conexión.</td></tr>`;
            }
        }

        // ── 2. Asignación de permisos ──────────────────────────────────────
        async function openPermisosModal(id, nombre) {
            rolPermisosActual = id;
            document.getElementById('permisosModalTitle').textContent = `Permisos de ${nombre}`;
            const container = document.getElementById('permisosContainer');
            container.innerHTML = '<div class="text-center text-muted py-4">Cargando permisos...</div>';
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
                html += `<div class="mb-3">
                    <h6 class="text-uppercase text-muted border-bottom pb-1">${escapeHtml(modulo)}</h6>
                    <div class="row">`;
                items.forEach(p => {
                    const checked = asignados.includes(p.codigo) ? 'checked' : '';
                    html += `<div class="col-md-4 col-sm-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input permiso-cb" type="checkbox" value="${escapeHtml(p.codigo)}" id="perm_${p.id}" ${checked}>
                            <label class="form-check-label" for="perm_${p.id}" title="${escapeHtml(p.descripcion || '')}">${escapeHtml(p.nombre)}</label>
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

        // ── 3. Crear / Editar / Eliminar rol ───────────────────────────────
        document.getElementById('crearRolForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            try {
                const res = await fetch('../backend/api.php?route=crear_rol', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        nombre: form.nombre.value,
                        descripcion: form.descripcion.value
                    })
                });
                const json = await res.json();
                mostrarAlerta(json.success ? 'success' : 'danger', json.message || 'Error al crear el rol.');
                if (json.success) {
                    bootstrap.Modal.getInstance(document.getElementById('crearRolModal')).hide();
                    form.reset();
                    loadRoles();
                }
            } catch (err) {
                mostrarAlerta('danger', 'Error de conexión con el servidor.');
            }
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
                    body: JSON.stringify({
                        id: form.id.value,
                        nombre: form.nombre.value,
                        descripcion: form.descripcion.value
                    })
                });
                const json = await res.json();
                mostrarAlerta(json.success ? 'success' : 'danger', json.message || 'Error al actualizar el rol.');
                if (json.success) {
                    bootstrap.Modal.getInstance(document.getElementById('editarRolModal')).hide();
                    loadRoles();
                }
            } catch (err) {
                mostrarAlerta('danger', 'Error de conexión con el servidor.');
            }
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
            } catch (err) {
                mostrarAlerta('danger', 'Error de conexión con el servidor.');
            }
        }

        // ── 4. Utilidades ──────────────────────────────────────────────────
        function escapeHtml(s) {
            return String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        }

        function mostrarAlerta(tipo, html) {
            const el = document.getElementById('resultAlert');
            el.className = `alert alert-${tipo} alert-dismissible fade show`;
            el.innerHTML = html + '<button type="button" class="btn-close" onclick="this.parentElement.classList.add(\'d-none\')"></button>';
            el.classList.remove('d-none');
            if (tipo === 'success') {
                setTimeout(() => el.classList.add('d-none'), 5000);
            }
        }

        document.addEventListener('DOMContentLoaded', loadRoles);
    </script>
</body>

</html>
