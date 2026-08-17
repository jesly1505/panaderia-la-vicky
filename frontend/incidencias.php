<?php
// frontend/incidencias.php
session_start();
require_once __DIR__ . '/includes/permisos.php';

if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

if (!tiene_permiso('dashboard.ver', 'auditoria.ver')) {
    header("Location: index.php");
    exit();
}

// Usar bootstrap para que el autoloader esté disponible antes de cargar cualquier clase
[$router, $container] = require_once __DIR__ . '/../backend/bootstrap.php';
require_once __DIR__ . '/../backend/Helpers/DateFilterHelper.php';

$filter     = $_GET['filter'] ?? 'all';
$start_date = $_GET['start_date'] ?? '';
$end_date   = $_GET['end_date'] ?? '';

$db        = $container->get(PDO::class);
$cmmiModel = new \App\Models\CmmiModel($db);
$incidencias = $cmmiModel->getIncidencias($filter, $start_date, $end_date);

$esAdmin = (strtolower($_SESSION['rol'] ?? '') === 'administrador' || tiene_permiso('auditoria.ver'));

$pageTitle = "Gestión de Incidencias";
$pageHeader = "Trazabilidad CMMI e Incidencias";
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
                <?php echo \App\Helpers\DateFilterHelper::getFilterUI($filter, $start_date, $end_date, 'incidencias.php'); ?>
                
                <?php if(isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                        <i class="fas fa-check-circle me-2"></i> Operación realizada con éxito.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if(isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i> Ocurrió un problema al procesar la solicitud.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <div class="row g-4">
                    <div class="col-12 col-lg-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-white">
                                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-plus-circle text-primary me-2"></i>Reportar Incidencia</h5>
                            </div>
                            <div class="card-body">
                                <form action="../backend/Controllers/CmmiController.php?action=registrar_incidencia" method="POST">
                                    <div class="mb-3">
                                        <label class="form-label text-muted small fw-semibold">Módulo Afectado</label>
                                        <select name="modulo" class="form-select" required>
                                            <option value="Ventas">Ventas y Facturación</option>
                                            <option value="Inventario">Inventario e Insumos</option>
                                            <option value="Producción">Producción y Recetas</option>
                                            <option value="Pedidos">Pedidos</option>
                                            <option value="Sistema General">Seguridad / Sistema General</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-muted small fw-semibold">Descripción del Problema</label>
                                        <textarea name="descripcion" class="form-control" rows="4" maxlength="500" placeholder="Describa el inconveniente encontrado..." required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-warning text-dark fw-bold w-100 shadow-sm">
                                        <i class="fas fa-paper-plane me-1"></i> Registrar Incidencia
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-8">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-list-alt text-secondary me-2"></i>Incidencias Registradas</h5>
                                <span class="badge bg-primary"><?= count($incidencias) ?> registro(s)</span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Estado</th>
                                                <th>Módulo</th>
                                                <th>Descripción</th>
                                                <th>Reportado por</th>
                                                <th>Fecha</th>
                                                <?php if($esAdmin): ?><th>Acción</th><?php endif; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(empty($incidencias)): ?>
                                                <tr>
                                                    <td colspan="<?= $esAdmin ? 6 : 5 ?>" class="text-center text-muted py-4">
                                                        No hay incidencias reportadas en el periodo seleccionado.
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach($incidencias as $inc): ?>
                                                <tr>
                                                    <td>
                                                        <?php if($inc['estado'] == 'abierta'): ?>
                                                            <span class="badge bg-danger">Abierta</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-success">Resuelta</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="fw-semibold text-dark"><?= htmlspecialchars($inc['modulo']) ?></td>
                                                    <td class="text-wrap" style="max-width: 250px;"><small><?= htmlspecialchars($inc['descripcion']) ?></small></td>
                                                    <td><small><?= htmlspecialchars($inc['usuario_reporta_nombre'] ?? 'Sistema') ?></small></td>
                                                    <td><small><?= date('d/m/Y H:i', strtotime($inc['fecha_reporte'])) ?></small></td>
                                                    <?php if($esAdmin): ?>
                                                    <td>
                                                        <?php if($inc['estado'] == 'abierta'): ?>
                                                            <a href="../backend/Controllers/CmmiController.php?action=resolver_incidencia&id=<?= (int)$inc['id'] ?>" class="btn btn-sm btn-success shadow-sm">
                                                                <i class="fas fa-check me-1"></i> Resolver
                                                            </a>
                                                        <?php else: ?>
                                                            <small class="text-muted"><i class="fas fa-check-double text-success me-1"></i><?= $inc['fecha_resolucion'] ? date('d/m/Y', strtotime($inc['fecha_resolucion'])) : 'Listo' ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <?php endif; ?>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
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
