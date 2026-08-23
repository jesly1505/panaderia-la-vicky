<?php
// frontend/reportes.php
session_start();
require_once __DIR__ . '/includes/permisos.php';

if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

if (!tiene_permiso('reportes.ver')) {
    header("Location: index.php");
    exit();
}

require_once __DIR__ . '/../backend/Helpers/DateFilterHelper.php';
$filter = $_GET['filter'] ?? 'all';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

$pageTitle = "Reportes";
$pageHeader = "Reportes y Estadísticas";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include 'includes/head.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .report-card { border: none; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); overflow: hidden; height: 100%; transition: var(--transition); }
        .report-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }
        .stat-icon { position: absolute; right: 1rem; top: 1rem; font-size: 2.5rem; opacity: 0.15; }
        .chart-container-premium { background: var(--white); padding: 1.5rem; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); }
        .export-item { border: 1px solid #eee; border-radius: var(--radius-sm); padding: 1rem; transition: var(--transition); background: var(--white); }
        .export-item:hover { background: var(--light); border-color: var(--primary-light); transform: translateX(3px); }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <div class="main-content">
            <?php include 'includes/navbar.php'; ?>

            <div class="container-fluid p-4 animate-fade-in">
                <?php echo \App\Helpers\DateFilterHelper::getFilterUI($filter, $start_date, $end_date, 'reportes.php'); ?>
                
                <!-- Summary Stats -->
                <div class="row g-4 mb-5">
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="report-card text-white" style="background: linear-gradient(135deg, #c0560f, #e07a34);">
                            <div class="card-body p-4 position-relative">
                                <h6 class="text-uppercase small fw-bold opacity-75 mb-1">Ventas (Periodo)</h6>
                                <h3 class="fw-bold mb-2" id="ventasVal">$0.00</h3>
                                <div class="small opacity-75">Ingresos brutos generados</div>
                                <i class="fas fa-calendar-day stat-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="report-card text-white" style="background: linear-gradient(135deg, #ff4b2b, #ff416c);">
                            <div class="card-body p-4 position-relative">
                                <h6 class="text-uppercase small fw-bold opacity-75 mb-1">Costos (Periodo)</h6>
                                <h3 class="fw-bold mb-2" id="costosVal">$0.00</h3>
                                <div class="small opacity-75">Costos de producción</div>
                                <i class="fas fa-file-invoice-dollar stat-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="report-card text-white" style="background: linear-gradient(135deg, #11998e, #38ef7d);">
                            <div class="card-body p-4 position-relative">
                                <h6 class="text-uppercase small fw-bold opacity-75 mb-1">Utilidad Neta</h6>
                                <h3 class="fw-bold mb-2" id="utilidadVal">$0.00</h3>
                                <div class="small opacity-75">Ganancias estimadas</div>
                                <i class="fas fa-chart-line stat-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="report-card text-white" style="background: linear-gradient(135deg, #a18cd1, #fbc2eb);">
                            <div class="card-body p-4 position-relative">
                                <h6 class="text-uppercase small fw-bold opacity-75 mb-1">Transacciones</h6>
                                <h3 class="fw-bold mb-2" id="transaccionesVal">0</h3>
                                <div class="small opacity-75">Ventas completadas</div>
                                <i class="fas fa-receipt stat-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-5">
                    <!-- Weekly Chart -->
                    <div class="col-12 col-lg-8">
                        <div class="chart-container-premium">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h5 class="mb-1 fw-bold text-dark">Tendencia de Ventas Semanales</h5>
                                    <p class="text-muted small mb-0">Ingresos de los últimos 7 días</p>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="../backend/api.php?route=export_ventas_csv&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-file-csv me-1"></i>CSV
                                    </a>
                                    <a href="../backend/api.php?route=export_ventas_pdf&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>" 
                                       class="btn btn-sm btn-outline-danger" target="_blank">
                                        <i class="fas fa-file-pdf me-1"></i>PDF
                                    </a>
                                </div>
                            </div>
                            <canvas id="ventasChart" height="100"></canvas>
                        </div>
                    </div>

                    <!-- Gastos Chart -->
                    <div class="col-12 col-lg-4">
                        <div class="chart-container-premium h-100">
                            <div class="mb-4">
                                <h5 class="mb-1 fw-bold text-dark">Gastos por Categoría</h5>
                                <p class="text-muted small mb-0">Distribución del periodo</p>
                            </div>
                            <canvas id="gastosChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Exports Section -->
                <div class="row g-4">
                    <div class="col-12">
                        <h5 class="fw-bold text-dark mb-3"><i class="fas fa-file-export me-2 text-primary"></i>Exportar Informes</h5>
                    </div>

                    <?php
                    $exports = [
                        ['label' => 'Reporte de Ventas', 'desc' => 'Historial completo con totales, ganancias y pagos.', 'icon' => 'fa-shopping-cart', 'color' => 'text-primary', 'route_csv' => 'export_ventas_csv', 'route_pdf' => 'export_ventas_pdf', 'extra' => '&start_date=' . urlencode($start_date) . '&end_date=' . urlencode($end_date)],
                        ['label' => 'Reporte de Insumos', 'desc' => 'Inventario actual, stock y costos unitarios.', 'icon' => 'fa-boxes', 'color' => 'text-success', 'route_csv' => 'export_insumos_csv', 'route_pdf' => 'export_insumos_pdf', 'extra' => ''],
                        ['label' => 'Reporte de Productos', 'desc' => 'Catálogo completo con precios y stock disponible.', 'icon' => 'fa-bread-slice', 'color' => 'text-warning', 'route_csv' => 'export_productos_csv', 'route_pdf' => 'export_productos_pdf', 'extra' => ''],
                        ['label' => 'Reporte de Gastos', 'desc' => 'Egresos por categoría y fecha del periodo.', 'icon' => 'fa-file-invoice-dollar', 'color' => 'text-danger', 'route_csv' => 'export_gastos_csv', 'route_pdf' => 'export_gastos_pdf', 'extra' => '&start_date=' . urlencode($start_date) . '&end_date=' . urlencode($end_date)],
                    ];
                    ?>

                    <?php foreach ($exports as $exp): ?>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="export-item text-center">
                                <i class="fas <?php echo $exp['icon']; ?> fs-2 <?php echo $exp['color']; ?> mb-3"></i>
                                <h6 class="fw-bold text-dark mb-1"><?php echo $exp['label']; ?></h6>
                                <p class="text-muted small mb-3"><?php echo $exp['desc']; ?></p>
                                <div class="d-flex gap-2">
                                    <a href="../backend/api.php?route=<?php echo $exp['route_csv'] . $exp['extra']; ?>" class="btn btn-sm btn-outline-secondary flex-fill">
                                        <i class="fas fa-file-csv me-1"></i>CSV
                                    </a>
                                    <a href="../backend/api.php?route=<?php echo $exp['route_pdf'] . $exp['extra']; ?>" class="btn btn-sm btn-outline-danger flex-fill" target="_blank">
                                        <i class="fas fa-file-pdf me-1"></i>PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Gastos por fecha (solo para quien tiene permiso) -->
                    <?php if (tiene_permiso('gastos.ver', 'gastos.gestionar')): ?>
                        <div class="col-12 mt-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-minus-circle me-2 text-danger"></i>Registro de Gastos</h5>
                                    <?php if (tiene_permiso('gastos.gestionar')): ?>
                                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#addGastoModal">
                                            <i class="fas fa-plus me-1"></i> Nuevo Gasto
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>Fecha</th>
                                                    <th>Categoría</th>
                                                    <th>Descripción</th>
                                                    <th>Monto</th>
                                                    <?php if (tiene_permiso('gastos.gestionar')): ?><th>Acción</th><?php endif; ?>
                                                </tr>
                                            </thead>
                                            <tbody id="gastosTableBody">
                                                <tr>
                                                    <td colspan="5" class="text-center py-4 text-muted">
                                                        <div class="spinner-border spinner-border-sm text-danger me-2"></div> Cargando gastos...
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo Gasto -->
    <div class="modal fade" id="addGastoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <form id="addGastoForm">
                    <div class="modal-header bg-danger text-white border-0">
                        <h5 class="modal-title fw-bold"><i class="fas fa-minus-circle me-2"></i>Registrar Gasto</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Categoría</label>
                            <select name="categoria" id="addGastoCategoria" class="form-select py-2" required>
                                <option value="" disabled selected>Cargando categorías...</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Monto ($)</label>
                            <input type="number" step="0.01" min="0.01" max="999999.99" name="monto" class="form-control py-2" required placeholder="0.00">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="2" maxlength="255" placeholder="Detalle del gasto..." required></textarea>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Fecha</label>
                            <input type="date" name="fecha" class="form-control py-2" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-3 bg-light">
                        <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger px-4 fw-bold shadow-sm">Guardar Gasto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Gasto -->
    <div class="modal fade" id="editGastoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <form id="editGastoForm">
                    <input type="hidden" name="id">
                    <div class="modal-header bg-warning text-dark border-0">
                        <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Editar Gasto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Categoría</label>
                            <select name="categoria" id="editGastoCategoria" class="form-select py-2" required>
                                <option value="" disabled selected>Cargando categorías...</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Monto ($)</label>
                            <input type="number" step="0.01" min="0.01" max="999999.99" name="monto" class="form-control py-2" required placeholder="0.00">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="2" maxlength="255" placeholder="Detalle del gasto..." required></textarea>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Fecha</label>
                            <input type="date" name="fecha" class="form-control py-2" required>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-3 bg-light">
                        <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning text-dark px-4 fw-bold shadow-sm">Actualizar Gasto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script>
        let ventasChart = null;
        let gastosChart = null;

        function escJs(value) {
            return String(value ?? '')
                .replace(/\\/g, '\\\\')
                .replace(/'/g, "\\'")
                .replace(/"/g, '&quot;')
                .replace(/\r?\n/g, ' ');
        }

        const urlParams = new URLSearchParams(window.location.search);
        const currentFilter = urlParams.get('filter') || 'all';
        const currentStart = urlParams.get('start_date') || '';
        const currentEnd = urlParams.get('end_date') || '';

        document.addEventListener('DOMContentLoaded', async () => {
            await loadCategorias();
            await loadStats();
            await loadVentasChart();
            await loadGastosChart();
            await loadGastosTable();
        });

        async function loadCategorias() {
            try {
                const res = await fetch('../backend/api.php?route=get_catalogo_activos&tipo=gastos');
                const data = await res.json();
                const opts = '<option value="" disabled selected>Seleccione categoría...</option>';
                const buildOpts = (items) => items.map(c => `<option value="${escapeHtml(c.valor)}">${escapeHtml(c.etiqueta)}</option>`).join('');
                if (data.success && data.data) {
                    const html = opts + buildOpts(data.data);
                    const addSel = document.getElementById('addGastoCategoria');
                    const editSel = document.getElementById('editGastoCategoria');
                    if (addSel) addSel.innerHTML = html;
                    if (editSel) editSel.innerHTML = html;
                }
            } catch (e) { console.error('Error loading categories:', e); }
        }

        async function loadStats() {
            try {
                const res = await fetch(`../backend/api.php?route=get_ventas_stats_reporte&start_date=${currentStart}&end_date=${currentEnd}`);
                const data = await res.json();
                if (data.success && data.data) {
                    const d = data.data;
                    document.getElementById('ventasVal').textContent = formatCurrency(d.total_ventas || 0);
                    document.getElementById('costosVal').textContent = formatCurrency(d.total_costos || 0);
                    document.getElementById('utilidadVal').textContent = formatCurrency(d.total_ganancias || 0);
                    document.getElementById('transaccionesVal').textContent = d.total_transacciones || 0;
                }
            } catch (e) { console.error(e); }
        }

        async function loadVentasChart() {
            try {
                const res = await fetch('../backend/api.php?route=get_ventas_semanales');
                const data = await res.json();
                if (data.success && data.data) {
                    const labels = data.data.map(d => d.dia);
                    const values = data.data.map(d => parseFloat(d.total || 0));
                    const profits = data.data.map(d => parseFloat(d.ganancias || 0));

                    const ctx = document.getElementById('ventasChart').getContext('2d');
                    if (ventasChart) ventasChart.destroy();
                    ventasChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'Ventas ($)',
                                    data: values,
                                    backgroundColor: 'rgba(192, 86, 15, 0.7)',
                                    borderColor: 'rgba(192, 86, 15, 1)',
                                    borderWidth: 2,
                                    borderRadius: 6,
                                },
                                {
                                    label: 'Ganancias ($)',
                                    data: profits,
                                    backgroundColor: 'rgba(17, 153, 142, 0.7)',
                                    borderColor: 'rgba(17, 153, 142, 1)',
                                    borderWidth: 2,
                                    borderRadius: 6,
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            plugins: { legend: { position: 'top' } },
                            scales: { y: { beginAtZero: true } }
                        }
                    });
                }
            } catch (e) { console.error(e); }
        }

        async function loadGastosChart() {
            try {
                const res = await fetch(`../backend/api.php?route=get_gastos_by_date&start_date=${currentStart}&end_date=${currentEnd}`);
                const data = await res.json();
                if (data.success && data.data && data.data.length > 0) {
                    const categorias = {};
                    data.data.forEach(g => {
                        categorias[g.categoria] = (categorias[g.categoria] || 0) + parseFloat(g.monto);
                    });

                    const ctx = document.getElementById('gastosChart').getContext('2d');
                    if (gastosChart) gastosChart.destroy();
                    gastosChart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: Object.keys(categorias),
                            datasets: [{
                                data: Object.values(categorias),
                                backgroundColor: ['#c0560f', '#11998e', '#a18cd1', '#fbc2eb', '#667eea', '#ff4b2b'],
                                borderWidth: 2
                            }]
                        },
                        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
                    });
                }
            } catch (e) { console.error(e); }
        }

        async function loadGastosTable() {
            const tbody = document.getElementById('gastosTableBody');
            if (!tbody) return;

            try {
                const res = await fetch(`../backend/api.php?route=get_gastos_by_date&start_date=${currentStart}&end_date=${currentEnd}`);
                const data = await res.json();
                tbody.innerHTML = '';

                if (data.success && data.data && data.data.length > 0) {
                    const puedeGestionar = (typeof tienePermiso === 'function' ? tienePermiso('gastos.gestionar') : true);
                    data.data.forEach(g => {
                        tbody.innerHTML += `
                            <tr>
                                <td><small>${g.fecha}</small></td>
                                <td><span class="badge bg-light text-dark border">${g.categoria}</span></td>
                                <td class="text-muted small">${g.descripcion || '-'}</td>
                                <td class="fw-bold text-danger">${formatCurrency(g.monto)}</td>
                                ${puedeGestionar ? `
                                    <td>
                                        <button class="btn btn-sm btn-link text-warning p-0 me-1" onclick="openEditGastoModal(${g.id}, '${escJs(g.fecha)}', '${escJs(g.categoria)}', ${parseFloat(g.monto)}, '${escJs(g.descripcion || '')}')" title="Editar">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                        <button class="btn btn-sm btn-link text-danger p-0" onclick="deleteGasto(${g.id})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                ` : ''}
                            </tr>
                        `;
                    });
                } else {
                    const cols = (typeof tienePermiso === 'function' && tienePermiso('gastos.gestionar')) ? 5 : 4;
                    tbody.innerHTML = `<tr><td colspan="${cols}" class="text-center py-4 text-muted">No hay gastos registrados para el periodo seleccionado.</td></tr>`;
                }
            } catch (e) { console.error(e); }
        }

        document.getElementById('addGastoForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const payload = Object.fromEntries(formData);

            try {
                const res = await fetch('../backend/api.php?route=add_gasto', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('addGastoModal')).hide();
                    e.target.reset();
                    await loadStats();
                    await loadGastosChart();
                    await loadGastosTable();
                } else {
                    alert(data.message || 'Error al guardar el gasto.');
                }
            } catch (err) {
                console.error(err);
            }
        });

        async function deleteGasto(id) {
            if (!confirm('¿Está seguro de eliminar este gasto?')) return;
            try {
                const res = await fetch('../backend/api.php?route=delete_gasto', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });
                const data = await res.json();
                if (data.success) {
                    await loadStats();
                    await loadGastosChart();
                    await loadGastosTable();
                } else {
                    alert(data.message || 'Error al eliminar el gasto.');
                }
            } catch (e) { console.error(e); }
        }

        function openEditGastoModal(id, fecha, categoria, monto, descripcion) {
            const form = document.getElementById('editGastoForm');
            form.elements['id'].value = id;
            form.elements['fecha'].value = fecha;
            form.elements['categoria'].value = categoria;
            form.elements['monto'].value = monto;
            form.elements['descripcion'].value = descripcion;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('editGastoModal')).show();
        }

        document.getElementById('editGastoForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const payload = Object.fromEntries(formData);

            try {
                const res = await fetch('../backend/api.php?route=update_gasto', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('editGastoModal')).hide();
                    await loadStats();
                    await loadGastosChart();
                    await loadGastosTable();
                } else {
                    alert(data.message || 'Error al actualizar el gasto.');
                }
            } catch (err) {
                console.error(err);
            }
        });
    </script>
</body>
</html>
