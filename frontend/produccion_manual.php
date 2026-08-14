<?php
session_start();
require_once __DIR__ . '/includes/permisos.php';
if (!isset($_SESSION['usuario'])) {
    header("Location: login.html");
    exit();
}
if (!tiene_permiso('produccion.ver')) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Producción Manual - La Vicky</title>
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
        .form-card { background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,.05); padding: 25px; margin-bottom: 20px;}
        .table-card { background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,.05); padding: 20px;}
        .ingrediente-row { background: #f8f9fa; padding: 10px; border-radius: 8px; margin-bottom: 10px; border: 1px solid #e9ecef;}
    </style>
</head>

<body>
    <div class="container-fluid p-0">
        <?php $active = 'produccion_manual'; $titulo = 'Producción Manual Libre'; include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            
            <!-- RESULT ALERT -->
            <div id="resultAlert" class="alert d-none"></div>

            <div class="row">
                <!-- FORMULARIO DE PRODUCCIÓN -->
                <?php if (tiene_permiso('produccion.gestionar')): ?>
                <div class="col-lg-5 mb-4">
                    <div class="form-card">
                        <h5 class="mb-4"><i class="fas fa-plus-circle text-primary me-2"></i>Registrar Nueva Producción</h5>
                        
                        <form id="produccionForm">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Producto Final Obtenido <span class="text-danger">*</span></label>
                                <select id="selectProducto" class="form-select" required>
                                    <option value="">Seleccione producto...</option>
                                    <!-- Options via JS -->
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Cantidad Producida (Unidades) <span class="text-danger">*</span></label>
                                <input type="number" id="inputCantidadProd" class="form-control" min="1" step="1" required placeholder="Ej. 100">
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold m-0 text-secondary">Insumos Utilizados</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addInsumoRow()">
                                    <i class="fas fa-plus"></i> Añadir Insumo
                                </button>
                            </div>
                            
                            <!-- CONTENEDOR DE INSUMOS -->
                            <div id="insumosContainer" class="mb-4">
                                <!-- Filas dinámicas aquí -->
                            </div>

                            <button type="submit" class="btn btn-success w-100 py-2 fw-bold" id="btnGuardar">
                                <i class="fas fa-save me-2"></i>Guardar Producción
                            </button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <!-- HISTORIAL -->
                <div class="col-lg-<?= tiene_permiso('produccion.gestionar') ? '7' : '12' ?>">
                    <div class="table-card h-100">
                        <h5 class="mb-4"><i class="fas fa-history text-secondary me-2"></i>Historial de Producción</h5>
                        
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Fecha y Hora</th>
                                        <th>Producto</th>
                                        <th class="text-center">Cant.</th>
                                        <th>Insumos Descontados</th>
                                    </tr>
                                </thead>
                                <tbody id="historialBody">
                                    <tr><td colspan='4' class='text-center text-muted'>Cargando historial...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/common.js"></script>
    
    <script>
        let productosList = [];
        let insumosList = [];

        document.addEventListener('DOMContentLoaded', () => {
            loadData();
            loadHistorial();
        });

        // ── 1. Cargar Combos ──────────────────────────────────────────────
        async function loadData() {
            try {
                // Productos
                const resProd = await fetch('../backend/api.php?route=get_productos');
                const jsonProd = await resProd.json();
                if (jsonProd.success) {
                    productosList = jsonProd.data;
                    const sel = document.getElementById('selectProducto');
                    productosList.forEach(p => {
                        sel.innerHTML += `<option value="${p.id}">${p.nombre} - ${p.categoria}</option>`;
                    });
                }

                // Insumos
                const resIns = await fetch('../backend/api.php?route=get_insumos');
                const jsonIns = await resIns.json();
                if (jsonIns.success) {
                    insumosList = jsonIns.data;
                    // Añadir primera fila por defecto
                    addInsumoRow();
                }
            } catch (err) {
                console.error('Error cargando catálogos:', err);
            }
        }

        // ── 2. Fila Dinámica de Insumos ───────────────────────────────────
        function addInsumoRow() {
            const container = document.getElementById('insumosContainer');
            
            let optionsHTML = '<option value="">Seleccione insumo...</option>';
            insumosList.forEach(ins => {
                optionsHTML += `<option value="${ins.id}">${ins.nombre} (${ins.unidad_medida} | Disp: ${ins.stock_actual})</option>`;
            });

            const row = document.createElement('div');
            row.className = 'row g-2 ingrediente-row align-items-center mb-2';
            row.innerHTML = `
                <div class="col-5">
                    <select class="form-select form-select-sm insumo-select" required>
                        ${optionsHTML}
                    </select>
                </div>
                <div class="col-3">
                    <input type="number" step="0.01" min="0.01" class="form-control form-control-sm insumo-cant" placeholder="Total Usado" required>
                </div>
                <div class="col-3">
                    <select class="form-select form-select-sm insumo-unidad" required>
                        <option value="Gramos">Gramos (g)</option>
                        <option value="Kg">Kilogramos (Kg)</option>
                        <option value="Libras">Libras (lb)</option>
                        <option value="Onzas">Onzas (oz)</option>
                        <option value="Mililitros">Mililitros (ml)</option>
                        <option value="Litros">Litros (L)</option>
                        <option value="Unidades">Unidades (Und)</option>
                    </select>
                </div>
                <div class="col-1 text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.ingrediente-row').remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            container.appendChild(row);
        }

        // ── 3. Guardar Producción ─────────────────────────────────────────
        const produccionFormEl = document.getElementById('produccionForm');
        if (produccionFormEl) produccionFormEl.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btnGuardar');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

            // Recolectar datos
            const payload = {
                producto_id: document.getElementById('selectProducto').value,
                cantidad_producida: parseFloat(document.getElementById('inputCantidadProd').value),
                insumos: []
            };

            document.querySelectorAll('.ingrediente-row').forEach(row => {
                const iId = row.querySelector('.insumo-select').value;
                const iCant = row.querySelector('.insumo-cant').value;
                const iUnid = row.querySelector('.insumo-unidad').value;
                if (iId && iCant) {
                    payload.insumos.push({
                        insumo_id: iId,
                        cantidad_usada: parseFloat(iCant),
                        unidad_usada: iUnid
                    });
                }
            });

            try {
                const res = await fetch('../backend/api.php?route=add_produccion_manual', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const json = await res.json();

                if (json.success) {
                    mostrarAlerta('success', json.message);
                    e.target.reset();
                    document.getElementById('insumosContainer').innerHTML = '';
                    addInsumoRow();
                    loadHistorial();
                    
                    // Recargar disp de insumos en fondo para actualizar "Disp: X"
                    const resIns = await fetch('../backend/api.php?route=get_insumos');
                    const jsonIns = await resIns.json();
                    if(jsonIns.success) insumosList = jsonIns.data;

                } else {
                    mostrarAlerta('danger', json.message);
                }
            } catch (error) {
                mostrarAlerta('danger', 'Error de conexión con el servidor.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save me-2"></i>Guardar Producción';
            }
        });

        // ── 4. Historial ──────────────────────────────────────────────────
        async function loadHistorial() {
            const tbody = document.getElementById('historialBody');
            try {
                const res = await fetch('../backend/api.php?route=get_produccion_historial');
                const json = await res.json();
                
                if (json.success && json.data.length > 0) {
                    tbody.innerHTML = '';
                    json.data.forEach(item => {
                        // Formatear detalle
                        let detalleList = '';
                        if (item.detalles_insumos) {
                            const arr = item.detalles_insumos.split('|');
                            detalleList = '<ul class="mb-0 ps-3 small text-muted">';
                            arr.forEach(li => detalleList += `<li>${li.trim()}</li>`);
                            detalleList += '</ul>';
                        } else {
                            detalleList = '<span class="text-muted small">Sin detalle</span>';
                        }
                        
                        // Formato Fecha
                        const f = new Date(item.fecha);
                        const strFecha = f.toLocaleDateString() + ' ' + f.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});

                        tbody.innerHTML += `
                            <tr>
                                <td class="small">${strFecha}</td>
                                <td class="fw-bold text-primary">${item.producto_nombre}</td>
                                <td class="text-center fw-bold fs-5">${parseFloat(item.cantidad_producida)}</td>
                                <td>${detalleList}</td>
                            </tr>
                        `;
                    });
                } else {
                    tbody.innerHTML = `<tr><td colspan='4' class='text-center py-4 text-muted'><i class="fas fa-info-circle mb-2 fa-2x"></i><br>No hay registros de producción manual aún.</td></tr>`;
                }
            } catch (err) {
                tbody.innerHTML = `<tr><td colspan='4' class='text-center text-danger'>Error al cargar el historial.</td></tr>`;
            }
        }

        // ── 5. Alertas Visuales ───────────────────────────────────────────
        function mostrarAlerta(tipo, html) {
            const el = document.getElementById('resultAlert');
            el.className = `alert alert-${tipo} alert-dismissible fade show`;
            el.innerHTML = html + '<button type="button" class="btn-close" onclick="this.parentElement.classList.add(\'d-none\')"></button>';
            el.classList.remove('d-none');
            // Auto hide
            if (tipo === 'success') {
                setTimeout(() => el.classList.add('d-none'), 5000);
            }
        }

        function logout() {
            fetch('../backend/api.php?route=logout').then(() => window.location.href = 'login.html');
        }
    </script>
</body>

</html>
