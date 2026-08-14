<?php
/**
 * Navegación lateral compartida (sidebar + barra superior).
 * Las páginas deben iniciar sesión y validar $_SESSION['usuario'] antes de incluir.
 * Variables esperadas:
 *   $active: clave de la página actual (ver $nav).
 *   $titulo: título mostrado en la barra superior.
 */
require_once __DIR__ . '/permisos.php';

$rol = $_SESSION['rol'] ?? '';
$usuario = $_SESSION['nombre'] ?? '';
$active = $active ?? '';
$titulo = $titulo ?? '';
$permisos = $_SESSION['permisos'] ?? [];

// clave => [archivo, icono, etiqueta, permisos requeridos (cualquiera)]
$nav = [
    'index'             => ['index.php',              'fa-home',           'Dashboard',     ['dashboard.ver']],
    'inventario'        => ['inventario.php',         'fa-box',            'Inventario',    ['inventario.ver']],
    'productos'         => ['productos.php',          'fa-bread-slice',    'Productos',     ['productos.ver']],
    'produccion_manual' => ['produccion_manual.php',  'fa-industry',       'Prod. Manual',  ['produccion.ver']],
    'pedidos'           => ['pedidos.php',            'fa-shopping-cart',  'Pedidos',       ['pedidos.ver']],
    'ventas'            => ['ventas.php',             'fa-chart-line',     'Ventas',        ['ventas.ver']],
    'clientes'          => ['clientes.php',           'fa-users',          'Clientes',      ['clientes.ver']],
    'reportes'          => ['reportes.php',           'fa-file-alt',       'Reportes',      ['reportes.ver']],
    'bitacora'          => ['bitacora.php',           'fa-shield-halved',  'Bitácora',      ['auditoria.ver']],
];

// Submódulos agrupados bajo la sección "Configuración".
// Clave = $active de cada página.
$configSection = [
    'perfil'        => ['perfil.php',              'fa-store',          'Perfil de la Panadería', ['perfil.gestionar']],
    'configuracion' => ['configuracion.php',      'fa-users',          'Empleados',       ['empleados.ver']],
    'roles'         => ['roles.php',              'fa-user-shield',    'Roles y Permisos', ['permisos.gestionar']],
];
?>
<!-- Sidebar -->
<div class="col-md-2 sidebar d-none d-md-block">
    <div class="text-center mb-4">
        <h3 class="text-white">🥖 La Vicky</h3>
    </div>
    <?php foreach ($nav as $key => $item): ?>
        <?php [$href, $icon, $label, $req] = $item; ?>
        <?php if (tiene_permiso(...$req)): ?>
            <a href="<?= $href ?>"<?= $key === $active ? ' class="active"' : '' ?>>
                <i class="fas <?= $icon ?> me-2"></i> <?= $label ?>
            </a>
        <?php endif; ?>
    <?php endforeach; ?>

    <?php
    $configPermisos = array_reduce($configSection, function ($acc, $item) {
        return array_merge($acc, $item[3]);
    }, []);
    $configActive = array_key_exists($active, $configSection);
    ?>
    <?php if (tiene_permiso(...$configPermisos)): ?>
        <style>
            .sidebar-section-toggle { display: flex; justify-content: space-between; align-items: center; padding: 15px 20px 5px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #d1b9c9; border-bottom: 1px solid rgba(255,255,255,.12); margin-bottom: 5px; text-decoration: none; cursor: pointer; }
            .sidebar-section-toggle:hover, .sidebar-section-toggle.open { color: #fff; background: transparent; }
            .sidebar-section-toggle .sidebar-chevron { transition: transform .3s ease; font-size: 11px; }
            .sidebar-section-items { display: none; }
            .sidebar-section-items.open { display: block; }
        </style>
        <a href="#" id="configSectionToggle" class="sidebar-section-toggle" onclick="toggleConfigSection(event)" aria-expanded="false" aria-controls="configSectionItems">
            <span><i class="fas fa-cog me-2"></i> Configuración</span>
            <i class="fas fa-chevron-down sidebar-chevron"></i>
        </a>
        <div id="configSectionItems" class="sidebar-section-items">
            <?php foreach ($configSection as $key => $item): ?>
                <?php [$href, $icon, $label, $req] = $item; ?>
                <?php if (tiene_permiso(...$req)): ?>
                    <a href="<?= $href ?>"<?= $key === $active ? ' class="active"' : '' ?> style="padding-left:40px;">
                        <i class="fas <?= $icon ?> me-2"></i> <?= $label ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <script>
            (function () {
                const wrap = document.getElementById('configSectionItems');
                const toggle = document.getElementById('configSectionToggle');
                if (!wrap || !toggle) return;
                const configActive = <?= $configActive ? 'true' : 'false' ?>;
                const saved = localStorage.getItem('configSectionOpen');
                let open = configActive;
                if (saved !== null) open = saved === '1';
                function apply(open) {
                    wrap.classList.toggle('open', open);
                    toggle.classList.toggle('open', open);
                    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                }
                apply(open);
                window.toggleConfigSection = function (e) {
                    e.preventDefault();
                    open = !wrap.classList.contains('open');
                    localStorage.setItem('configSectionOpen', open ? '1' : '0');
                    apply(open);
                };
            })();
        </script>
    <?php endif; ?>
</div>

<!-- Permisos del usuario para uso desde JS (ocultar botones de acción) -->
<script>
    const SESION_PERMISOS = <?= json_encode($permisos, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    function tienePermiso(codigo) {
        return SESION_PERMISOS.includes(codigo);
    }
</script>

<!-- Top Navbar -->
<div class="top-navbar">
    <div>
        <h4 class="m-0"><?= htmlspecialchars($titulo) ?></h4>
    </div>
    <div>
        <span class="me-3"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($usuario) ?>
            <small class="text-muted">(<?= htmlspecialchars($rol) ?>)</small></span>
        <a href="#" class="btn btn-outline-danger btn-sm" onclick="event.preventDefault(); fetch('../backend/api.php?route=logout').then(() => window.location.href = 'login.html');">
            <i class="fas fa-sign-out-alt"></i> Salir
        </a>
    </div>
</div>
