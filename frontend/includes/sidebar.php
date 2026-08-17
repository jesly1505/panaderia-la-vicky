<?php
// frontend/includes/sidebar.php
require_once __DIR__ . '/permisos.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$active = $active ?? str_replace('.php', '', $currentPage);
$permisos = $_SESSION['permisos'] ?? [];

// Lista de enlaces principales [archivo, icono, etiqueta, permisos requeridos]
$mainNav = [
    'index'             => ['index.php',              'fa-home',               'Dashboard',     ['dashboard.ver']],
    'inventario'        => ['inventario.php',         'fa-box',                'Inventario',    ['inventario.ver']],
    'productos'         => ['productos.php',          'fa-bread-slice',        'Productos',     ['productos.ver']],
    'produccion_manual' => ['produccion_manual.php',  'fa-industry',           'Prod. Manual',  ['produccion.ver']],
    'pedidos'           => ['pedidos.php',            'fa-shopping-cart',      'Pedidos',       ['pedidos.ver']],
    'ventas'            => ['ventas.php',             'fa-chart-line',         'Ventas',        ['ventas.ver']],
    'clientes'          => ['clientes.php',           'fa-users',              'Clientes',      ['clientes.ver']],
    'reportes'          => ['reportes.php',           'fa-file-alt',           'Reportes',      ['reportes.ver']],
    'bitacora'          => ['bitacora.php',           'fa-shield-halved',      'Bitácora',      ['auditoria.ver']],
    'incidencias'       => ['incidencias.php',        'fa-exclamation-triangle','Incidencias',  ['dashboard.ver', 'auditoria.ver']],
];

// Submódulos de Configuración
$configSection = [
    'perfil'        => ['perfil.php',        'fa-store',       'Perfil de Empresa',  ['perfil.gestionar']],
    'configuracion' => ['configuracion.php', 'fa-users-cog',   'Empleados',          ['empleados.ver']],
    'roles'         => ['roles.php',         'fa-user-shield', 'Roles y Permisos',   ['permisos.gestionar']],
    'respaldo'      => ['respaldo.php',      'fa-database',    'Respaldo',           ['permisos.gestionar', 'perfil.gestionar', 'auditoria.ver']],
];

$configPermisos = array_reduce($configSection, function ($acc, $item) {
    return array_merge($acc, $item[3]);
}, []);
$isConfigActive = array_key_exists($active, $configSection);
?>

