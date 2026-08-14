<?php
session_start();
require_once __DIR__ . '/includes/permisos.php';
if (!isset($_SESSION['usuario'])) {
    header("Location: login.html");
    exit();
}
if (!tiene_permiso('empleados.ver')) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - La Vicky</title>
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
        <?php $active = 'configuracion'; $titulo = 'Configuración del Sistema'; include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <div class="row">

                <?php if (tiene_permiso('empleados.ver')): ?>
                <div class="col-md-12 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white pt-3 pb-2 d-flex justify-content-between align-items-center">
                            <h5 class="m-0"><i class="fas fa-users text-info"></i> Gestión de Empleados</h5>
                            <?php if (tiene_permiso('empleados.gestionar')): ?>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                                <i class="fas fa-plus"></i> Añadir
                            </button>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Rol</th>
                                        <th>Eliminar</th>
                                    </tr>
                                </thead>
                                <tbody id="employeeTableBody">
                                    <tr><td colspan="3" class="text-center">Cargando...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (tiene_permiso('empleados.ver')): ?>
                <div class="col-md-12 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-dark text-white pt-3 pb-2">
                            <h5 class="m-0"><i class="fas fa-chart-pie me-2"></i> Rendimiento por Empleado (Ganancias Generadas)</h5>
                        </div>
                        <div class="card-body">
                            <div class="row" id="employeeStatsRows">
                                <div class="col-12 text-center text-muted">Cargando estadísticas...</div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Añadir Empleado -->
    <div class="modal fade" id="addEmployeeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addEmployeeForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Registrar Nuevo Empleado</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Nombre Completo</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Correo Electrónico (Usuario)</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Contraseña Provisional</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Rol</label>
                            <select name="rol_id" class="form-select" id="employeeRolSelect" required>
                                <option value="">Cargando...</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Crear Empleado</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            loadEmployees();
            loadEmployeeStats();
            checkAccess();
            loadEmployeeRoles();
        });

        async function checkAccess() {
            const res = await fetch('../backend/api.php?route=check_session');
            const data = await res.json();
            if (!data.logged_in) window.location.href = 'login.html';
            const permisos = data.permisos || [];
            const ok = permisos.includes('empleados.ver') || permisos.includes('permisos.gestionar');
            if (!ok) {
                alert('Acceso no autorizado');
                window.location.href = 'index.php';
            }
        }

        async function loadEmployees() {
            const tbody = document.getElementById('employeeTableBody');
            if (!tbody) return;
            const res = await fetch('../backend/api.php?route=get_employees');
            const data = await res.json();
            tbody.innerHTML = '';
            if (data.success) {
                data.data.forEach(emp => {
                    tbody.innerHTML += `
                        <tr>
                            <td>${emp.nombre}</td>
                            <td><span class="badge ${emp.rol_nombre === 'Administrador' ? 'bg-primary' : 'bg-info'}">${emp.rol_nombre}</span></td>
                            <td>
                                ${emp.id != 1 && tienePermiso('empleados.gestionar') ? `<button class="btn btn-sm btn-outline-danger" onclick="deleteEmployee(${emp.id})"><i class="fas fa-trash"></i></button>` : ''}
                            </td>
                        </tr>
                    `;
                });
            }
        }

        async function loadEmployeeStats() {
            const container = document.getElementById('employeeStatsRows');
            if (!container) return;
            const res = await fetch('../backend/api.php?route=get_employee_stats');
            const data = await res.json();
            container.innerHTML = '';
            if (data.success && data.data.length > 0) {
                data.data.forEach(stat => {
                    container.innerHTML += `
                        <div class="col-md-3 mb-3">
                            <div class="p-3 border rounded bg-light">
                                <h6 class="text-muted mb-1">${stat.nombre}</h6>
                                <h4 class="text-success">$${parseFloat(stat.total_ganado).toFixed(2)}</h4>
                                <small class="text-muted">Ganancias generadas</small>
                            </div>
                        </div>
                    `;
                });
            } else {
                container.innerHTML = '<div class="col-12 text-center text-muted">Aún no hay ventas registradas por empleados.</div>';
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
        });

        async function deleteEmployee(id) {
            if (!confirm('¿Eliminar este empleado?')) return;
            const res = await fetch('../backend/api.php?route=delete_employee', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            });
            const data = await res.json();
            if (data.success) loadEmployees();
            else alert(data.message);
        }

        function logout() { fetch('../backend/api.php?route=logout').then(() => window.location.href = 'login.html'); }

        // ── Roles para el alta de empleados ─────────────────────────────────
        async function loadEmployeeRoles() {
            const sel = document.getElementById('employeeRolSelect');
            if (!sel) return;
            try {
                const res = await fetch('../backend/api.php?route=get_roles');
                const data = await res.json();
                sel.innerHTML = '';
                if (data.success) {
                    data.data.forEach(r => {
                        const opt = document.createElement('option');
                        opt.value = r.id;
                        opt.textContent = r.nombre;
                        sel.appendChild(opt);
                    });
                } else {
                    sel.innerHTML = '<option value="">Sin roles disponibles</option>';
                }
            } catch (e) {
                sel.innerHTML = '<option value="">Error al cargar roles</option>';
            }
        }
    </script>
</body>

</html>
