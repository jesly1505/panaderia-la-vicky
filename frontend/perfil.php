<?php
// frontend/perfil.php
session_start();
require_once __DIR__ . '/includes/permisos.php';

if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

if (!tiene_permiso('perfil.gestionar')) {
    header("Location: index.php");
    exit();
}

$pageTitle = "Perfil de la Empresa";
$pageHeader = "Datos del Negocio";
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
                <div class="row justify-content-center">
                    <div class="col-12 col-lg-8">
                        <div id="resultAlert" class="alert d-none mb-4"></div>

                        <div class="card border-0 shadow-sm border-top border-4 border-primary">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-store me-2 text-primary"></i>Perfil de la Panadería</h5>
                                <p class="text-muted small mb-0">Información que se muestra en facturas y que rige los cálculos de impuestos.</p>
                            </div>
                            <div class="card-body p-4">
                                <form id="perfilForm">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small text-uppercase text-muted">Nombre del Negocio <span class="text-danger">*</span></label>
                                            <input type="text" name="nombre" class="form-control py-2" maxlength="100" required placeholder="Panadería La Vicky">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small text-uppercase text-muted">Moneda <span class="text-danger">*</span></label>
                                            <select name="moneda" class="form-select py-2" required>
                                                <option value="USD">USD - Dólar ($)</option>
                                                <option value="NIO">NIO - Córdoba (C$)</option>
                                                <option value="MXN">MXN - Peso mexicano ($)</option>
                                                <option value="EUR">EUR - Euro (€)</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-bold small text-uppercase text-muted">Descripción</label>
                                            <input type="text" name="descripcion" class="form-control py-2" maxlength="255" placeholder="Ej. Panadería &amp; Pastelería">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small text-uppercase text-muted">Dirección</label>
                                            <input type="text" name="direccion" class="form-control py-2" maxlength="255" placeholder="Av. Principal calle 5">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold small text-uppercase text-muted">Teléfono</label>
                                            <input type="tel" name="telefono" class="form-control py-2" maxlength="30" placeholder="1234-5678">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold small text-uppercase text-muted">RUC / NIT</label>
                                            <input type="text" name="ruc" class="form-control py-2" maxlength="30">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold small text-uppercase text-muted">Tasa de Impuesto (%) <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="number" name="tasa_impuesto" class="form-control py-2" min="0" max="100" step="0.01" required placeholder="15">
                                                <span class="input-group-text">%</span>
                                            </div>
                                            <div class="form-text">Se aplica al subtotal de cada venta (ej. IVA 15%).</div>
                                        </div>
                                    </div>

                                    <div class="mt-4 pt-2 border-top">
                                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                                            <i class="fas fa-save me-2"></i>Guardar Cambios
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script>
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

        document.addEventListener('DOMContentLoaded', loadPerfil);
    </script>
</body>
</html>
