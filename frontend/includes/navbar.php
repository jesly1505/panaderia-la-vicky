<?php
// frontend/includes/navbar.php
?>
<header class="top-navbar">
    <div class="d-flex align-items-center">
        <button class="btn btn-link d-lg-none me-3 p-0 text-dark" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas">
            <i class="fas fa-bars fs-4"></i>
        </button>
        <button class="btn btn-link d-none d-lg-flex me-3 p-0 text-dark align-items-center" type="button" id="sidebarToggleBtn" title="Colapsar / Expandir menú">
            <i class="fas fa-bars fs-5"></i>
        </button>
        <h4 class="m-0 fw-bold text-dark d-none d-sm-block"><?php echo isset($pageHeader) ? $pageHeader : "Panel de Administración"; ?></h4>
        <h5 class="m-0 fw-bold text-dark d-block d-sm-none"><?php echo isset($pageHeader) ? $pageHeader : "La Vicky"; ?></h5>
    </div>
    <div class="d-flex align-items-center">
        <div class="dropdown">
            <button class="btn btn-link text-dark text-decoration-none dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown">
                <div class="bg-primary-light text-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                    <i class="fas fa-user-circle fs-5"></i>
                </div>
                <span class="d-none d-md-inline fw-medium user-name-display"><?php echo htmlspecialchars($_SESSION['usuario'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                <li><a class="dropdown-item" href="perfil.php"><i class="fas fa-store me-2 text-muted"></i> Mi Perfil</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="#" onclick="logout()"><i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión</a></li>
            </ul>
        </div>
    </div>
</header>
