<?php
session_start();
require_once __DIR__ . '/includes/permisos.php';
if (!isset($_SESSION['usuario'])) {
    header("Location: login.html");
    exit();
}
if (!tiene_permiso('productos.ver')) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - La Vicky</title>
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
        .product-card { border: none; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,.05); transition: .25s; }
        .product-card:hover { transform: translateY(-4px); box-shadow: 0 8px 16px rgba(0,0,0,.1); }
        .category-badge { font-size: .7rem; letter-spacing: .04em; text-transform: uppercase; }
        .filter-btn { border-radius: 20px; padding: .3rem 1rem; font-size: .85rem; transition: .2s; }
        .filter-btn.active { background-color: #685569; color: #fff; border-color: #685569; }
        .product-icon { font-size: 2.5rem; }
    </style>
</head>

<body>
    <div class="container-fluid p-0">
        <?php $active = 'productos'; $titulo = 'Gestión de Productos'; include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Cabecera y botón nuevo -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-muted m-0">Catálogo de Productos al Público</h5>
                <?php if (tiene_permiso('productos.gestionar')): ?>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductoModal">
                    <i class="fas fa-plus"></i> Añadir Producto
                </button>
                <?php endif; ?>
            </div>

            <!-- ===== FILTROS POR CATEGORÍA ===== -->
            <div class="mb-4 d-flex flex-wrap gap-2" id="categoryFilters">
                <button class="btn btn-outline-secondary filter-btn active" onclick="filterCategoria('', this)">
                    <i class="fas fa-th-large me-1"></i> Todos
                </button>
                <button class="btn btn-outline-warning filter-btn" onclick="filterCategoria('Pan Dulce', this)">
                    🍞 Pan Dulce
                </button>
                <button class="btn btn-outline-info filter-btn" onclick="filterCategoria('Pan Salado', this)">
                    🥖 Pan Salado
                </button>
                <button class="btn btn-outline-danger filter-btn" onclick="filterCategoria('Pastelería', this)">
                    🎂 Pastelería
                </button>
                <button class="btn btn-outline-success filter-btn" onclick="filterCategoria('Bebidas', this)">
                    ☕ Bebidas
                </button>
            </div>

            <!-- Contador de resultados -->
            <p class="text-muted small mb-3" id="productCount">Cargando...</p>

            <!-- Grid de productos -->
            <div class="row g-3" id="productosCatalog">
                <div class="col-12 text-center text-muted py-5">
                    <i class="fas fa-spinner fa-spin fa-2x mb-2"></i><br>Cargando productos...
                </div>
            </div>
        </div>
    </div>

    <!-- ===== MODAL NUEVO PRODUCTO ===== -->
    <div class="modal fade" id="addProductoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="addProductoForm">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-bread-slice me-2"></i>Registrar Nuevo Producto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre del Producto <span class="text-danger">*</span></label>
                                <input type="text" name="nombre" class="form-control" required placeholder="Ej. Pan Francés">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Categoría <span class="text-danger">*</span></label>
                                <select name="categoria" class="form-select" required>
                                    <option value="Pan Dulce">🍞 Pan Dulce</option>
                                    <option value="Pan Salado">🥖 Pan Salado</option>
                                    <option value="Pastelería">🎂 Pastelería</option>
                                    <option value="Bebidas">☕ Bebidas</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="2" placeholder="Descripción corta del producto..."></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Precio de Venta ($) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="precio_venta" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Cantidad Inicial (Stock)</label>
                                <input type="number" name="cantidad" class="form-control" value="0" min="0">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Stock Mínimo (Alerta)</label>
                                <input type="number" name="stock_minimo" class="form-control" value="0" min="0">
                            </div>
                        </div>
                        <hr>
                        <h6 class="mb-2"><i class="fas fa-list-ul me-1 text-secondary"></i>Receta / Insumos Requeridos</h6>
                        <p class="text-muted small mb-2">Si tienes stock inicial, los insumos se descontarán automáticamente del inventario.</p>
                        <div id="ingredientesList"></div>
                        <button type="button" class="btn btn-sm btn-outline-success w-100 mt-2" onclick="addIngredienteRow()">
                            <i class="fas fa-plus"></i> Añadir Insumo a Receta
                        </button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Guardar Producto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ===== MODAL PRODUCIR PRODUCTO ===== -->
    <div class="modal fade" id="producirModal" tabindex="-1" aria-labelledby="producirModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="producirModalLabel">
                        <i class="fas fa-industry me-2 text-success"></i>Producir Producto
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Alerta de resultado (éxito o error) -->
                    <div id="producirAlert" class="alert d-none mb-3"></div>

                    <p class="mb-1 text-muted small">Producto:</p>
                    <p class="fw-bold mb-3" id="producirNombreLabel">—</p>

                    <label for="producirCantidad" class="form-label">Cantidad a producir <span class="text-danger">*</span></label>
                    <input type="number" id="producirCantidad" class="form-control"
                           min="1" step="1" placeholder="Ej. 50">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="btnConfirmarProduccion"
                            onclick="confirmarProduccion()">
                        <i class="fas fa-check me-1"></i>Confirmar Producción
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/common.js"></script>
    <script>
        // ---- Icons per category ----
        const catIcons = {
            'Pan Dulce':  { icon: '🍞', color: 'warning' },
            'Pan Salado': { icon: '🥖', color: 'info'    },
            'Pastelería': { icon: '🎂', color: 'danger'  },
            'Bebidas':    { icon: '☕', color: 'success'  }
        };

        let insumosGlob    = [];
        let activeCategory = '';   // '' = todos

        document.addEventListener('DOMContentLoaded', () => {
            loadProductos('');
            loadInsumosDropdown();
        });

        // ---- Load insumos for recipe builder ----
        async function loadInsumosDropdown() {
            try {
                const res  = await fetch('../backend/api.php?route=get_insumos');
                const json = await res.json();
                if (json.success) insumosGlob = json.data;
            } catch (e) {}
        }

        // ---- Category filter button click ----
        function filterCategoria(cat, btn) {
            activeCategory = cat;
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            loadProductos(cat);
        }

        // ---- Load / render products ----
        async function loadProductos(categoria = '') {
            const catalog = document.getElementById('productosCatalog');
            catalog.innerHTML = `<div class="col-12 text-center text-muted py-5">
                <i class="fas fa-spinner fa-spin fa-2x mb-2"></i><br>Cargando...</div>`;

            const route = categoria
                ? `get_productos_by_categoria&categoria=${encodeURIComponent(categoria)}`
                : 'get_productos';

            try {
                const res  = await fetch(`../backend/api.php?route=${route}`);
                const data = await res.json();
                catalog.innerHTML = '';

                if (data.success && data.data.length > 0) {
                    document.getElementById('productCount').textContent =
                        `Mostrando ${data.data.length} producto${data.data.length !== 1 ? 's' : ''}`;

                    data.data.forEach(prod => {
                        const ci    = catIcons[prod.categoria] || { icon: '📦', color: 'secondary' };
                        const isLow = parseFloat(prod.stock_actual) <= parseFloat(prod.stock_minimo || 0);

                        catalog.innerHTML += `
                        <div class="col-sm-6 col-md-4 col-lg-3">
                            <div class="card product-card h-100">
                                <div class="card-body d-flex flex-column text-center p-3">
                                    <div class="product-icon mb-2">${ci.icon}</div>
                                    <span class="badge bg-${ci.color} category-badge mb-2">${prod.categoria}</span>
                                    <h6 class="card-title fw-bold mb-1">${prod.nombre}</h6>
                                    <p class="text-muted small flex-grow-1 mb-2">${prod.descripcion || 'Sin descripción'}</p>
                                    <h5 class="text-success mb-1">$${parseFloat(prod.precio_venta).toFixed(2)}</h5>
                                    <p class="mb-2 small ${isLow ? 'text-danger fw-bold' : 'text-muted'}">
                                        <i class="fas fa-boxes me-1"></i>Stock: ${prod.stock_actual}
                                        ${isLow ? ' <span class="badge bg-danger ms-1">Bajo</span>' : ''}
                                    </p>
                                    <div class="d-flex gap-2 mt-auto">
                                        ${tienePermiso('productos.gestionar') ? `
                                        <button class="btn btn-outline-success btn-sm flex-grow-1"
                                            onclick="abrirProducir(${prod.id}, '${prod.nombre.replace(/'/g,"\\'")}')"
                                            title="Producir unidades de este producto">
                                            <i class="fas fa-industry me-1"></i>Producir
                                        </button>` : ''}
                                        ${tienePermiso('productos.eliminar') ? `
                                        <button class="btn btn-outline-danger btn-sm"
                                            onclick="deleteProducto(${prod.id}, '${prod.nombre.replace(/'/g,"\\'")}')">
                                            <i class="fas fa-trash"></i>
                                        </button>` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>`;
                    });
                } else {
                    const label = categoria || 'el catálogo';
                    document.getElementById('productCount').textContent = '0 productos';
                    catalog.innerHTML = `
                        <div class="col-12 text-center py-5 text-muted">
                            <i class="fas fa-bread-slice fa-3x mb-3 opacity-25"></i>
                            <p class="fs-5">No hay productos en <strong>${label}</strong>.</p>
                            ${tienePermiso('productos.gestionar') ? `
                            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addProductoModal">
                                <i class="fas fa-plus me-1"></i>Añadir el primero
                            </button>` : ''}
                        </div>`;
                }
            } catch (e) {
                catalog.innerHTML = `<div class="col-12 text-center text-danger py-4">Error al cargar productos.</div>`;
            }
        }

        // ---- Recipe ingredient row ----
        function addIngredienteRow() {
            const list = document.getElementById('ingredientesList');
            const row  = document.createElement('div');
            row.className = 'row mb-2 ingrediente-row g-2 align-items-center';
            let opts = '<option value="">Seleccione insumo...</option>';
            insumosGlob.forEach(ins => opts += `<option value="${ins.id}">${ins.nombre} (${ins.unidad_medida})</option>`);
            row.innerHTML = `
                <div class="col-5">
                    <select class="form-select form-select-sm insumo-select" required>${opts}</select>
                </div>
                <div class="col-3">
                    <input type="number" step="0.01" class="form-control form-control-sm insumo-cant"
                           placeholder="Cant. a usar" required>
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
                    <button type="button" class="btn btn-outline-danger btn-sm"
                        onclick="this.closest('.ingrediente-row').remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>`;
            list.appendChild(row);
        }

        // ---- Submit new product ----
        document.getElementById('addProductoForm').addEventListener('submit', async e => {
            e.preventDefault();
            const formObj = {
                nombre:       e.target.nombre.value,
                categoria:    e.target.categoria.value,
                descripcion:  e.target.descripcion.value,
                precio_venta: e.target.precio_venta.value,
                cantidad:     e.target.cantidad.value,
                stock_minimo: e.target.stock_minimo.value,
                ingredientes: []
            };

            document.querySelectorAll('.ingrediente-row').forEach(row => {
                const id  = row.querySelector('.insumo-select').value;
                const qty = row.querySelector('.insumo-cant').value;
                const unit = row.querySelector('.insumo-unidad').value;
                if (id && qty) formObj.ingredientes.push({ 
                    insumo_id: id, 
                    cantidad_requerida: qty,
                    unidad_usada: unit
                });
            });

            try {
                const res  = await fetch('../backend/api.php?route=add_producto', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formObj)
                });
                const json = await res.json();
                if (json.success) {
                    bootstrap.Modal.getInstance(document.getElementById('addProductoModal')).hide();
                    e.target.reset();
                    document.getElementById('ingredientesList').innerHTML = '';
                    loadProductos(activeCategory);
                } else {
                    alert('Error: ' + json.message);
                }
            } catch (err) {
                alert('Error del servidor: ' + err.message);
            }
        });

        // ---- Delete product ----
        async function deleteProducto(id, nombre) {
            if (!confirm(`¿Eliminar el producto "${nombre}"?\nTambién se eliminará su receta asignada.`)) return;
            const fd = new FormData();
            fd.append('id', id);
            try {
                const res  = await fetch('../backend/api.php?route=delete_producto', { method: 'POST', body: fd });
                const json = await res.json();
                if (json.success) {
                    loadProductos(activeCategory);
                } else {
                    alert('Error: ' + json.message);
                }
            } catch (err) {
                alert('Error del servidor.');
            }
        }

        // =====================================================================
        // PRODUCCIÓN DE PRODUCTO
        // =====================================================================

        /** ID del producto actualmente seleccionado para producir */
        let producirProductoId = null;

        /**
         * Abre el modal de producción y guarda el ID del producto.
         * @param {number} id     - ID del producto
         * @param {string} nombre - Nombre legible del producto
         */
        function abrirProducir(id, nombre) {
            producirProductoId = id;
            document.getElementById('producirNombreLabel').textContent = nombre;
            document.getElementById('producirCantidad').value = '';
            // Ocultar alerta previa
            const alert = document.getElementById('producirAlert');
            alert.classList.add('d-none');
            alert.textContent = '';
            // Habilitar botón de confirmación
            document.getElementById('btnConfirmarProduccion').disabled = false;

            new bootstrap.Modal(document.getElementById('producirModal')).show();
        }

        /**
         * Envía la solicitud de producción a la API.
         * Muestra el resultado directamente en el modal sin cerrar la ventana
         * en caso de error, para que el usuario vea el detalle de insumos faltantes.
         */
        async function confirmarProduccion() {
            const cantidad = parseFloat(document.getElementById('producirCantidad').value);
            const alertEl  = document.getElementById('producirAlert');

            // Validar cantidad
            if (!cantidad || cantidad <= 0) {
                mostrarAlertaProducir('warning', 'Ingresa una cantidad válida mayor a cero.');
                return;
            }

            // Deshabilitar botón mientras espera respuesta
            document.getElementById('btnConfirmarProduccion').disabled = true;

            try {
                const res  = await fetch('../backend/api.php?route=producir_producto', {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body:    JSON.stringify({ producto_id: producirProductoId, cantidad })
                });
                const json = await res.json();

                if (json.success) {
                    // Éxito: actualizar el catálogo y cerrar el modal con un pequeño retardo
                    mostrarAlertaProducir('success', json.message);
                    loadProductos(activeCategory);
                    setTimeout(() => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('producirModal'));
                        if (modal) modal.hide();
                    }, 1800);
                } else {
                    // Error: mostrar detalle sin cerrar el modal
                    let msg = json.message;
                    if (json.faltantes && json.faltantes.length > 0) {
                        msg += '<ul class="mt-2 mb-0">';
                        json.faltantes.forEach(f => { msg += `<li>${f}</li>`; });
                        msg += '</ul>';
                    }
                    mostrarAlertaProducir('danger', msg, true);
                    document.getElementById('btnConfirmarProduccion').disabled = false;
                }
            } catch (err) {
                mostrarAlertaProducir('danger', 'Error de conexión: ' + err.message);
                document.getElementById('btnConfirmarProduccion').disabled = false;
            }
        }

        /**
         * Muestra una alerta Bootstrap dentro del modal de producción.
         * @param {string}  tipo - 'success'|'danger'|'warning'
         * @param {string}  html - Contenido HTML del mensaje
         * @param {boolean} esHTML - Si true, usa innerHTML; si false, textContent
         */
        function mostrarAlertaProducir(tipo, html, esHTML = false) {
            const el = document.getElementById('producirAlert');
            el.className = `alert alert-${tipo}`;
            if (esHTML) el.innerHTML = html;
            else        el.textContent = html;
        }

        // =====================================================================

        function logout() {
            fetch('../backend/api.php?route=logout').then(() => window.location.href = 'login.html');
        }
    </script>
</body>

</html>
