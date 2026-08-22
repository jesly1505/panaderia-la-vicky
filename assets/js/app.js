// assets/js/app.js
console.log('Sistema La Vicky inicializado');

// Funciones del Dashboard
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('ventas-hoy')) {
        fetchDashboardStats();
        // Polling controlado cada 15 segundos
        setInterval(fetchDashboardStats, 15000);
    }
});

let refrescandoDashboard = false;

async function fetchDashboardStats() {
    if (refrescandoDashboard) return;
    refrescandoDashboard = true;

    // Si hay parámetros de filtro en la URL, pasarlos
    const params = new URLSearchParams(window.location.search);
    const filter = params.get('filter') || '';
    const startDate = params.get('start_date') || '';
    const endDate = params.get('end_date') || '';

    let url = '../backend/api.php?route=get_dashboard_resumen';
    if (filter) url += `&filter=${encodeURIComponent(filter)}`;
    if (startDate) url += `&start_date=${encodeURIComponent(startDate)}`;
    if (endDate) url += `&end_date=${encodeURIComponent(endDate)}`;

    try {
        const response = await fetch(url);
        const data = await response.json();
        if (data.success) {
            // Stats Cards
            if (document.getElementById('ventas-hoy')) {
                document.getElementById('ventas-hoy').textContent = formatCurrency(data.data.ventas_hoy || 0);
            }
            if (document.getElementById('ganancias-hoy')) {
                document.getElementById('ganancias-hoy').textContent = formatCurrency(data.data.ganancias_hoy || 0);
            }
            if (document.getElementById('pedidos-pendientes')) {
                document.getElementById('pedidos-pendientes').textContent = data.data.pedidos_pendientes ?? 0;
            }
            if (document.getElementById('productos-catalogo')) {
                document.getElementById('productos-catalogo').textContent = data.data.productos_catalogo ?? 0;
            }
            if (document.getElementById('clientes-registrados')) {
                document.getElementById('clientes-registrados').textContent = data.data.clientes_registrados ?? 0;
            }

            // KPIs Dinámicos CMMI si existen en el DOM
            if (data.data.kpis_dinamicos) {
                const kpis = data.data.kpis_dinamicos;
                if (document.getElementById('kpi-eventos')) document.getElementById('kpi-eventos').textContent = kpis.eventos ?? 0;
                if (document.getElementById('kpi-usuarios')) document.getElementById('kpi-usuarios').textContent = kpis.usuarios_activos ?? 0;
                if (document.getElementById('kpi-errores')) document.getElementById('kpi-errores').textContent = kpis.errores ?? 0;
                if (document.getElementById('kpi-ventas-qty')) document.getElementById('kpi-ventas-qty').textContent = kpis.ventas_realizadas ?? 0;
                if (document.getElementById('kpi-produccion-qty')) document.getElementById('kpi-produccion-qty').textContent = kpis.produccion_registrada ?? 0;
            }

            // Últimos Pedidos
            const pedidosBody = document.getElementById('ultimos-pedidos-body');
            if (pedidosBody) {
                pedidosBody.innerHTML = '';
                if (data.data.ultimos_pedidos && data.data.ultimos_pedidos.length > 0) {
                    data.data.ultimos_pedidos.forEach((p, index) => {
                        const rowNumber = index + 1;
                        let badgeClass = 'bg-secondary';
                        if (p.estado === 'en_proceso' || p.estado === 'pendiente') badgeClass = 'bg-warning text-dark';
                        if (p.estado === 'entregado' || p.estado === 'completado') badgeClass = 'bg-success';
                        if (p.estado === 'cancelado') badgeClass = 'bg-danger';

                        pedidosBody.innerHTML += `
                            <tr>
                                <td>${rowNumber}</td>
                                <td>${escapeHtml(p.cliente_nombre || 'Consumidor Final')}</td>
                                <td>${p.fecha_pedido ? p.fecha_pedido.split(' ')[0] : '-'}</td>
                                <td><span class="badge ${badgeClass}">${p.estado}</span></td>
                                <td class="fw-bold">${formatCurrency(p.total || 0)}</td>
                            </tr>
                        `;
                    });
                } else {
                    pedidosBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">No hay pedidos registrados en este periodo</td></tr>';
                }
            }

            // Alertas de Stock
            const alertsContainer = document.getElementById('alertas-stock-container');
            if (alertsContainer) {
                alertsContainer.innerHTML = '';
                if (data.data.alertas_stock && data.data.alertas_stock.length > 0) {
                    data.data.alertas_stock.forEach(a => {
                        alertsContainer.innerHTML += `
                            <div class="alert alert-danger mb-2 p-2 small d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    <strong>${a.tipo.toUpperCase()}:</strong> ${escapeHtml(a.nombre)}
                                </div>
                                <span class="badge bg-danger">${a.stock} en stock</span>
                            </div>
                        `;
                    });
                } else {
                    alertsContainer.innerHTML = `
                        <div class="alert alert-success mb-0">
                            <i class="fas fa-check-circle me-1"></i> Todos los niveles de stock son óptimos.
                        </div>
                    `;
                }
            }
        } else {
            console.error('Error al obtener estadísticas del dashboard', data.message);
        }
    } catch (error) {
        console.error('Error en la petición de dashboard:', error);
    } finally {
        refrescandoDashboard = false;
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}