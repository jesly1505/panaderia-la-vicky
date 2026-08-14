<?php
session_start();
require_once __DIR__ . '/includes/permisos.php';
if (!isset($_SESSION['usuario'])) {
    header("Location: login.html");
    exit();
}
if (!tiene_permiso('perfil.gestionar')) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de la Panadería - La Vicky</title>
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
        <?php $active = 'perfil'; $titulo = 'Perfil de la Panadería'; include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">

            <!-- RESULT ALERT -->
            <div id="resultAlert" class="alert d-none"></div>

            <div class="table-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="m-0"><i class="fas fa-store text-primary me-2"></i>Datos del Negocio</h5>
                </div>
                <div class="text-muted small mb-4">
                    Estos datos se muestran en la cabecera de la factura y se usan para calcular los impuestos de las ventas.
                </div>

                <form id="perfilForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nombre del Negocio <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control" maxlength="100" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Moneda <span class="text-danger">*</span></label>
                            <select name="moneda" class="form-select" required>
                                <option value="USD">USD - Dólar ($)</option>
                                <option value="NIO">NIO - Córdoba (C$)</option>
                                <option value="MXN">MXN - Peso mexicano ($)</option>
                                <option value="EUR">EUR - Euro (€)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Descripción</label>
                            <input type="text" name="descripcion" class="form-control" maxlength="255" placeholder="Ej. Panadería & Pastelería">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Dirección</label>
                            <input type="text" name="direccion" class="form-control" maxlength="255">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Teléfono</label>
                            <input type="text" name="telefono" class="form-control" maxlength="30">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">RUC/NIT</label>
                            <input type="text" name="ruc" class="form-control" maxlength="30">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Tasa de Impuesto (%) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="tasa_impuesto" class="form-control" min="0" max="100" step="0.01" required>
                                <span class="input-group-text">%</span>
                            </div>
                            <div class="form-text">Se aplica al subtotal de cada venta (ej. IVA 15%).</div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // ── 1. Carga del perfil ─────────────────────────────────────────────
        async function loadPerfil() {
            const form = document.getElementById('perfilForm');
            try {
                const res = await fetch('../backend/api.php?route=get_perfil_empresa');
                const json = await res.json();
                if (!json.success) {
                    mostrarAlerta('danger', json.message || 'Error al cargar el perfil.');
                    return;
                }
                const d = json.data || {};
                form.nombre.value = d.nombre || '';
                form.descripcion.value = d.descripcion || '';
                form.direccion.value = d.direccion || '';
                form.telefono.value = d.telefono || '';
                form.ruc.value = d.ruc || '';
                form.moneda.value = d.moneda || 'USD';
                form.tasa_impuesto.value = d.tasa_impuesto != null ? d.tasa_impuesto : 15;
            } catch (e) {
                mostrarAlerta('danger', 'Error de conexión con el servidor.');
            }
        }

        // ── 2. Guardado del perfil ──────────────────────────────────────────
        document.getElementById('perfilForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const body = {
                nombre: form.nombre.value,
                descripcion: form.descripcion.value,
                direccion: form.direccion.value,
                telefono: form.telefono.value,
                ruc: form.ruc.value,
                moneda: form.moneda.value,
                tasa_impuesto: form.tasa_impuesto.value
            };
            try {
                const res = await fetch('../backend/api.php?route=set_perfil_empresa', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(body)
                });
                const json = await res.json();
                mostrarAlerta(json.success ? 'success' : 'danger', json.message || 'Error al guardar el perfil.');
            } catch (err) {
                mostrarAlerta('danger', 'Error de conexión con el servidor.');
            }
        });

        // ── 3. Utilidades ──────────────────────────────────────────────────
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

        document.addEventListener('DOMContentLoaded', loadPerfil);
    </script>
</body>

</html>
