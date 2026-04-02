<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - La Vicky</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background-color: #f4f6f9;
        }

        .sidebar {
            height: 100vh;
            background-color: #1c67b1;
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
            background-color: #685569;
        }

        .main-content {
            padding: 30px;
            margin-left: 16.666667%;
        }

        .top-navbar {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(130, 18, 135, 0.1);
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
        <!-- Sidebar -->
        <div class="col-md-2 sidebar d-none d-md-block">
            <div class="text-center mb-4">
                <h3 class="text-white">🥖 La Vicky</h3>
            </div>
            <a href="index.php"><i class="fas fa-home me-2"></i> Dashboard</a>
            <a href="inventario.php"><i class="fas fa-box me-2"></i> Inventario</a>
            <a href="productos.php"><i class="fas fa-bread-slice me-2"></i> Productos</a>
            <a href="produccion_manual.php"><i class="fas fa-industry me-2"></i> Prod. Manual</a>
            <a href="pedidos.php"><i class="fas fa-shopping-cart me-2"></i> Pedidos</a>
            <a href="ventas.php"><i class="fas fa-chart-line me-2"></i> Ventas</a>
            <a href="clientes.php" class="active"><i class="fas fa-users me-2"></i> Clientes</a>
            <a href="reportes.php"><i class="fas fa-file-alt me-2"></i> Reportes</a>
            <a href="configuracion.php"><i class="fas fa-cog me-2"></i> Configuración</a>
        </div>

        <!-- Top Navbar -->
        <div class="top-navbar">
            <div>
                <h4 class="m-0">Gestión de Clientes</h4>
            </div>
            <div>
                <span class="me-3"><i class="fas fa-user-circle"></i> Administrador</span>
                <a href="#" class="btn btn-outline-danger btn-sm" onclick="logout()"><i class="fas fa-sign-out-alt"></i>
                    Salir</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center pt-4 pb-3">
                    <h5 class="card-title m-0">Directorio de Clientes</h5>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addClienteModal">
                        <i class="fas fa-user-plus"></i> Nuevo Cliente
                    </button>
                </div>
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Dirección</th>
                                <th>Puntos</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="clientesTableBody">
                            <tr>
                                <td colspan="6" class="text-center text-muted">Cargando...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Historial -->
    <div class="modal fade" id="historialModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Historial de Compras</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-sm">
                        <thead>
                            <tr><th>Fecha</th><th>Venta ID</th><th>Total</th><th>Ganancia</th></tr>
                        </thead>
                        <tbody id="historialTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Cliente Modal -->
    <div class="modal fade" id="addClienteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addClienteForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Registrar Nuevo Cliente</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Nombre Completo</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Correo Electrónico (Opcional)</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Teléfono (Opcional)</label>
                            <input type="text" name="telefono" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Dirección Física (Opcional)</label>
                            <textarea name="direccion" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Registrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Cliente Modal -->
    <div class="modal fade" id="editClienteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editClienteForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Cliente</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="editClientId">
                        <div class="mb-3">
                            <label>Nombre Completo</label>
                            <input type="text" name="nombre" id="editClientNombre" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Correo Electrónico (Opcional)</label>
                            <input type="email" name="email" id="editClientEmail" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Teléfono (Opcional)</label>
                            <input type="text" name="telefono" id="editClientTelefono" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Dirección Física (Opcional)</label>
                            <textarea name="direccion" id="editClientDireccion" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/common.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', loadClientes);

        async function loadClientes() {
            try {
                const response = await fetch('../backend/api.php?route=get_clientes');
                const data = await response.json();
                const tbody = document.getElementById('clientesTableBody');
                tbody.innerHTML = '';

                if (data.success && data.data.length > 0) {
                    data.data.forEach(c => {
                        tbody.innerHTML += `
                            <tr>
                                <td>${c.id}</td>
                                <td class="fw-bold">${c.nombre}</td>
                                <td>${c.email || '-'}</td>
                                <td>${c.telefono || '-'}</td>
                                <td>${c.direccion || '-'}</td>
                                <td><span class="badge bg-warning text-dark"><i class="fas fa-star"></i> ${c.puntos_fidelidad}</span></td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-primary" onclick="viewHistory(${c.id})" title="Ver Historial"><i class="fas fa-history"></i></button>
                                        <button class="btn btn-sm btn-outline-warning" onclick='editCliente(${JSON.stringify(c).replace(/'/g, "&#39;")})' title="Editar"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteCliente(${c.id}, '${c.nombre}')" title="Eliminar"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No existen clientes registrados.</td></tr>';
                }
            } catch (err) {
                console.error(err);
            }
        }

        document.getElementById('addClienteForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            try {
                const response = await fetch('../backend/api.php?route=add_cliente', {
                    method: 'POST', body: formData
                });
                const res = await response.json();
                if (res.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addClienteModal'));
                    modal.hide();
                    e.target.reset();
                    loadClientes();
                } else alert(res.message);
            } catch (err) { alert('Error del servidor'); }
        });

        async function viewHistory(id) {
            const res = await fetch(`../backend/api.php?route=get_cliente_historial&id=${id}`);
            const data = await res.json();
            const body = document.getElementById('historialTableBody');
            body.innerHTML = '';
            if(data.success && data.data.length > 0) {
                data.data.forEach(v => {
                    body.innerHTML += `<tr><td>${v.fecha_venta}</td><td>#${v.id}</td><td>$${v.total}</td><td>$${v.ganancias}</td></tr>`;
                });
            } else {
                body.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Sin compras registradas.</td></tr>';
            }
            new bootstrap.Modal(document.getElementById('historialModal')).show();
        }

        function editCliente(c) {
            document.getElementById('editClientId').value = c.id;
            document.getElementById('editClientNombre').value = c.nombre;
            document.getElementById('editClientEmail').value = c.email || '';
            document.getElementById('editClientTelefono').value = c.telefono || '';
            document.getElementById('editClientDireccion').value = c.direccion || '';
            new bootstrap.Modal(document.getElementById('editClienteModal')).show();
        }

        document.getElementById('editClienteForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            try {
                const response = await fetch('../backend/api.php?route=update_cliente', {
                    method: 'POST', body: formData
                });
                const res = await response.json();
                if (res.success) {
                    bootstrap.Modal.getInstance(document.getElementById('editClienteModal')).hide();
                    loadClientes();
                } else alert(res.message);
            } catch (err) { alert('Error de red al actualizar'); }
        });

        async function deleteCliente(id, nombre) {
            if (!confirm(`¿Estás seguro de que deseas eliminar al cliente "${nombre}"?\nEsto no será posible si el cliente tiene pedidos o ventas asociadas a su historial.`)) return;
            
            const formData = new FormData();
            formData.append('id', id);
            try {
                const response = await fetch('../backend/api.php?route=delete_cliente', {
                    method: 'POST', body: formData
                });
                const res = await response.json();
                if (res.success) {
                    loadClientes();
                } else {
                    alert(res.message);
                }
            } catch (err) { alert('Error de red al eliminar'); }
        }

        function logout() { fetch('../backend/api.php?route=logout').then(() => window.location.href = 'login.html'); }
    </script>
</body>

</html>
