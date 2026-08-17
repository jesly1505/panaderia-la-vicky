<?php
// frontend/bitacora.php
session_start();
require_once __DIR__ . '/includes/permisos.php';

if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

if (!tiene_permiso('auditoria.ver')) {
    header("Location: index.php");
    exit();
}

$pageTitle = "Bitácora";
$pageHeader = "Auditoría y Trazabilidad";
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
                <div class="row g-4">
                    <!-- Bitácora del Sistema -->
                    <div class="col-12 col-lg-7">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                                <h5 class="card-title mb-0 fw-bold text-dark">
                                    <i class="fas fa-clipboard-list me-2 text-primary"></i>Bitácora del Sistema
                                </h5>
                                <button class="btn btn-sm btn-outline-secondary" onclick="loadBitacora()">
                                    <i class="fas fa-sync-alt me-1"></i> Refrescar
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height: 520px; overflow-y: auto;">
                                    <table class="table table-hover table-sm align-middle mb-0">
                                        <thead class="table-light sticky-top">
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
                                            <tr><td colspan="7" class="text-center py-4 text-muted">
                                                <div class="spinner-border spinner-border-sm me-2"></div>Cargando...
                                            </td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Accesos Denegados -->
                    <div class="col-12 col-lg-5">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                                <h5 class="card-title mb-0 fw-bold text-dark">
                                    <i class="fas fa-ban me-2 text-danger"></i>Accesos Denegados
                                </h5>
                                <button class="btn btn-sm btn-outline-secondary" onclick="loadDenied()">
                                    <i class="fas fa-sync-alt me-1"></i> Refrescar
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height: 520px; overflow-y: auto;">
                                    <table class="table table-hover table-sm align-middle mb-0">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th>#</th>
                                                <th>Usuario</th>
                                                <th>Módulo Intentado</th>
                                                <th>IP</th>
                                                <th>Fecha</th>
                                            </tr>
                                        </thead>
                                        <tbody id="denied-body">
                                            <tr><td colspan="5" class="text-center py-4 text-muted">
                                                <div class="spinner-border spinner-border-sm me-2"></div>Cargando...
                                            </td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script>
        function escapeHtml(text) {
            if (!text) return '-';
            return String(text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        async function loadBitacora() {
            const body = document.getElementById('bitacora-body');
            body.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>Cargando...</td></tr>';
            try {
                const res = await fetch('../backend/api.php?route=get_bitacora');
                const data = await res.json();
                if (!data.success) throw new Error(data.message);
                if (!data.data || data.data.length === 0) {
                    body.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">Sin registros en bitácora.</td></tr>';
                    return;
                }
                body.innerHTML = data.data.map(r => `
                    <tr>
                        <td class="text-muted fw-bold">${r.id}</td>
                        <td>${escapeHtml(r.usuario)}</td>
                        <td><span class="badge bg-light text-dark border small">${escapeHtml(r.modulo)}</span></td>
                        <td class="fw-semibold">${escapeHtml(r.accion)}</td>
                        <td class="text-muted" style="max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${escapeHtml(r.detalles)}">${escapeHtml(r.detalles)}</td>
                        <td class="text-muted small">${escapeHtml(r.ip_address)}</td>
                        <td class="small text-muted">${escapeHtml(r.fecha_hora || r.fecha)}</td>
                    </tr>
                `).join('');
            } catch (e) {
                body.innerHTML = `<tr><td colspan="7" class="text-center text-danger py-3">Error: ${escapeHtml(e.message)}</td></tr>`;
            }
        }

        async function loadDenied() {
            const body = document.getElementById('denied-body');
            body.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>Cargando...</td></tr>';
            try {
                const res = await fetch('../backend/api.php?route=get_accesos_denegados');
                const data = await res.json();
                if (!data.success) throw new Error(data.message);
                if (!data.data || data.data.length === 0) {
                    body.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No hay intentos de acceso denegados registrados.</td></tr>';
                    return;
                }
                body.innerHTML = data.data.map(r => `
                    <tr>
                        <td class="text-muted fw-bold">${r.id}</td>
                        <td class="fw-semibold text-danger">${escapeHtml(r.usuario)}</td>
                        <td><span class="badge bg-danger bg-opacity-10 text-danger small">${escapeHtml(r.modulo_intentado)}</span></td>
                        <td class="text-muted small">${escapeHtml(r.ip_address)}</td>
                        <td class="small text-muted">${escapeHtml(r.fecha)}</td>
                    </tr>
                `).join('');
            } catch (e) {
                body.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-3">Error: ${escapeHtml(e.message)}</td></tr>`;
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            loadBitacora();
            loadDenied();
            // Auto-refresh every 30 seconds
            setInterval(() => {
                loadBitacora();
                loadDenied();
            }, 30000);
        });
    </script>
</body>
</html>
