<?php
// frontend/factura.php  —  Comprobante de venta imprimible (no requiere layout de sidebar)
session_start();
if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$venta_id = intval($_GET['id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura #<?php echo $venta_id; ?> - La Vicky</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; font-family: 'Courier New', Courier, monospace; }
        .invoice-wrapper { padding: 30px 0; }
        .invoice-card {
            max-width: 420px;
            margin: 0 auto;
            background: #fff;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
            border-radius: 8px;
            border-top: 4px solid #c0560f;
        }
        .invoice-header { text-align: center; border-bottom: 1px dashed #000; padding-bottom: 12px; margin-bottom: 12px; }
        .invoice-header h3 { font-size: 1.4rem; font-weight: 700; letter-spacing: 2px; }
        .invoice-footer { text-align: center; margin-top: 20px; font-size: 12px; color: #555; border-top: 1px dashed #000; padding-top: 12px; }
        .table-receipt { width: 100%; font-size: 13px; }
        .table-receipt th { text-align: left; border-bottom: 1px dashed #ccc; padding-bottom: 4px; margin-bottom: 4px; }
        .table-receipt td { padding: 3px 0; }
        .no-print-actions { text-align: center; margin-bottom: 20px; }
        .no-print-actions .btn { margin: 0 4px; }

        @media print {
            .no-print { display: none !important; }
            body { background: #fff; }
            .invoice-card {
                box-shadow: none;
                border: none;
                border-top: none;
                border-radius: 0;
                max-width: 100%;
                margin: 0;
                padding: 10px;
            }
            .invoice-wrapper { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="invoice-wrapper">
        <div class="no-print no-print-actions">
            <button onclick="window.print()" class="btn btn-primary shadow-sm">
                <i class="fas fa-print me-1"></i> Imprimir Comprobante
            </button>
            <button onclick="window.close()" class="btn btn-outline-secondary">
                <i class="fas fa-times me-1"></i> Cerrar
            </button>
        </div>

        <div class="invoice-card" id="invoiceContent">
            <div class="invoice-header">
                <h3 id="empresaNombre">🥖 LA VICKY</h3>
                <p id="empresaInfo" class="small mb-0">Panadería &amp; Pastelería<br>Dir: Av. Principal calle 5<br>Tel: 1234-5678</p>
            </div>

            <div id="loading" class="text-center py-3 text-muted">
                <div class="spinner-border spinner-border-sm me-1"></div> Cargando datos...
            </div>

            <div id="invoiceData" style="display:none;">
                <p class="small mb-1">
                    <strong>Factura:</strong> #<span id="factura_id"></span><br>
                    <strong>Fecha:</strong> <span id="fecha_venta"></span><br>
                    <strong>Vendedor:</strong> <span id="vendedor"></span><br>
                    <strong>Cliente:</strong> <span id="cliente"></span>
                </p>
                <hr class="my-2">
                <table class="table-receipt">
                    <thead>
                        <tr><th>CANT</th><th>PRODUCTO</th><th class="text-end">TOTAL</th></tr>
                    </thead>
                    <tbody id="detallesBody"></tbody>
                </table>
                <hr class="my-2">
                <div class="text-end small">
                    <div>Subtotal: $<span id="subtotalDisplay"></span></div>
                    <div>Impuestos: $<span id="impuestosDisplay"></span></div>
                    <div>Descuento: -$<span id="descuentoDisplay"></span></div>
                    <div class="fw-bold mt-1" style="font-size: 1rem;">TOTAL: $<span id="totalVenta"></span></div>
                </div>
                <hr class="my-2">
                <h6 class="fw-bold mb-1">PAGOS:</h6>
                <div id="pagosBody" class="small"></div>
            </div>

            <div class="invoice-footer">
                <p class="mb-0">Estado: <span id="estadoVenta" class="fw-bold"></span></p>
                <p class="mt-2">¡Gracias por su compra!<br>Vuelva pronto. 😊</p>
            </div>
        </div>
    </div>

    <script>
        function escapeHtml(s) {
            return String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        }

        async function api(route, params = {}) {
            const qs = new URLSearchParams({ route, ...params });
            const res = await fetch(`../backend/api.php?${qs}`);
            return res.json();
        }

        document.addEventListener('DOMContentLoaded', async () => {
            const urlParams = new URLSearchParams(window.location.search);
            const id = urlParams.get('id');
            if (!id) return;

            // Load company profile for invoice header
            try {
                const emp = await api('get_datos_empresa');
                if (emp.success && emp.data) {
                    const e = emp.data;
                    document.getElementById('empresaNombre').textContent = '🥖 ' + (e.nombre || 'LA VICKY');
                    let info = e.descripcion ? escapeHtml(e.descripcion) + '<br>' : '';
                    if (e.ruc) info += 'RUC: ' + escapeHtml(e.ruc) + '<br>';
                    if (e.direccion) info += 'Dir: ' + escapeHtml(e.direccion) + '<br>';
                    if (e.telefono) info += 'Tel: ' + escapeHtml(e.telefono);
                    document.getElementById('empresaInfo').innerHTML = info;
                }
            } catch (err) { /* Use default header */ }

            try {
                const json = await api('get_venta_detalles', { id });

                if (json.success && json.data) {
                    const v = json.data;
                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('invoiceData').style.display = 'block';

                    document.getElementById('factura_id').textContent = v.id;
                    document.getElementById('fecha_venta').textContent = v.fecha_venta;
                    document.getElementById('vendedor').textContent = v.vendedor || 'Admin';
                    document.getElementById('cliente').textContent = v.cliente_nombre || 'Consumidor Final';

                    document.getElementById('subtotalDisplay').textContent = parseFloat(v.subtotal || 0).toFixed(2);
                    document.getElementById('impuestosDisplay').textContent = parseFloat(v.impuestos || 0).toFixed(2);
                    document.getElementById('descuentoDisplay').textContent = parseFloat(v.descuento || 0).toFixed(2);
                    document.getElementById('totalVenta').textContent = parseFloat(v.total || 0).toFixed(2);
                    document.getElementById('estadoVenta').textContent = (v.estado || '').toUpperCase();

                    const body = document.getElementById('detallesBody');
                    (v.detalles || []).forEach(d => {
                        const descSuffix = Number(d.descuento) > 0
                            ? `<br><small>Desc: -$${escapeHtml(d.descuento)}</small>`
                            : '';
                        body.innerHTML += `
                            <tr>
                                <td>${escapeHtml(d.cantidad)}</td>
                                <td>${escapeHtml(d.producto_nombre)}${descSuffix}</td>
                                <td class="text-end">$${parseFloat(d.subtotal || 0).toFixed(2)}</td>
                            </tr>
                        `;
                    });

                    const pBody = document.getElementById('pagosBody');
                    if (v.pagos && v.pagos.length > 0) {
                        v.pagos.forEach(p => {
                            pBody.innerHTML += `
                                <div class="d-flex justify-content-between">
                                    <span>${escapeHtml((p.metodo_pago || '').toUpperCase())}</span>
                                    <span>$${parseFloat(p.monto || 0).toFixed(2)}</span>
                                </div>
                            `;
                        });
                    } else {
                        // Fallback for old sales
                        pBody.innerHTML = `
                            <div class="d-flex justify-content-between">
                                <span>EFECTIVO</span>
                                <span>$${parseFloat(v.total || 0).toFixed(2)}</span>
                            </div>
                        `;
                    }
                } else {
                    document.getElementById('loading').innerHTML = '<p class="text-danger">No se encontró la venta solicitada.</p>';
                }
            } catch (e) {
                document.getElementById('loading').innerHTML = '<p class="text-danger">Error al cargar la factura.</p>';
                console.error(e);
            }
        });
    </script>
</body>
</html>
