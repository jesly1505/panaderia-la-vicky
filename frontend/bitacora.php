<?php
session_start();
require_once __DIR__ . '/includes/permisos.php';
if (!isset($_SESSION['usuario'])) {
    header("Location: login.html");
    exit();
}
if (!tiene_permiso('auditoria.ver')) {
    header("Location: index.php");
    exit();
}
$active = 'bitacora';
$titulo = 'Bitácora de Seguridad';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bitácora - La Vicky</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background-color: #f4f6f9; }
        .sidebar { height: 100vh; background-color: #685569; padding-top: 20px; position: fixed; width: 16.666667%; overflow-y: auto; }
        .sidebar a { padding: 15px 20px; text-decoration: none; font-size: 16px; color: #d1d8e0; display: block; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { color: #fff; background-color: #0d6efd; }
        .main-content { padding: 30px; margin-left: 16.666667%; }
        .top-navbar { background-color: #fff; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; margin-left: 16.666667%; }
        .table-responsive { max-height: 480px; overflow-y: auto; }
    </style>
</head>

<body>
    <div class="container-fluid p-0">
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <div class="row g-3">
                <div class="col-md-7">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center pt-4 pb-3">
                            <h5 class="card-title m-0"><i class="fas fa-clipboard-list me-2"></i>Bitácora del Sistema</h5>
                            <button class="btn btn-outline-secondary btn-sm" onclick="loadBitacora()">
                                <i class="fas fa-sync-alt"></i> Refrescar</button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Usuario</th>
                                            <th>Módulo</th>
                                            <th>Acción</th>
                                            <th>Detalles</th>
                                            <th>IP</th>
                                            <th>Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody id="bitacora-body">
                                        <tr><td colspan="7" class="text-center text-muted">Cargando...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center pt-4 pb-3">
                            <h5 class="card-title m-0"><i class="fas fa-ban me-2"></i>Accesos Denegados</h5>
                            <button class="btn btn-outline-secondary btn-sm" onclick="loadDenied()">
                                <i class="fas fa-sync-alt"></i> Refrescar</button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Usuario</th>
                                            <th>Módulo intentado</th>
                                            <th>IP</th>
                                            <th>Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody id="denied-body">
                                        <tr><td colspan="5" class="text-center text-muted">Cargando...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/api.js"></script>
    <script>
        async function loadBitacora() {
            const body = document.getElementById('bitacora-body');
            body.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Cargando...</td></tr>';
            try {
                const data = await api('get_bitacora');
                if (!data.success) throw new Error(data.message);
                if (data.data.length === 0) {
                    body.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Sin registros</td></tr>';
                    return;
                }
                body.innerHTML = data.data.map(r => `
                    <tr>
                        <td>${r.id}</td>
                        <td>${escapeHtml(r.usuario)}</td>
                        <td>${escapeHtml(r.modulo)}</td>
                        <td>${escapeHtml(r.accion)}</td>
                        <td>${escapeHtml(r.detalles)}</td>
                        <td>${escapeHtml(r.ip_address)}</td>
                        <td class="small text-muted">${escapeHtml(r.fecha)}</td>
                    </tr>`).join('');
            } catch (e) {
                body.innerHTML = `<tr><td colspan="7" class="text-center text-danger">Error: ${escapeHtml(e.message)}</td></tr>`;
            }
        }

        async function loadDenied() {
            const body = document.getElementById('denied-body');
            body.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Cargando...</td></tr>';
            try {
                const data = await api('get_accesos_denegados');
                if (!data.success) throw new Error(data.message);
                if (data.data.length === 0) {
                    body.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Sin registros</td></tr>';
                    return;
                }
                body.innerHTML = data.data.map(r => `
                    <tr>
                        <td>${r.id}</td>
                        <td>${escapeHtml(r.usuario)}</td>
                        <td>${escapeHtml(r.modulo_intentado)}</td>
                        <td>${escapeHtml(r.ip_address)}</td>
                        <td class="small text-muted">${escapeHtml(r.fecha)}</td>
                    </tr>`).join('');
            } catch (e) {
                body.innerHTML = `<tr><td colspan="5" class="text-center text-danger">Error: ${escapeHtml(e.message)}</td></tr>`;
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            loadBitacora();
            loadDenied();
            setInterval(loadBitacora, 30000);
        });
    </script>
</body>

</html>
