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
    <title>Reportes - La Vicky</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #f4f6f9; }
        .sidebar { height: 100vh; background-color: #685569; padding-top: 20px; position: fixed; width: 16.666667%; overflow-y: auto; }
        .sidebar a { padding: 15px 20px; text-decoration: none; font-size: 16px; color: #d1d8e0; display: block; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { color: #fff; background-color: #0d6efd; }
        .main-content { padding: 30px; margin-left: 16.666667%; }
        .top-navbar { background-color: #fff; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; margin-left: 16.666667%; }
        .chart-container { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .stat-card { border-radius: 12px; border: none; }
        .stat-card .card-body { padding: 1.5rem; }
        .stat-value { font-size: 1.8rem; font-weight: 700; }
        .stat-label { font-size: 0.8rem; text-transform: uppercase; letter-spacing: .05em; opacity: .75; }
    </style>
</head>
<body>
    <div class="container-fluid p-0">
        <!-- Sidebar -->
        <div class="col-md-2 sidebar">
            <div class="text-center mb-4"><h3 class="text-white">🥖 La Vicky</h3></div>
            <a href="index.php"><i class="fas fa-home me-2"></i> Dashboard</a>
            <a href="inventario.php"><i class="fas fa-box me-2"></i> Inventario</a>
            <a href="productos.php"><i class="fas fa-bread-slice me-2"></i> Productos</a>
            <a href="produccion_manual.php"><i class="fas fa-industry me-2"></i> Prod. Manual</a>
            <a href="pedidos.php"><i class="fas fa-shopping-cart me-2"></i> Pedidos</a>
            <a href="ventas.php"><i class="fas fa-chart-line me-2"></i> Ventas</a>
            <a href="clientes.php"><i class="fas fa-users me-2"></i> Clientes</a>
            <a href="reportes.php" class="active"><i class="fas fa-file-alt me-2"></i> Reportes</a>
            <a href="configuracion.php"><i class="fas fa-cog me-2"></i> Configuración</a>
        </div>

        <!-- Top Navbar -->
        <div class="top-navbar">
            <h4 class="m-0">Reportes y Estadísticas</h4>
            <div>
                <span class="me-3"><i class="fas fa-user-circle"></i> Administrador</span>
                <a href="#" onclick="logout()" class="btn btn-outline-danger btn-sm">Salir</a>
            </div>
        </div>

        <div class="main-content">

            <!-- ===== FILA 1: Tarjetas de resumen ===== -->
            <div class="row g-3 mb-4">
                <!-- Ventas Semanales -->
                <div class="col-md-3">
                    <div class="card stat-card shadow-sm h-100" style="background: linear-gradient(135deg,#0d6efd,#3a86ff); color:#fff;">
                        <div class="card-body">
                            <p class="stat-label mb-1"><i class="fas fa-calendar-week me-1"></i> Ventas esta semana</p>
                            <div class="stat-value" id="ventasSemanalesVal">—</div>
                            <hr class="my-2 border-white opacity-25">
                            <p class="stat-label mb-0">Ganancia: <strong id="gananciaSemanalesVal">—</strong></p>
                        </div>
                    </div>
                </div>
                <!-- Ventas Mensuales -->
                <div class="col-md-3">
                    <div class="card stat-card shadow-sm h-100" style="background: linear-gradient(135deg,#198754,#34d399); color:#fff;">
                        <div class="card-body">
                            <p class="stat-label mb-1"><i class="fas fa-calendar-alt me-1"></i> Ventas este mes</p>
                            <div class="stat-value" id="ventasMensualesVal">—</div>
                            <hr class="my-2 border-white opacity-25">
                            <p class="stat-label mb-0">Ganancia: <strong id="gananciaMensualVal">—</strong></p>
                        </div>
                    </div>
                </div>
                <!-- Ganancias netas (suma de semana) -->
                <div class="col-md-3">
                    <div class="card stat-card shadow-sm h-100" style="background: linear-gradient(135deg,#fd7e14,#ffc107); color:#fff;">
                        <div class="card-body">
                            <p class="stat-label mb-1"><i class="fas fa-coins me-1"></i> Ganancia semanal neta</p>
                            <div class="stat-value" id="gananciaSemanaNetaVal">—</div>
                            <p class="stat-label mt-2 mb-0">total_venta − costos</p>
                        </div>
                    </div>
                </div>
                <!-- Ganancia mensual neta -->
                <div class="col-md-3">
                    <div class="card stat-card shadow-sm h-100" style="background: linear-gradient(135deg,#6f42c1,#a855f7); color:#fff;">
                        <div class="card-body">
                            <p class="stat-label mb-1"><i class="fas fa-piggy-bank me-1"></i> Ganancia mensual neta</p>
                            <div class="stat-value" id="gananciaMesNetaVal">—</div>
                            <p class="stat-label mt-2 mb-0">total_venta − costos</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== FILA 2: Gráficas existentes ===== -->
            <div class="row g-3 mb-4">
                <div class="col-md-8">
                    <div class="chart-container">
                        <h5>Ingresos de la última semana (USD)</h5>
                        <canvas id="revenueChart" height="100"></canvas>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="chart-container text-center">
                        <h5>Top 5 Productos</h5>
                        <div style="max-height: 280px; display: flex; justify-content: center;">
                            <canvas id="topProductsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== FILA 3: Gastos por día ===== -->
            <div class="row g-3 mb-4">
                <div class="col-md-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center pt-3 pb-2">
                            <h5 class="m-0"><i class="fas fa-receipt text-danger me-2"></i>Gastos por Día</h5>
                            <div class="d-flex gap-2 align-items-center flex-wrap">
                                <!-- Selector de día (semana actual) -->
                                <input type="date" id="fechaGastoFilter" class="form-control form-control-sm" style="width:180px;"
                                    value="<?= date('Y-m-d') ?>">
                                <button class="btn btn-primary btn-sm" onclick="loadGastos()">
                                    <i class="fas fa-search"></i> Ver gastos
                                </button>
                                <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#addGastoModal">
                                    <i class="fas fa-plus"></i> Nuevo Gasto
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Descripción</th>
                                            <th>Monto</th>
                                            <th class="text-end">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody id="gastosTableBody">
                                        <tr><td colspan="4" class="text-center text-muted py-3">Selecciona una fecha para ver los gastos.</td></tr>
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-secondary fw-bold">
                                            <td colspan="2" class="text-end">Total del día:</td>
                                            <td id="totalGastosDia">$0.00</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /main-content -->
    </div>

    <!-- Modal Nuevo Gasto -->
    <div class="modal fade" id="addGastoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addGastoForm">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-receipt"></i> Registrar Gasto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <input type="text" name="descripcion" class="form-control" placeholder="Ej. Compra de harina" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Monto ($)</label>
                                <input type="number" step="0.01" name="monto" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha</label>
                                <input type="date" name="fecha" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Guardar Gasto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const fmt = v => '$' + parseFloat(v || 0).toLocaleString('es-HN', {minimumFractionDigits:2, maximumFractionDigits:2});

        document.addEventListener('DOMContentLoaded', () => {
            loadRevenueChart();
            loadTopProductsChart();
            loadVentasSemanales();
            loadVentasMensuales();
            loadGastos();   // carga gastos del día de hoy por defecto
        });

        /* ---- Tarjetas de resumen ---- */
        async function loadVentasSemanales() {
            const res  = await fetch('../backend/api.php?route=get_ventas_semanales');
            const json = await res.json();
            if (json.success) {
                const d = json.data;
                document.getElementById('ventasSemanalesVal').textContent   = fmt(d.total_ventas);
                document.getElementById('gananciaSemanalesVal').textContent = fmt(d.total_ganancias);
                document.getElementById('gananciaSemanaNetaVal').textContent = fmt(d.total_ganancias);
            }
        }

        async function loadVentasMensuales() {
            const res  = await fetch('../backend/api.php?route=get_ventas_mensuales');
            const json = await res.json();
            if (json.success) {
                const d = json.data;
                document.getElementById('ventasMensualesVal').textContent = fmt(d.total_ventas);
                document.getElementById('gananciaMensualVal').textContent  = fmt(d.total_ganancias);
                document.getElementById('gananciaMesNetaVal').textContent  = fmt(d.total_ganancias);
            }
        }

        /* ---- Gastos por día ---- */
        async function loadGastos() {
            const fecha = document.getElementById('fechaGastoFilter').value;
            if (!fecha) return;
            const res  = await fetch(`../backend/api.php?route=get_gastos_by_date&fecha=${fecha}`);
            const json = await res.json();
            const tbody = document.getElementById('gastosTableBody');
            tbody.innerHTML = '';
            let total = 0;
            if (json.success && json.data.length > 0) {
                json.data.forEach(g => {
                    total += parseFloat(g.monto);
                    tbody.innerHTML += `
                        <tr>
                            <td>${new Date(g.fecha).toLocaleDateString('es-HN')}</td>
                            <td>${g.descripcion}</td>
                            <td>${fmt(g.monto)}</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteGasto(${g.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>`;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">No hay gastos registrados para esta fecha.</td></tr>';
            }
            document.getElementById('totalGastosDia').textContent = fmt(total);
        }

        document.getElementById('addGastoForm').addEventListener('submit', async e => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const res = await fetch('../backend/api.php?route=add_gasto', { method: 'POST', body: formData });
            const json = await res.json();
            if (json.success) {
                bootstrap.Modal.getInstance(document.getElementById('addGastoModal')).hide();
                e.target.reset();
                loadGastos();
            } else {
                alert('Error: ' + json.message);
            }
        });

        async function deleteGasto(id) {
            if (!confirm('¿Eliminar este gasto?')) return;
            const fd = new FormData();
            fd.append('id', id);
            const res  = await fetch('../backend/api.php?route=delete_gasto', { method: 'POST', body: fd });
            const json = await res.json();
            if (json.success) loadGastos();
            else alert(json.message);
        }

        /* ---- Gráficas ---- */
        async function loadRevenueChart() {
            const res  = await fetch('../backend/api.php?route=get_revenue_chart');
            const json = await res.json();
            if (json.success) {
                const ctx = document.getElementById('revenueChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: json.data.map(d => d.fecha),
                        datasets: [{
                            label: 'Ventas ($)',
                            data: json.data.map(d => d.total_dia),
                            borderColor: '#0d6efd',
                            tension: 0.3,
                            fill: true,
                            backgroundColor: 'rgba(13,110,253,0.1)'
                        }]
                    }
                });
            }
        }

        async function loadTopProductsChart() {
            const res  = await fetch('../backend/api.php?route=get_top_products');
            const json = await res.json();
            if (json.success) {
                const ctx = document.getElementById('topProductsChart').getContext('2d');
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: json.data.map(d => d.nombre),
                        datasets: [{
                            data: json.data.map(d => d.total_vendido),
                            backgroundColor: ['#FF6384','#36A2EB','#FFCE56','#4BC0C0','#9966FF']
                        }]
                    }
                });
            }
        }

        function logout() {
            fetch('../backend/api.php?route=logout').then(() => window.location.href = 'login.html');
        }
    </script>
</body>
</html>
