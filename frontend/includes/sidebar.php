<?php
// frontend/includes/sidebar.php
require_once __DIR__ . '/permisos.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$active = $active ?? str_replace('.php', '', $currentPage);
$permisos = $_SESSION['permisos'] ?? [];

// Lista de enlaces principales [archivo, icono, etiqueta, permisos requeridos, badge]
$mainNav = [
    'index' => ['index.php', 'fa-house', 'Dashboard', ['dashboard.ver']],
    'inventario' => ['inventario.php', 'fa-box-archive', 'Inventario', ['inventario.ver']],
    'proveedores' => ['proveedores.php', 'fa-truck-moving', 'Proveedores', ['proveedores.ver']],
    'productos' => ['productos.php', 'fa-bread-slice', 'Productos', ['productos.ver']],
    'produccion_manual' => ['produccion_manual.php', 'fa-chart-column', 'Prod. Manual', ['produccion.ver']],
    'pedidos' => ['pedidos.php', 'fa-cart-shopping', 'Pedidos', ['pedidos.ver']],
    'ventas' => ['ventas.php', 'fa-chart-line', 'Ventas', ['ventas.ver']],
    'clientes' => ['clientes.php', 'fa-users', 'Clientes', ['clientes.ver']],
    'reportes' => ['reportes.php', 'fa-chart-pie', 'Reportes', ['reportes.ver', 'gastos.ver', 'gastos.gestionar']],
    'bitacora' => ['bitacora.php', 'fa-clipboard-list', 'Bitácora', ['auditoria.ver']],
    'incidencias' => ['incidencias.php', 'fa-triangle-exclamation', 'Incidencias', ['dashboard.ver', 'auditoria.ver'], 'incidencias'],
];

// Submódulos de Configuración
$configSection = [
    'perfil' => ['perfil.php', 'fa-building', 'Perfil de Empresa', ['perfil.gestionar']],
    'configuracion' => ['configuracion.php', 'fa-users-gear', 'Empleados', ['empleados.ver']],
    'roles' => ['roles.php', 'fa-user-shield', 'Roles y Permisos', ['permisos.gestionar']],
    'respaldo' => ['respaldo.php', 'fa-database', 'Respaldo', ['permisos.gestionar', 'perfil.gestionar', 'auditoria.ver']],
];

$configPermisos = array_reduce($configSection, function ($acc, $item) {
    return array_merge($acc, $item[3]);
}, []);
$isConfigActive = array_key_exists($active, $configSection) || in_array($currentPage, array_column($configSection, 0));
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
<aside class="sidebar d-none d-lg-flex">
    <!-- Header / Logo La Vicky -->
    <div class="sidebar-header">
        <div class="brand-emblem-mini">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
                <path
                    d="M12 2C8.5 2 5.6 4.4 5.1 7.7C3.3 8.3 2 10 2 12c0 2.2 1.6 4 3.7 4.4.2 1.5 1.5 2.6 3 2.6h6.6c1.5 0 2.8-1.1 3-2.6 2.1-.4 3.7-2.2 3.7-4.4 0-2-1.3-3.7-3.1-4.3C18.4 4.4 15.5 2 12 2zm-3.5 19v1h7v-1h-7z" />
            </svg>
        </div>
        <h2 class="brand-title-sidebar">La Vicky</h2>
        <div class="brand-subtitle-sidebar">
            <span class="wheat-icon">🌾</span>
            <span>PANADERÍA</span>
            <span class="wheat-icon">🌾</span>
        </div>
    </div>

    <!-- Navegación -->
    <nav>
        <div class="sidebar-section-title">• PRINCIPAL •</div>

        <?php foreach ($mainNav as $key => $navItem):
            [$href, $icon, $label, $req] = $navItem;
            $hasBadge = isset($navItem[4]) && $navItem[4] === 'incidencias';
            $isActive = ($active === $key || ($currentPage === $href && !array_key_exists($active, $configSection)));
            ?>
            <?php if (empty($req) || tiene_permiso(...$req)): ?>
                <a href="<?= $href ?>" class="nav-link <?= $isActive ? 'active' : '' ?>"
                    title="<?= htmlspecialchars($label) ?>">
                    <i class="fas <?= $icon ?> nav-icon"></i>
                    <span class="nav-text"><?= htmlspecialchars($label) ?></span>
                    <?php if ($hasBadge): ?>
                        <span class="badge-incidencias" id="sidebarIncidenciasBadge" style="display: none;"></span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>

        <!-- Configuración -->
        <?php if (tiene_permiso(...$configPermisos)): ?>
            <div class="sidebar-divider"></div>
            <div class="sidebar-section-title">• CONFIGURACIÓN •</div>

            <button type="button" id="configSectionToggle"
                class="sidebar-config-toggle <?= $isConfigActive ? 'open' : '' ?>" onclick="toggleConfigSection(event)"
                aria-expanded="<?= $isConfigActive ? 'true' : 'false' ?>" title="Configuración">
                <div class="toggle-left">
                    <i class="fas fa-gear nav-icon"></i>
                    <span class="toggle-text">Configuración</span>
                </div>
                <i class="fas fa-chevron-down chevron-icon"></i>
            </button>
            <div id="configSectionItems" class="sidebar-submenu-container <?= $isConfigActive ? 'open' : '' ?>">
                <?php foreach ($configSection as $key => [$href, $icon, $label, $req]): ?>
                    <?php if (tiene_permiso(...$req)): ?>
                        <a href="<?= $href ?>"
                            class="sidebar-sublink <?= ($active === $key || $currentPage === $href) ? 'active' : '' ?>"
                            title="<?= htmlspecialchars($label) ?>">
                            <i class="fas <?= $icon ?>"></i>
                            <span class="sub-text"><?= htmlspecialchars($label) ?></span>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </nav>
