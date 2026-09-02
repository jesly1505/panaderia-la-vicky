<?php
// frontend/error.php — Página de error (acceso denegado, mantenimiento, 404, etc.)
session_start();

$code = strtoupper($_GET['code'] ?? '');
$isLogged = isset($_SESSION['usuario_id']) || isset($_SESSION['usuario']);

// Configuración de contenido por código
$config = [
    '403' => [
        'http'    => 403,
        'icon'    => 'fa-lock',
        'iconCls' => 'text-danger',
        'title'   => 'Acceso Denegado',
        'message' => 'No dispone de los permisos necesarios para acceder a este módulo.',
        'hint'    => 'Contacte con el administrador del sistema si considera que debería tener acceso.',
    ],
    '503' => [
        'http'    => 503,
        'icon'    => 'fa-screwdriver-wrench',
        'iconCls' => 'text-warning',
        'title'   => 'Sistema en Mantenimiento',
        'message' => 'El sistema se encuentra en mantenimiento en este momento.',
        'hint'    => 'Por favor, inténtelo más tarde. Disculpe las molestias.',
    ],
    '404' => [
        'http'    => 404,
        'icon'    => 'fa-compass',
        'iconCls' => 'text-info',
        'title'   => 'Página No Encontrada',
        'message' => 'La página o recurso solicitado no existe o ha sido movido.',
        'hint'    => 'Verifique la dirección o regrese al inicio.',
    ],
    '' => [
        'http'    => 500,
        'icon'    => 'fa-triangle-exclamation',
        'iconCls' => 'text-primary',
        'title'   => 'Error Inesperado',
        'message' => 'Ocurrió un error inesperado en el sistema.',
        'hint'    => 'Vuelva a intentarlo o contacte con el administrador.',
    ],
];

$entry = $config[$code] ?? $config[''];
$httpCode = $entry['http'];
http_response_code($httpCode);
$pageTitle = $entry['title'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?> - La Vicky</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6.4 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        html, body { height: 100%; margin: 0; padding: 0; }
        body.bakery-error-page {
            font-family: 'Outfit', sans-serif;
            min-height: 100vh;
            width: 100vw;
            background: linear-gradient(135deg, rgba(32,16,8,0.85) 0%, rgba(18,9,4,0.9) 100%),
                        url('../assets/img/bakery_bg.jpg') center center / cover no-repeat fixed;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            position: relative;
        }
        .error-screen-container {
            width: 100%;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1.5rem;
            position: relative;
            z-index: 2;
        }
        .error-card {
            width: 100%;
            max-width: 560px;
            text-align: center;
            color: #FFFDF9;
        }
        .error-icon-circle {
            width: 110px;
            height: 110px;
            margin: 0 auto 1.5rem;
            border-radius: 50%;
            background: rgba(201,149,69,0.15);
            border: 2px solid rgba(201,149,69,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-code {
            font-family: 'Cinzel', serif;
            font-size: 4.5rem;
            font-weight: 800;
            line-height: 1;
            color: #C99545;
            margin-bottom: .5rem;
        }
        .error-title {
            font-family: 'Cinzel', serif;
            font-size: 1.9rem;
            font-weight: 700;
            margin-bottom: .75rem;
        }
        .error-message {
            font-size: 1.05rem;
            color: #EDE4D5;
            margin-bottom: .5rem;
        }
        .error-hint {
            font-size: .92rem;
            color: #B9AB95;
            margin-bottom: 2rem;
        }
        .brand-emblem-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .6rem;
            margin-bottom: 2rem;
        }
        .brand-name {
            font-family: 'Cinzel', serif;
            font-weight: 700;
            font-size: 1.15rem;
            color: #FFFDF9;
        }
        .brand-sub {
            font-size: .6rem;
            letter-spacing: .3em;
            color: #C99545;
        }
    </style>
</head>
<body class="bakery-error-page">
    <div class="error-screen-container">
        <div class="error-card">
            <div class="brand-emblem-row">
                <i class="fas fa-wheat-awn text-warning fs-4"></i>
                <div>
                    <div class="brand-name">La Vicky</div>
                    <div class="brand-sub">PANADERÍA</div>
                </div>
            </div>

            <div class="error-icon-circle">
                <i class="fas <?php echo htmlspecialchars($entry['icon'], ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($entry['iconCls'], ENT_QUOTES, 'UTF-8'); ?>" style="font-size: 3rem;"></i>
            </div>

            <div class="error-code"><?php echo $httpCode; ?></div>
            <h1 class="error-title"><?php echo htmlspecialchars($entry['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="error-message"><?php echo htmlspecialchars($entry['message'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p class="error-hint"><?php echo htmlspecialchars($entry['hint'], ENT_QUOTES, 'UTF-8'); ?></p>

            <div class="d-flex justify-content-center flex-wrap gap-2">
                <?php if ($isLogged): ?>
                    <a href="index.php" class="btn btn-primary px-4 fw-bold">
                        <i class="fas fa-house me-2"></i> Volver al Inicio
                    </a>
                    <a href="#" onclick="logout(); return false;" class="btn btn-outline-light px-4 fw-bold">
                        <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary px-4 fw-bold">
                        <i class="fas fa-right-to-bracket me-2"></i> Iniciar Sesión
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        <?php if ($isLogged): ?>
        async function logout() {
            try {
                await fetch('../backend/api.php?route=logout');
            } catch (e) { /* ignore */ }
            window.location.href = 'login.php';
        }
        <?php endif; ?>
    </script>
</body>
</html>
