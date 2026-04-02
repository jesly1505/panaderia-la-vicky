console.log('Sistema funcionando');

// Funciones del Dashboard
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('ventas-hoy')) {
        fetchDashboardStats();
        setInterval(fetchDashboardStats, 5000); // Polling cada 5s
    }
});

function fetchDashboardStats() {
    fetch('../backend/api.php?route=get_dashboard_resumen')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Stats Card
                document.getElementById('ventas-hoy').textContent = '$' + parseFloat(data.data.ventas_hoy).toFixed(2);
                document.getElementById('pedidos-pendientes').textContent = data.data.pedidos_pendientes;
                document.getElementById('productos-catalogo').textContent = data.data.productos_catalogo;
                document.getElementById('clientes-registrados').textContent = data.data.clientes_registrados;

                // Últimos Pedidos
                const pedidosBody = document.getElementById('ultimos-pedidos-body');
                if (pedidosBody) {
                    pedidosBody.innerHTML = '';
                    if (data.data.ultimos_pedidos.length > 0) {
                        data.data.ultimos_pedidos.forEach(p => {
                            let badgeClass = 'bg-secondary';
                            if (p.estado === 'en_proceso') badgeClass = 'bg-warning text-dark';
                            if (p.estado === 'entregado') badgeClass = 'bg-success';
                            if (p.estado === 'cancelado') badgeClass = 'bg-danger';

                            pedidosBody.innerHTML += `
                                <tr>
                                    <td>#${p.id}</td>
                                    <td>${p.cliente_nombre || 'Consumidor Final'}</td>
                                    <td>${p.fecha_pedido.split(' ')[0]}</td>
                                    <td><span class="badge ${badgeClass}">${p.estado}</span></td>
                                    <td class="fw-bold">$${p.total}</td>
                                </tr>
                            `;
                        });
                    } else {
                        pedidosBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No hay pedidos registrados</td></tr>';
                    }
                }

                // Alertas de Stock
                const alertsContainer = document.getElementById('alertas-stock-container');
                if (alertsContainer) {
                    alertsContainer.innerHTML = '';
                    if (data.data.alertas_stock.length > 0) {
                        data.data.alertas_stock.forEach(a => {
                            alertsContainer.innerHTML += `
                                <div class="alert alert-danger mb-2 p-2 small">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    <strong>${a.tipo.toUpperCase()}:</strong> ${a.nombre} 
                                    <span class="badge bg-danger float-end">${a.stock}</span>
                                </div>
                            `;
                        });
                    } else {
                        alertsContainer.innerHTML = `
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> Todos los niveles de stock son óptimos.
                            </div>
                        `;
                    }
                }
            } else {
                console.error('Error al obtener estadísticas del dashboard', data.message);
            }
        })
        .catch(error => console.error('Error en la petición de dashboard:', error));
}