</aside>

<!-- Mobile Offcanvas Sidebar -->
<div class="offcanvas offcanvas-start"
    style="background-color: var(--bakery-dark); color: var(--bakery-cream); width: 280px;" tabindex="-1"
    id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
    <div class="offcanvas-header" style="border-bottom: 1px solid rgba(201, 149, 69, 0.25); padding: 1.25rem 1rem;">
        <div class="d-flex align-items-center">
            <div class="brand-emblem-mini me-2" style="width: 36px; height: 36px; font-size: 1.1rem; margin-bottom: 0;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                    <path
                        d="M12 2C8.5 2 5.6 4.4 5.1 7.7C3.3 8.3 2 10 2 12c0 2.2 1.6 4 3.7 4.4.2 1.5 1.5 2.6 3 2.6h6.6c1.5 0 2.8-1.1 3-2.6 2.1-.4 3.7-2.2 3.7-4.4 0-2-1.3-3.7-3.1-4.3C18.4 4.4 15.5 2 12 2zm-3.5 19v1h7v-1h-7z" />
                </svg>
            </div>
            <div>
                <h5 class="brand-title-sidebar m-0" style="font-size: 1.2rem;">La Vicky</h5>
                <div class="brand-subtitle-sidebar m-0" style="font-size: 0.6rem; justify-content: flex-start;">
                    <span>PANADERÍA</span>
                </div>
            </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0 sidebar"
        style="position: relative; height: 100%; width: 100%; box-shadow: none; border: none;">
        <nav class="pt-2 pb-4">
            <div class="sidebar-section-title">• PRINCIPAL •</div>

            <?php foreach ($mainNav as $key => $navItem):
                [$href, $icon, $label, $req] = $navItem;
                $hasBadge = isset($navItem[4]) && $navItem[4] === 'incidencias';
                $isActive = ($active === $key || ($currentPage === $href && !array_key_exists($active, $configSection)));
                ?>
                <?php if (empty($req) || tiene_permiso(...$req)): ?>
                    <a href="<?= $href ?>" class="nav-link <?= $isActive ? 'active' : '' ?>">
                        <i class="fas <?= $icon ?> nav-icon"></i>
                        <span class="nav-text"><?= htmlspecialchars($label) ?></span>
                        <?php if ($hasBadge): ?>
                            <span class="badge-incidencias" id="sidebarIncidenciasBadgeMobile" style="display: none;"></span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php if (tiene_permiso(...$configPermisos)): ?>
                <div class="sidebar-divider"></div>
                <div class="sidebar-section-title">• CONFIGURACIÓN •</div>

                <button type="button" id="configSectionToggleMobile"
                    class="sidebar-config-toggle <?= $isConfigActive ? 'open' : '' ?>" onclick="toggleConfigSection(event)"
                    aria-expanded="<?= $isConfigActive ? 'true' : 'false' ?>">
                    <div class="toggle-left">
                        <i class="fas fa-gear nav-icon"></i>
                        <span class="toggle-text">Configuración</span>
                    </div>
                    <i class="fas fa-chevron-down chevron-icon"></i>
                </button>
                <div id="configSectionItemsMobile" class="sidebar-submenu-container <?= $isConfigActive ? 'open' : '' ?>">
                    <?php foreach ($configSection as $key => [$href, $icon, $label, $req]): ?>
                        <?php if (tiene_permiso(...$req)): ?>
                            <a href="<?= $href ?>"
                                class="sidebar-sublink <?= ($active === $key || $currentPage === $href) ? 'active' : '' ?>">
                                <i class="fas <?= $icon ?>"></i>
                                <span class="sub-text"><?= htmlspecialchars($label) ?></span>
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

        const isOpen = desktopWrap ? desktopWrap.classList.contains('open') : (mobileWrap ? mobileWrap.classList.contains('open') : false);
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

        try { localStorage.setItem('configSectionOpen', next ? '1' : '0'); } catch (err) { }
    }

    async function loadIncidenciasBadge() {
        try {
            const res = await fetch('../backend/api.php?route=get_incidencias&filter=abierta');
            const data = await res.json();
            if (data.success && Array.isArray(data.data)) {
                const count = data.data.filter(i => (i.estado === 'abierta' || i.estado === 'pendiente')).length;
                const b1 = document.getElementById('sidebarIncidenciasBadge');
                const b2 = document.getElementById('sidebarIncidenciasBadgeMobile');
                if (count > 0) {
                    if (b1) { b1.textContent = count; b1.style.display = 'inline-flex'; }
                    if (b2) { b2.textContent = count; b2.style.display = 'inline-flex'; }
                } else {
                    if (b1) b1.style.display = 'none';
                    if (b2) b2.style.display = 'none';
                }
            }
        } catch (e) { }
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadIncidenciasBadge();

        const sidebar = document.querySelector('.sidebar.d-none.d-lg-flex');
        const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');

        if (sidebar && sidebarToggleBtn) {
            try {
                if (localStorage.getItem('sidebarCollapsed') === '1') {
                    sidebar.classList.add('collapsed');
                }
            } catch (err) { }

            sidebarToggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
                try {
                    localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed') ? '1' : '0');
                } catch (err) { }
            });
        }
    });
</script>