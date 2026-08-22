<?php
// frontend/productos.php
session_start();
require_once __DIR__ . '/includes/permisos.php';

if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

if (!tiene_permiso('productos.ver')) {
    header("Location: index.php");
    exit();
}

$pageTitle = "Productos";
$pageHeader = "Catálogo y Recetas de Productos";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <?php include 'includes/head.php'; ?>
    <style>
        .product-card {
            border: none;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            overflow: hidden;
        }

        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
        }

        .product-card .card-body {
            padding: 1.5rem;
        }

        .product-icon-wrapper {
            width: 60px;
            height: 60px;
            margin: 0 auto 1rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            background: var(--primary-light);
            color: var(--primary);
        }

        .category-badge {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0.4em 0.8em;
            border-radius: 4px;
        }

        .filter-btn {
            border-radius: 30px;
            padding: 0.5rem 1.25rem;
            font-size: 0.85rem;
            font-weight: 500;
            border: 1px solid #dee2e6;
            color: var(--text-muted);
            background: var(--white);
            transition: var(--transition);
        }

        .filter-btn:hover {
            background: var(--primary-light);
            color: var(--primary);
            border-color: var(--primary);
        }

        .filter-btn.active {
            background: var(--primary);
            color: var(--white);
            border-color: var(--primary);
            box-shadow: 0 4px 10px rgba(192, 86, 15, 0.3);
        }

        .ingrediente-row {
            background: var(--light);
            border-radius: var(--radius-sm);
            padding: 10px;
            margin-bottom: 8px;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <div class="main-content">
            <?php include 'includes/navbar.php'; ?>

            <div class="container-fluid p-4 animate-fade-in">
                <!-- Header Actions -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                    <div>
                        <h5 class="mb-1 fw-bold text-dark">Catálogo de Productos</h5>
                        <p class="text-muted small mb-0" id="productCount">Cargando catálogo...</p>
                    </div>
                    <?php if (tiene_permiso('productos.gestionar')): ?>
                        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addProductoModal">
                            <i class="fas fa-plus me-2"></i>Añadir Producto
                        </button>
                    <?php endif; ?>
                </div>

                <!-- Category Filters -->
                <div class="mb-4 d-flex flex-wrap gap-2" id="categoryFilters">
                    <button class="filter-btn active" onclick="filterCategoria('', this)">
                        <i class="fas fa-th-large me-2"></i>Todos
                    </button>
                    <button class="filter-btn" onclick="filterCategoria('Pan Dulce', this)">
                        <span class="me-2">🍞</span>Pan Dulce
                    </button>
                    <button class="filter-btn" onclick="filterCategoria('Pan Salado', this)">
                        <span class="me-2">🥖</span>Pan Salado
                    </button>
                    <button class="filter-btn" onclick="filterCategoria('Pastelería', this)">
                        <span class="me-2">🎂</span>Pastelería
                    </button>
                    <button class="filter-btn" onclick="filterCategoria('Bebidas', this)">
                        <span class="me-2">☕</span>Bebidas
                    </button>
                </div>

                <!-- Product Grid -->
                <div class="row g-4" id="productosCatalog">
                    <div class="col-12 text-center py-5 text-muted">
                        <div class="spinner-border text-primary mb-3" role="status"></div>
                        <p>Cargando catálogo de productos...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo Producto -->
    <div class="modal fade" id="addProductoModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <form id="addProductoForm">
                    <div class="modal-header bg-dark text-white border-0 p-4">
                        <h5 class="modal-title fw-bold"><i class="fas fa-bread-slice me-2"></i>Registrar Nuevo Producto
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-7 mb-3">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Nombre del
                                    Producto</label>
                                <input type="text" name="nombre" class="form-control py-2" required maxlength="100"
                                    placeholder="Ej. Pan Francés Especial">
                            </div>
                            <div class="col-md-5 mb-3">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Categoría</label>
                                <select name="categoria" class="form-select py-2" required>
                                    <option value="Pan Dulce">🍞 Pan Dulce</option>
                                    <option value="Pan Salado">🥖 Pan Salado</option>
                                    <option value="Pastelería">🎂 Pastelería</option>
                                    <option value="Bebidas">☕ Bebidas</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="2" maxlength="255"
                                placeholder="Breve descripción del producto..."></textarea>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Precio Venta
                                    ($)</label>
                                <input type="number" step="0.01" min="0.01" max="999999.99" name="precio_venta"
                                    class="form-control py-2" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Stock
                                    Inicial</label>
                                <input type="number" step="1" name="cantidad" class="form-control py-2" value="0"
                                    min="0" max="99999">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Stock
                                    Mínimo</label>
                                <input type="number" step="1" name="stock_minimo" class="form-control py-2" value="5"
                                    min="0" max="99999">
                            </div>
                        </div>

                        <div class="mt-4 border rounded p-4 bg-light bg-opacity-50">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0 fw-bold text-dark"><i
                                        class="fas fa-list-ul me-2 text-primary"></i>Receta / Insumos Requeridos</h6>
                                <button type="button" class="btn btn-sm btn-primary" onclick="addIngredienteRow()">
                                    <i class="fas fa-plus me-1"></i>Añadir Insumo
                                </button>
                            </div>
                            <p class="text-muted small mb-3">Defina los insumos necesarios para producir una unidad de
                                este producto.</p>
                            <div id="ingredientesList"></div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-3 bg-light">
                        <button type="button" class="btn btn-link text-muted text-decoration-none"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm fw-bold">Guardar Producto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Producir -->
    <div class="modal fade" id="producirModal" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-success text-white border-0">
                    <h5 class="modal-title fw-bold" id="producirModalLabel"><i
                            class="fas fa-industry me-2"></i>Producción</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="producirForm">
                    <div class="modal-body p-4 text-center">
                        <input type="hidden" id="producirProductoId" name="producto_id">
                        <h6 class="fw-bold mb-3" id="producirProductoNombre">Producto</h6>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-semibold">Cantidad a Hornear / Producir</label>
                            <input type="number" id="producirCantidad" name="cantidad"
                                class="form-control form-control-lg text-center fw-bold" value="10" min="1" max="99999"
                                required>
                        </div>
                        <p class="text-muted small mb-0">Se descontarán automáticamente los insumos de la receta.</p>
                    </div>
                    <div class="modal-footer border-0 p-3 bg-light justify-content-center">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success fw-bold px-4">Producir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Producto -->
    <div class="modal fade" id="editProductoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <form id="editProductoForm">
                    <input type="hidden" name="id">
                    <div class="modal-header bg-dark text-white border-0 p-4">
                        <h5 class="modal-title fw-bold"><i class="fas fa-pen me-2"></i>Editar Producto</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-7 mb-3">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Nombre del
                                    Producto</label>
                                <input type="text" name="nombre" class="form-control py-2" required maxlength="100"
                                    placeholder="Ej. Pan Francés Especial">
                            </div>
                            <div class="col-md-5 mb-3">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Categoría</label>
                                <select name="categoria" class="form-select py-2" required>
                                    <option value="Pan Dulce">🍞 Pan Dulce</option>
                                    <option value="Pan Salado">🥖 Pan Salado</option>
                                    <option value="Pastelería">🎂 Pastelería</option>
                                    <option value="Bebidas">☕ Bebidas</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="2" maxlength="255"
                                placeholder="Breve descripción del producto..."></textarea>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Precio Venta
                                    ($)</label>
                                <input type="number" step="0.01" min="0.01" max="999999.99" name="precio_venta"
                                    class="form-control py-2" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Stock
                                    Mínimo</label>
                                <input type="number" step="1" name="stock_minimo" class="form-control py-2" min="0"
                                    max="99999" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-3 bg-light">
                        <button type="button" class="btn btn-link text-muted text-decoration-none"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm fw-bold">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <?php include 'includes/footer.php'; ?>
    <script>
        let allInsumos = [];
        let allProductos = [];
        let currentCategoria = '';

        document.addEventListener('DOMContentLoaded', async () => {
            await loadInsumos();
            await loadProductos();
        });

        async function loadInsumos() {
            try {
                const res = await fetch('../backend/api.php?route=get_insumos');
                const data = await res.json();
                if (data.success) {
                    allInsumos = data.data;
                }
            } catch (e) {
                console.error('Error fetching insumos:', e);
            }
        }

        async function loadProductos(categoria = '') {
            let url = '../backend/api.php?route=get_productos';
            if (categoria) {
                url = `../backend/api.php?route=get_productos_by_categoria&categoria=${encodeURIComponent(categoria)}`;
            }

            try {
                const res = await fetch(url);
                const data = await res.json();
                const container = document.getElementById('productosCatalog');
                const countBadge = document.getElementById('productCount');
                container.innerHTML = '';

                if (data.success && data.data && data.data.length > 0) {
                    allProductos = data.data;
                    countBadge.textContent = `Mostrando ${data.data.length} producto(s)`;

                    const puedeGestionar = (typeof tienePermiso === 'function' ? tienePermiso('productos.gestionar') : true);
                    const puedeEliminar = (typeof tienePermiso === 'function' ? tienePermiso('productos.eliminar') : true);

                    data.data.forEach(p => {
                        let catEmoji = '🍞';
                        let catBg = 'bg-warning text-dark';
                        if (p.categoria === 'Pan Salado') { catEmoji = '🥖'; catBg = 'bg-info text-white'; }
                        if (p.categoria === 'Pastelería') { catEmoji = '🎂'; catBg = 'bg-danger text-white'; }
                        if (p.categoria === 'Bebidas') { catEmoji = '☕'; catBg = 'bg-secondary text-white'; }

                        let stockBadge = '<span class="badge bg-success">En Stock</span>';
                        if (p.stock_actual <= 0) {
                            stockBadge = '<span class="badge bg-danger">Agotado</span>';
                        } else if (p.stock_actual <= p.stock_minimo) {
                            stockBadge = '<span class="badge bg-warning text-dark">Stock Bajo</span>';
                        }

                        container.innerHTML += `
                            <div class="col-12 col-sm-6 col-md-4 col-xl-3">
                                <div class="card product-card h-100 position-relative">
                                    <div class="card-body d-flex flex-column text-center">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="category-badge ${catBg}">${p.categoria}</span>
                                            ${stockBadge}
                                        </div>

                                        <div class="product-icon-wrapper">
                                            <span>${catEmoji}</span>
                                        </div>

                                        <h5 class="fw-bold text-dark mb-1">${p.nombre}</h5>
                                        <p class="text-muted small text-truncate mb-3" style="max-height: 40px;">${p.descripcion || 'Sin descripción'}</p>

                                        <div class="bg-light p-2 rounded mb-3 mt-auto">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <small class="text-muted">Precio Venta:</small>
                                                <span class="fw-bold text-primary fs-5">${formatCurrency(p.precio_venta)}</span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">Stock Disponible:</small>
                                                <span class="fw-semibold text-dark">${p.stock_actual} unids</span>
                                            </div>
                                        </div>

                                        <div class="d-flex gap-2">
                                            ${puedeGestionar ? `
                                                <button class="btn btn-sm btn-outline-success flex-grow-1" onclick="openProducirModal(${p.id}, '${escapeHtml(p.nombre)}')">
                                                    <i class="fas fa-industry me-1"></i> Producir
                                                </button>
                                                <button class="btn btn-sm btn-outline-primary" onclick="openEditProductoModal(${p.id})">
                                                    <i class="fas fa-pen"></i>
                                                </button>
                                            ` : ''}
                                            ${puedeEliminar ? `
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteProduct(${p.id}, '${escapeHtml(p.nombre)}')">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            ` : ''}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    countBadge.textContent = '0 productos encontrados';
                    container.innerHTML = `
                        <div class="col-12 text-center py-5 text-muted">
                            <i class="fas fa-box-open fs-1 mb-3 text-secondary"></i>
                            <p>No se encontraron productos en esta categoría.</p>
                        </div>
                    `;
                }
            } catch (e) {
                console.error('Error fetching products:', e);
            }
        }

        function filterCategoria(cat, btn) {
            currentCategoria = cat;
            document.querySelectorAll('#categoryFilters .filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            loadProductos(cat);
        }

        function addIngredienteRow() {
            const container = document.getElementById('ingredientesList');
            const rowId = Date.now();
            let options = '<option value="" disabled selected>Seleccione insumo...</option>';
            allInsumos.forEach(i => {
                options += `<option value="${i.id}" data-um="${i.unidad_medida}">${i.nombre} (${i.unidad_medida})</option>`;
            });

            const row = document.createElement('div');
            row.className = 'row g-2 align-items-center ingrediente-row';
            row.id = `row-${rowId}`;
            row.innerHTML = `
                <div class="col-6">
                    <select name="insumo_id[]" class="form-select form-select-sm" required onchange="updateRowUm(this, ${rowId})">
                        ${options}
                    </select>
                </div>
                <div class="col-4">
                    <div class="input-group input-group-sm">
                        <input type="number" step="0.001" min="0.001" name="cantidad_usada[]" class="form-control" placeholder="Cant" required>
                        <span class="input-group-text um-label" id="um-${rowId}">u</span>
                    </div>
                </div>
                <div class="col-2 text-end">
                    <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="document.getElementById('row-${rowId}').remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            container.appendChild(row);
        }

        function updateRowUm(select, rowId) {
            const opt = select.options[select.selectedIndex];
            const um = opt.getAttribute('data-um') || 'u';
            document.getElementById(`um-${rowId}`).textContent = um;
        }

        document.getElementById('addProductoForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);

            const insumoIds = formData.getAll('insumo_id[]');
            const cantidades = formData.getAll('cantidad_usada[]');

            const receta = [];
            for (let i = 0; i < insumoIds.length; i++) {
                if (insumoIds[i] && cantidades[i]) {
                    receta.push({
                        insumo_id: parseInt(insumoIds[i]),
                        cantidad_usada: parseFloat(cantidades[i])
                    });
                }
            }

            const payload = {
                nombre: formData.get('nombre'),
                categoria: formData.get('categoria'),
                descripcion: formData.get('descripcion'),
                precio_venta: parseFloat(formData.get('precio_venta')),
                stock_inicial: parseInt(formData.get('cantidad') || 0),
                stock_minimo: parseInt(formData.get('stock_minimo') || 5),
                receta: receta
            };

            try {
                const res = await fetch('../backend/api.php?route=add_producto', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    showAlert('Producto registrado exitosamente', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('addProductoModal')).hide();
                    e.target.reset();
                    document.getElementById('ingredientesList').innerHTML = '';
                    await loadProductos(currentCategoria);
                } else {
                    showAlert(data.message || 'Error al registrar el producto', 'error');
                }
            } catch (err) {
                console.error(err);
                showAlert('Error de conexión con el servidor.', 'error');
            }
        });

        function openProducirModal(id, nombre) {
            document.getElementById('producirProductoId').value = id;
            document.getElementById('producirProductoNombre').textContent = nombre;
            document.getElementById('producirCantidad').value = 10;
            const modal = new bootstrap.Modal(document.getElementById('producirModal'));
            modal.show();
        }

        function openEditProductoModal(id) {
            const producto = allProductos.find(x => parseInt(x.id) === parseInt(id));
            if (!producto) {
                showAlert('No se encontró el producto seleccionado.', 'warning');
                return;
            }
            const form = document.getElementById('editProductoForm');
            form.elements['id'].value = producto.id;
            form.elements['nombre'].value = producto.nombre;
            form.elements['descripcion'].value = producto.descripcion || '';
            form.elements['precio_venta'].value = producto.precio_venta;
            form.elements['categoria'].value = producto.categoria;
            form.elements['stock_minimo'].value = producto.stock_minimo;
            new bootstrap.Modal(document.getElementById('editProductoModal')).show();
        }

        document.getElementById('editProductoForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const payload = {
                id: parseInt(formData.get('id')),
                nombre: formData.get('nombre'),
                descripcion: formData.get('descripcion'),
                precio_venta: parseFloat(formData.get('precio_venta')),
                categoria: formData.get('categoria'),
                stock_minimo: parseInt(formData.get('stock_minimo') || 0)
            };

            try {
                const res = await fetch('../backend/api.php?route=update_producto', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    showAlert('Producto actualizado correctamente.', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('editProductoModal')).hide();
                    await loadProductos(currentCategoria);
                } else {
                    showAlert(data.message || 'Error al actualizar el producto', 'error');
                }
            } catch (err) {
                console.error(err);
                showAlert('Error de conexión con el servidor.', 'error');
            }
        });

        document.getElementById('producirForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const prodId = document.getElementById('producirProductoId').value;
            const cant = document.getElementById('producirCantidad').value;

            try {
                const res = await fetch('../backend/api.php?route=producir_producto', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ producto_id: prodId, cantidad_producida: cant })
                });
                const data = await res.json();
                if (data.success) {
                    showAlert('Producción realizada con éxito. Stock actualizado.', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('producirModal')).hide();
                    await loadProductos(currentCategoria);
                } else {
                    showAlert(data.message || 'Error al realizar producción', 'error');
                }
            } catch (err) {
                console.error(err);
                showAlert('Error al procesar producción', 'error');
            }
        });

        async function deleteProduct(id, nombre) {
            if (typeof tienePermiso === 'function' && !tienePermiso('productos.eliminar')) {
                showAlert('No dispone de permisos para eliminar productos.', 'warning');
                return;
            }

            if (!(await showConfirm(`¿Está seguro de eliminar "${nombre}" del catálogo?`))) return;

            try {
                const res = await fetch('../backend/api.php?route=delete_producto', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });
                const data = await res.json();
                if (data.success) {
                    showAlert('Producto eliminado correctamente.', 'success');
                    await loadProductos(currentCategoria);
                } else {
                    showAlert(data.message || 'Error al eliminar el producto', 'error');
                }
            } catch (e) {
                console.error(e);
                showAlert('Error de conexión.', 'error');
            }
        }

        function escapeHtml(text) {
            if (!text) return '';
            return text.replace(/'/g, "\\'").replace(/"/g, '&quot;');
        }
    </script>
</body>

</html>