<!-- Permisos de sesión expuestos a JavaScript -->
<script>
    const SESION_PERMISOS = <?= json_encode($permisos, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    function tienePermiso(codigo) {
        if (!SESION_PERMISOS || !Array.isArray(SESION_PERMISOS)) return false;
        return SESION_PERMISOS.includes(codigo);
    }
</script>

<!-- Desktop Sidebar -->
<aside class="sidebar d-none d-lg-block">
    <div class="p-4 text-center border-bottom border-secondary border-opacity-25">
        <h3 class="text-white fw-bold m-0"><i class="fas fa-bread-slice me-2" style="color: var(--primary);"></i>La Vicky</h3>
        <p class="text-muted small mt-1 mb-0">Sistema de Gestión</p>
    </div>
    
    <nav class="mt-3 pb-4">
        <?php foreach ($mainNav as $key => [$href, $icon, $label, $req]): ?>
            <?php if (empty($req) || tiene_permiso(...$req)): ?>
                <a href="<?= $href ?>" class="nav-link <?= ($active === $key || $currentPage === $href) ? 'active' : '' ?>">
                    <i class="fas <?= $icon ?>"></i> <?= htmlspecialchars($label) ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if (tiene_permiso(...$configPermisos)): ?>
            <a href="#" id="configSectionToggle" class="sidebar-section-toggle open" onclick="toggleConfigSection(event)" aria-expanded="true">
                <i class="fas fa-cog"></i><span> Configuración</span>
                <i class="fas fa-chevron-down sidebar-chevron"></i>
            </a>
            <div id="configSectionItems" class="sidebar-section-items open">
                <?php foreach ($configSection as $key => [$href, $icon, $label, $req]): ?>
                    <?php if (tiene_permiso(...$req)): ?>
                        <a href="<?= $href ?>" class="nav-link <?= ($active === $key || $currentPage === $href) ? 'active' : '' ?>">
                            <i class="fas <?= $icon ?>"></i> <?= htmlspecialchars($label) ?>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </nav>
</aside>

<!-- Mobile Offcanvas Sidebar -->
<div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
    <div class="offcanvas-header border-bottom border-secondary">
        <h5 class="offcanvas-title fw-bold" id="sidebarOffcanvasLabel">
            <i class="fas fa-bread-slice me-2" style="color: var(--primary);"></i>La Vicky
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <nav class="mt-2 pb-4">
            <?php foreach ($mainNav as $key => [$href, $icon, $label, $req]): ?>
                <?php if (empty($req) || tiene_permiso(...$req)): ?>
                    <a href="<?= $href ?>" class="nav-link text-white py-3 px-4 border-bottom border-secondary border-opacity-25 <?= ($active === $key || $currentPage === $href) ? 'active bg-primary' : '' ?>" style="border-radius: 0; margin: 0;">
                        <i class="fas <?= $icon ?> me-3"></i> <?= htmlspecialchars($label) ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php if (tiene_permiso(...$configPermisos)): ?>
                <a href="#" id="configSectionToggleMobile" class="sidebar-section-toggle open" onclick="toggleConfigSection(event)" aria-expanded="true" style="margin: 12px 12px 4px; color: rgba(255,255,255,0.8);">
                    <span><i class="fas fa-cog me-2"></i> Configuración</span>
                    <i class="fas fa-chevron-down sidebar-chevron"></i>
                </a>
                <div id="configSectionItemsMobile" class="sidebar-section-items open" style="margin: 0 12px 8px;">
                    <?php foreach ($configSection as $key => [$href, $icon, $label, $req]): ?>
                        <?php if (tiene_permiso(...$req)): ?>
                            <a href="<?= $href ?>" class="nav-link text-white py-2 ps-5 pe-4 border-bottom border-secondary border-opacity-25 <?= ($active === $key || $currentPage === $href) ? 'active bg-primary' : '' ?>" style="border-radius: 0; margin: 0;">
                                <i class="fas <?= $icon ?> me-2"></i> <?= htmlspecialchars($label) ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </nav>
    </div>
</div>

<script>
    function toggleConfigSection(e) {
        if (e) e.preventDefault();

        const desktopWrap = document.getElementById('configSectionItems');
        const desktopToggle = document.getElementById('configSectionToggle');
        const mobileWrap = document.getElementById('configSectionItemsMobile');
        const mobileToggle = document.getElementById('configSectionToggleMobile');

        const isOpen = desktopWrap ? desktopWrap.classList.contains('open') : false;
        const next = !isOpen;

        if (desktopWrap) desktopWrap.classList.toggle('open', next);
        if (desktopToggle) {
            desktopToggle.classList.toggle('open', next);
            desktopToggle.setAttribute('aria-expanded', next ? 'true' : 'false');
        }
        if (mobileWrap) mobileWrap.classList.toggle('open', next);
        if (mobileToggle) {
            mobileToggle.classList.toggle('open', next);
            mobileToggle.setAttribute('aria-expanded', next ? 'true' : 'false');
        }

        try { localStorage.setItem('configSectionOpen', next ? '1' : '0'); } catch(err) {}
    }

    document.addEventListener('DOMContentLoaded', () => {
        const desktopWrap = document.getElementById('configSectionItems');
        const desktopToggle = document.getElementById('configSectionToggle');
        const mobileWrap = document.getElementById('configSectionItemsMobile');
        const mobileToggle = document.getElementById('configSectionToggleMobile');
        const sidebar = document.querySelector('.sidebar.d-none.d-lg-block');
        const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');

        const shouldOpen = false;

        if (desktopWrap) desktopWrap.classList.toggle('open', shouldOpen);
        if (desktopToggle) {
            desktopToggle.classList.toggle('open', shouldOpen);
            desktopToggle.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
        }
        if (mobileWrap) mobileWrap.classList.toggle('open', shouldOpen);
        if (mobileToggle) {
            mobileToggle.classList.toggle('open', shouldOpen);
            mobileToggle.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
        }

        if (sidebar && sidebarToggleBtn) {
            try {
                if (localStorage.getItem('sidebarCollapsed') === '1') {
                    sidebar.classList.add('collapsed');
                }
            } catch(err) {}

            sidebarToggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
                try {
                    localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed') ? '1' : '0');
                } catch(err) {}
            });
        }
    });
</script>
