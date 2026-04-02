<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.html");
    exit();
}
$venta_id = $_GET['id'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura #<?php echo $venta_id; ?> - La Vicky</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; font-family: 'Courier New', Courier, monospace; }
        .invoice-card { max-width: 400px; margin: 20px auto; background: #fff; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); border: 1px dashed #ccc; }
        .header { text-align: center; border-bottom: 1px dashed #000; padding-bottom: 10px; margin-bottom: 10px; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; }
        .table-receipt { width: 100%; font-size: 14px; }
        .table-receipt th { text-align: left; }
        .table-receipt td { padding: 2px 0; }
        @media print {
            .no-print { display: none; }
            body { background: #fff; }
            .invoice-card { box-shadow: none; border: none; margin: 0 auto; }
        }
    </style>
</head>
<body>
    <div class="no-print text-center mt-3">
        <button onclick="window.print()" class="btn btn-primary">Imprimir Comprobante</button>
        <button onclick="window.close()" class="btn btn-secondary">Cerrar</button>
    </div>

    <div class="invoice-card" id="invoiceContent">
        <div class="header">
            <h3>🥖 LA VICKY</h3>
            <p>Panadería & Pastelería<br>Dir: Av. Principal calle 5<br>Tel: 1234-5678</p>
        </div>
        <div id="loading" class="text-center">Cargando datos...</div>
        <div id="invoiceData" style="display:none;">
            <p class="small">
                <strong>Factura:</strong> #<span id="factura_id"></span><br>
                <strong>Fecha:</strong> <span id="fecha_venta"></span><br>
                <strong>Vendedor:</strong> <span id="vendedor"></span><br>
                <strong>Cliente:</strong> <span id="cliente"></span>
            </p>
            <hr>
            <table class="table-receipt">
                <thead>
                    <tr><th>CANT</th><th>PRODUCTO</th><th class="text-end">TOTAL</th></tr>
                </thead>
                <tbody id="detallesBody"></tbody>
            </table>
            <hr>
            <div class="text-end">
                <p class="mb-1">Subtotal: $<span id="subtotalDisplay"></span></p>
                <p class="mb-1">Impuestos: $<span id="impuestosDisplay"></span></p>
                <p class="mb-1">Descuento: -$<span id="descuentoDisplay"></span></p>
                <p class="mb-2"><strong>TOTAL: $<span id="totalVenta"></span></strong></p>
            </div>
            <hr>
            <h6>PAGOS:</h6>
            <div id="pagosBody" class="small"></div>
        </div>
        <div class="footer">
            <p>Estado: <span id="estadoVenta" class="fw-bold"></span></p>
            <p>¡Gracias por su compra!<br>Vuelva pronto.</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const urlParams = new URLSearchParams(window.location.search);
            const id = urlParams.get('id');
            if(!id) return;

            const res = await fetch(`../backend/api.php?route=get_venta_detalles&id=${id}`);
            const json = await res.json();
            
            if(json.success && json.data) {
                const v = json.data;
                document.getElementById('loading').style.display = 'none';
                document.getElementById('invoiceData').style.display = 'block';
                
                document.getElementById('factura_id').textContent = v.id;
                document.getElementById('fecha_venta').textContent = v.fecha_venta;
                document.getElementById('vendedor').textContent = v.vendedor || 'Admin';
                document.getElementById('cliente').textContent = v.cliente_nombre || 'Consumidor Final';
                
                document.getElementById('subtotalDisplay').textContent = v.subtotal;
                document.getElementById('impuestosDisplay').textContent = v.impuestos;
                document.getElementById('descuentoDisplay').textContent = v.descuento;
                document.getElementById('totalVenta').textContent = v.total;
                document.getElementById('estadoVenta').textContent = v.estado.toUpperCase();

                const body = document.getElementById('detallesBody');
                v.detalles.forEach(d => {
                    let itemTotal = (d.cantidad * d.precio_unitario);
                    body.innerHTML += `
                        <tr>
                            <td>${d.cantidad}</td>
                            <td>${d.producto_nombre} ${d.descuento > 0 ? '<br><small>Desc: -$'+d.descuento+'</small>' : ''}</td>
                            <td class="text-end">$${d.subtotal}</td>
                        </tr>
                    `;
                });

                const pBody = document.getElementById('pagosBody');
                if (v.pagos && v.pagos.length > 0) {
                    v.pagos.forEach(p => {
                        pBody.innerHTML += `<div class="d-flex justify-content-between"><span>${p.metodo_pago.toUpperCase()}</span><span>$${p.monto}</span></div>`;
                    });
                } else {
                    // Fallback for old sales
                    pBody.innerHTML = `<div class="d-flex justify-content-between"><span>Efectivo</span><span>$${v.total}</span></div>`;
                }
            }
        });
    </script>
</body>
</html>
