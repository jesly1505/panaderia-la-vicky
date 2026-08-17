<?php
// frontend/produccion_manual.php
session_start();
require_once __DIR__ . '/includes/permisos.php';

if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

if (!tiene_permiso('produccion.ver')) {
    header("Location: index.php");
    exit();
}

require_once __DIR__ . '/../backend/Helpers/DateFilterHelper.php';
$filter = $_GET['filter'] ?? 'all';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

$pageTitle = "Producción Manual";
$pageHeader = "Registro de Producción Libre";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include 'includes/head.php'; ?>
    <style>
        .prod-form-card { border: none; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); }
        .insumo-row-premium { background: var(--light); border-radius: var(--radius-sm); border: 1px solid #eee; padding: 12px; margin-bottom: 10px; transition: var(--transition); }
        .insumo-row-premium:hover { border-color: var(--primary-light); background: var(--white); }
        .history-card { border: none; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); overflow: hidden; }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <div class="main-content">
            <?php include 'includes/navbar.php'; ?>

            <div class="container-fluid p-4 animate-fade-in">
                <?php echo \App\Helpers\DateFilterHelper::getFilterUI($filter, $start_date, $end_date, 'produccion_manual.php'); ?>
                
                <!-- Status Alert -->
                <div id="resultAlert" class="alert d-none shadow-sm border-0 mb-4 animate-fade-in" role="alert"></div>

                <div class="row g-4">
                    <!-- Form Column -->
                    <div class="col-12 col-lg-5">
                        <div class="card prod-form-card border-top border-4 border-primary">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-plus-circle me-2 text-primary"></i>Nueva Producción</h5>
                                <p class="text-muted small mb-0 mt-1">Registre manualmente la fabricación de productos</p>
                            </div>
                            <div class="card-body p-4">
                                <form id="produccionForm">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold small text-muted text-uppercase">Producto Final</label>
                                        <select id="selectProducto" class="form-select py-2" required>
                                            <option value="">Seleccione un producto...</option>
                                        </select>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label fw-bold small text-muted text-uppercase">Unidades Producidas</label>
                                        <input type="number" id="inputCantidadProd" class="form-control py-2 fw-bold h4 mb-0 text-primary" min="1" max="99999" step="1" required placeholder="0">
                                    </div>

                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label fw-bold small text-muted text-uppercase mb-0">Insumos Utilizados</label>
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addInsumoRow()">
                                                <i class="fas fa-plus me-1"></i> Añadir Insumo
                                            </button>
                                        </div>
                                        <div id="insumosContainer"></div>
                                    </div>

                                    <?php if (tiene_permiso('produccion.gestionar')): ?>
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary py-3 fw-bold shadow-sm">
                                                <i class="fas fa-industry me-2"></i> Registrar Producción
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-warning p-2 small mb-0">
                                            <i class="fas fa-lock me-1"></i> No dispone del permiso para registrar producción.
                                        </div>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- History Column -->
                    <div class="col-12 col-lg-7">
                        <div class="card history-card">
                            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-history me-2 text-secondary"></i>Historial de Producción</h5>
                                <button class="btn btn-sm btn-outline-secondary" onclick="loadHistory()">
                                    <i class="fas fa-sync-alt me-1"></i> Actualizar
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Producto</th>
                                                <th class="text-center">Cant.</th>
                                                <th>Insumos Usados</th>
                                            </tr>
                                        </thead>
                                        <tbody id="historialBody">
                                            <tr>
                                                <td colspan="4" class="text-center py-5 text-muted">
                                                    <div class="spinner-border spinner-border-sm text-primary me-2"></div> Cargando...
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
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script>
        let allInsumos = [];
        let allProducts = [];

        document.addEventListener('DOMContentLoaded', async () => {
            await loadInsumos();
            await loadProductos();
            await loadHistory();
        });

        async function loadInsumos() {
            try {
                const res = await fetch('../backend/api.php?route=get_insumos');
                const data = await res.json();
                if (data.success) allInsumos = data.data;
            } catch (e) { console.error(e); }
        }

        async function loadProductos() {
            try {
                const res = await fetch('../backend/api.php?route=get_productos');
                const data = await res.json();
                const select = document.getElementById('selectProducto');
                if (data.success && data.data) {
                    allProducts = data.data;
                    data.data.forEach(p => {
                        select.innerHTML += `<option value="${p.id}">${p.nombre}</option>`;
                    });
                }
            } catch (e) { console.error(e); }
        }

        function addInsumoRow() {
            const container = document.getElementById('insumosContainer');
            const rowId = Date.now();
            let options = '<option value="" disabled selected>Seleccione insumo...</option>';
            allInsumos.forEach(i => {
                options += `<option value="${i.id}" data-um="${i.unidad_medida}">${i.nombre} (Disponible: ${parseFloat(i.stock_actual).toFixed(2)} ${i.unidad_medida})</option>`;
            });

            const row = document.createElement('div');
            row.className = 'insumo-row-premium';
            row.id = `row-${rowId}`;
            row.innerHTML = `
                <div class="row g-2 align-items-center">
                    <div class="col-6">
                        <select name="insumo_id[]" class="form-select form-select-sm" required onchange="updateUm(this, '${rowId}')">
                            ${options}
                        </select>
                    </div>
                    <div class="col-4">
                        <div class="input-group input-group-sm">
                            <input type="number" step="0.001" min="0.001" name="cantidad_usada[]" class="form-control" placeholder="Cant." required>
                            <span class="input-group-text small" id="um-${rowId}">u</span>
                        </div>
                    </div>
                    <div class="col-2 text-end">
                        <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="document.getElementById('row-${rowId}').remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(row);
        }

        function updateUm(select, rowId) {
            const opt = select.options[select.selectedIndex];
            document.getElementById(`um-${rowId}`).textContent = opt.getAttribute('data-um') || 'u';
        }

        document.getElementById('produccionForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const alert = document.getElementById('resultAlert');

            const productoId = document.getElementById('selectProducto').value;
            const cantidad = document.getElementById('inputCantidadProd').value;

            if (!productoId || !cantidad || parseInt(cantidad) <= 0) {
                showAlert('Por favor complete todos los campos requeridos.', 'danger');
                return;
            }

            const insumoIds = [...document.querySelectorAll('[name="insumo_id[]"]')].map(el => el.value);
            const cantidades = [...document.querySelectorAll('[name="cantidad_usada[]"]')].map(el => el.value);

            const insumos = [];
            for (let i = 0; i < insumoIds.length; i++) {
                if (insumoIds[i] && cantidades[i]) {
                    insumos.push({ insumo_id: parseInt(insumoIds[i]), cantidad_usada: parseFloat(cantidades[i]) });
                }
            }

            const payload = { producto_id: parseInt(productoId), cantidad_producida: parseInt(cantidad), insumos_usados: insumos };

            try {
                const res = await fetch('../backend/api.php?route=add_produccion_manual', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();

                if (data.success) {
                    showAlert('✅ Producción registrada exitosamente. Stock actualizado.', 'success');
                    e.target.reset();
                    document.getElementById('insumosContainer').innerHTML = '';
                    await loadHistory();
                } else if (data.insuficiente) {
                    showAlert('⚠️ Stock insuficiente para: ' + data.insuficiente.join(', '), 'warning');
                } else {
                    showAlert('Error: ' + (data.message || 'Ocurrió un error inesperado.'), 'danger');
                }
            } catch (err) {
                showAlert('Error de conexión con el servidor.', 'danger');
                console.error(err);
            }
        });

        function showAlert(msg, type) {
            const el = document.getElementById('resultAlert');
            el.className = `alert alert-${type} shadow-sm border-0 mb-4 animate-fade-in`;
            el.innerHTML = msg;
        }

        async function loadHistory() {
            const urlParams = new URLSearchParams(window.location.search);
            const filter = urlParams.get('filter') || 'all';
            const startDate = urlParams.get('start_date') || '';
            const endDate = urlParams.get('end_date') || '';

            try {
                const res = await fetch(`../backend/api.php?route=get_produccion_historial&filter=${filter}&start_date=${startDate}&end_date=${endDate}`);
                const data = await res.json();
                const tbody = document.getElementById('historialBody');
                tbody.innerHTML = '';

                if (data.success && data.data && data.data.length > 0) {
                    data.data.forEach(h => {
                        tbody.innerHTML += `
                            <tr>
                                <td><small class="text-muted">${h.fecha}</small></td>
                                <td class="fw-semibold text-dark">${h.producto_nombre}</td>
                                <td class="text-center"><span class="badge bg-primary rounded-pill">${h.cantidad_producida}</span></td>
                                <td><small class="text-muted">${h.detalles_insumos || '<em>N/A</em>'}</small></td>
                            </tr>
                        `;
                    });
                } else {
                    tbody.innerHTML = `<tr><td colspan="4" class="text-center py-5 text-muted">No hay registros de producción para el periodo seleccionado.</td></tr>`;
                }
            } catch (e) {
                console.error(e);
            }
        }
    </script>
</body>
</html>
