<?php
// frontend/respaldo.php
session_start();
require_once __DIR__ . '/includes/permisos.php';

if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

if (!tiene_permiso('permisos.gestionar', 'perfil.gestionar', 'auditoria.ver')) {
    header("Location: index.php");
    exit();
}

$pageTitle = "Respaldo y Recuperación";
$pageHeader = "Copias de Seguridad y Base de Datos";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include 'includes/head.php'; ?>
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <div class="main-content">
            <?php include 'includes/navbar.php'; ?>
            <div class="container-fluid p-4 animate-fade-in">
                
                <?php if(isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i> Error al generar el respaldo. Verifique la configuración del servidor o permisos.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="row justify-content-center pt-3">
                    <div class="col-12 col-md-8 col-lg-6">
                        <div class="card shadow-sm border-0 text-center p-4 p-md-5">
                            <div class="mb-4">
                                <div class="rounded-circle bg-primary-light d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px; background-color: var(--primary-light);">
                                    <i class="fas fa-database text-primary" style="font-size: 3rem; color: var(--primary) !important;"></i>
                                </div>
                            </div>
                            <h4 class="fw-bold text-dark mb-2">Copia de Seguridad de la Base de Datos</h4>
                            <p class="text-muted mb-4">
                                Descarga un archivo SQL completo con la estructura de todas las tablas y sus registros. Este archivo permite restaurar el sistema ante cualquier contingencia o auditoría técnica.
                            </p>
                            
                            <div class="p-3 bg-light rounded-3 mb-4 text-start small text-muted">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-shield-alt text-success me-2 fs-5"></i>
                                    <span>Exportación íntegra de tablas operativas y de seguridad.</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-file-code text-info me-2 fs-5"></i>
                                    <span>Formato compatible con MySQL, MariaDB y phpMyAdmin.</span>
                                </div>
                            </div>

                            <a href="../backend/Controllers/CmmiController.php?action=backup_db" class="btn btn-lg btn-primary shadow-sm py-3 px-4">
                                <i class="fas fa-download me-2"></i> Generar y Descargar Respaldo (.SQL)
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
    <script src="../assets/js/app.js"></script>
</body>
</html>
