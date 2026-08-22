<?php
// frontend/configuracion.php
session_start();
require_once __DIR__ . '/includes/permisos.php';

if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

if (!tiene_permiso('empleados.ver')) {
    header("Location: index.php");
    exit();
}

$pageTitle = "Configuración";
$pageHeader = "Ajustes del Sistema";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include 'includes/head.php'; ?>
    <style>
        .config-card { border: none; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); height: 100%; transition: var(--transition); }
        .config-card:hover { box-shadow: var(--shadow-md); }
        .employee-stat-card { background: var(--light); border-radius: var(--radius-sm); border: 1px solid #eee; padding: 1.25rem; transition: var(--transition); }
        .employee-stat-card:hover { border-color: var(--primary-light); transform: translateY(-3px); }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <div class="main-content">
            <?php include 'includes/navbar.php'; ?>

            <div class="container-fluid p-4 animate-fade-in">
                <div class="row g-4">
                    <!-- Employee Management -->
                    <div class="col-12">
                        <div class="card config-card border-top border-4 border-info">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-users-cog me-2 text-info"></i>Gestión de Personal</h5>
                                    <p class="text-muted x-small mb-0">Control de usuarios y accesos</p>
                                </div>
                                <button class="btn btn-info btn-sm text-white fw-bold px-3 shadow-xs" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                                    <i class="fas fa-plus me-1"></i>AÑADIR
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="ps-4">Nombre / Usuario</th>
                                                <th>Email</th>
                                                <th>Rol</th>
                                                <th class="text-end pe-4">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="employeeTableBody">
                                            <tr><td colspan="4" class="text-center py-4 text-muted small">Cargando personal...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Stats -->
                    <div class="col-12">
                        <div class="card config-card border-0">
                            <div class="card-header bg-dark text-white py-3">
                                <h6 class="mb-0 fw-bold"><i class="fas fa-chart-line me-2 text-warning"></i>Rendimiento Individual de Ventas</h6>
                            </div>
                            <div class="card-body p-4 bg-light bg-opacity-50">
                                <div class="row g-4" id="employeeStatsRows">
                                    <div class="col-12 text-center text-muted py-5 italic">Calculando métricas de productividad...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo Empleado -->
    <div class="modal fade" id="addEmployeeModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <form id="addEmployeeForm">
                    <div class="modal-header bg-info text-white border-0">
                        <h5 class="modal-title fw-bold"><i class="fas fa-user-plus me-2"></i>Registrar Empleado</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Nombre Completo</label>
                            <input type="text" name="nombre" class="form-control" required maxlength="100" placeholder="Ej. Ricardo Mendoza">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Email (Usuario)</label>
                            <input type="email" name="email" class="form-control" required placeholder="usuario@lavicky.com">
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted text-uppercase">Contraseña</label>
                                <input type="password" name="password" class="form-control" required minlength="6" placeholder="Mínimo 6 caracteres">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted text-uppercase">Rol</label>
                                <select name="rol_id" class="form-select" id="selectRolEmpleado">
                                    <option value="2">Cajero / Vendedor</option>
                                    <option value="1">Administrador</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-3 bg-light rounded-bottom">
                        <button type="button" class="btn btn-link link-secondary text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-info text-white px-4 fw-bold shadow-sm">CREAR USUARIO</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Empleado -->
    <div class="modal fade" id="editEmployeeModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <form id="editEmployeeForm">
                    <input type="hidden" name="id">
                    <div class="modal-header bg-primary text-white border-0">
                        <h5 class="modal-title fw-bold"><i class="fas fa-user-edit me-2"></i>Editar Empleado</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Nombre Completo</label>
                            <input type="text" name="nombre" class="form-control" required maxlength="100" placeholder="Ej. Ricardo Mendoza">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Email (Usuario)</label>
                            <input type="email" name="email" class="form-control" required placeholder="usuario@lavicky.com">
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-bold small text-muted text-uppercase">Rol</label>
                            <select name="rol_id" class="form-select">
                                <option value="2">Cajero / Vendedor</option>
                                <option value="1">Administrador</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-3 bg-light rounded-bottom">
                        <button type="button" class="btn btn-link link-secondary text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">GUARDAR CAMBIOS</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            loadEmployees();
            loadEmployeeStats();
        });

        function escJs(value) {
            return String(value ?? '')
                .replace(/\\/g, '\\\\')
                .replace(/'/g, "\\'")
                .replace(/"/g, '&quot;')
                .replace(/\r?\n/g, ' ');
        }

        async function loadEmployees() {
            try {
                const res = await fetch('../backend/api.php?route=get_employees');
                const data = await res.json();
                const tbody = document.getElementById('employeeTableBody');
                tbody.innerHTML = '';
                if (data.success) {
                    data.data.forEach(emp => {
                        const isMainAdmin = (emp.id == 1);
                        tbody.innerHTML += `
                            <tr class="animate-fade-in">
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">${emp.nombre}</div>
                                </td>
                                <td><small class="text-muted">${emp.email}</small></td>
                                <td>
                                    <span class="badge ${emp.rol_nombre === 'Administrador' ? 'bg-primary bg-opacity-10 text-primary border-primary' : 'bg-info bg-opacity-10 text-info border-info'} border small px-2 py-1">
                                        ${emp.rol_nombre.toUpperCase()}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    ${!isMainAdmin ? `
                                        <button class="btn btn-sm btn-outline-primary border-0 me-1" onclick="openEditEmployeeModal(${emp.id}, '${escJs(emp.nombre)}', '${escJs(emp.email)}', ${emp.rol_id})" title="Editar">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger border-0" onclick="deleteEmployee(${emp.id})" title="Dar de baja">
                                            <i class="fas fa-user-slash"></i>
                                        </button>
                                    ` : '<span class="text-muted x-small italic">System Protected</span>'}
                                </td>
                            </tr>
                        `;
                    });
                }
            } catch (e) { 
                console.error(e);
            }
        }

        async function loadEmployeeStats() {
            try {
                const res = await fetch('../backend/api.php?route=get_employee_stats');
                const data = await res.json();
                const container = document.getElementById('employeeStatsRows');
                container.innerHTML = '';
                if (data.success && data.data.length > 0) {
                    data.data.forEach(stat => {
                        container.innerHTML += `
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3 animate-fade-in">
                                <div class="employee-stat-card shadow-xs">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-primary text-white rounded-circle p-2 me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-user small"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-0 text-truncate">${stat.nombre}</h6>
                                    </div>
                                    <div class="small text-muted text-uppercase fw-bold fs-xs opacity-75 mb-1">Ventas Generadas</div>
                                    <h4 class="fw-bold text-primary mb-0">${formatCurrency(stat.total_ganado)}</h4>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    container.innerHTML = '<div class="col-12 text-center text-muted py-4 italic">No se registran ventas para el personal consultado.</div>';
                }
            } catch (e) {
                console.error(e);
            }
        }

        document.getElementById('addEmployeeForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formObj = {
                nombre: e.target.nombre.value,
                email: e.target.email.value,
                password: e.target.password.value,
                rol_id: e.target.rol_id.value
            };
            try {
                const res = await fetch('../backend/api.php?route=add_employee', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formObj)
                });
                const data = await res.json();
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('addEmployeeModal')).hide();
                    e.target.reset();
                    loadEmployees();
                    loadEmployeeStats();
                } else alert(data.message);
            } catch (e) { 
                alert('Error de red');
                console.error(e);
            }
        });

        async function deleteEmployee(id) {
            if (!confirm('¿Está seguro de dar de baja a este empleado? Perderá acceso inmediato al sistema.')) return;
            try {
                const res = await fetch('../backend/api.php?route=delete_employee', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });
                const data = await res.json();
                if (data.success) {
                    loadEmployees();
                    loadEmployeeStats();
                } else alert(data.message);
            } catch (e) {
                console.error(e);
            }
        }

        function openEditEmployeeModal(id, nombre, email, rol_id) {
            const form = document.getElementById('editEmployeeForm');
            form.elements['id'].value = id;
            form.elements['nombre'].value = nombre;
            form.elements['email'].value = email;
            form.elements['rol_id'].value = rol_id;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('editEmployeeModal')).show();
        }

        document.getElementById('editEmployeeForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formObj = {
                id: e.target.elements['id'].value,
                nombre: e.target.nombre.value,
                email: e.target.email.value,
                rol_id: e.target.rol_id.value
            };
            try {
                const res = await fetch('../backend/api.php?route=update_employee', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formObj)
                });
                const data = await res.json();
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('editEmployeeModal')).hide();
                    loadEmployees();
                    loadEmployeeStats();
                } else alert(data.message);
            } catch (e) {
                alert('Error de red');
                console.error(e);
            }
        });
    </script>
</body>
</html>
