<?php
session_start();
// If logged in, redirect to index
if (isset($_SESSION['usuario_id']) || isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}
// Retrieve remembered email if set
$remembered_email = isset($_COOKIE['remember_email']) ? $_COOKIE['remember_email'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - La Vicky Panadería</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6.4 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom Style -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body.bakery-login-page {
            font-family: 'Outfit', sans-serif;
            min-height: 100vh;
            width: 100vw;
            background: linear-gradient(135deg, rgba(32, 16, 8, 0.82) 0%, rgba(18, 9, 4, 0.88) 100%),
                        url('../assets/img/bakery_bg.jpg') center center / cover no-repeat fixed;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            position: relative;
        }

        .login-screen-container {
            width: 100%;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1.5rem;
            position: relative;
            z-index: 2;
        }

        .login-grid {
            width: 100%;
            max-width: 1180px;
            margin: 0 auto;
        }

        /* -------------------------------------------------------------
           LEFT COLUMN: Bakery Branding
        ------------------------------------------------------------- */
        .bakery-branding {
            color: #FFFDF9;
            padding: 2rem 2.5rem;
        }

        .brand-emblem-wrapper {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: linear-gradient(135deg, #D49B44 0%, #8C4E18 100%);
            border: 2px solid rgba(255, 235, 205, 0.35);
            box-shadow: 0 10px 25px rgba(184, 115, 38, 0.4);
            color: #FFFDF9;
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
        }

        .brand-main-title {
            font-family: 'Cinzel', serif;
            font-size: 3.8rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            color: #FFFDF9;
            line-height: 1.1;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.6);
            margin-bottom: 0.25rem;
        }

        .brand-sub-title {
            font-size: 1.2rem;
            font-weight: 600;
            letter-spacing: 0.35em;
            color: #E2AD61;
            margin-bottom: 1.25rem;
        }

        .wheat-divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: #D49B44;
            font-size: 1.2rem;
            margin-bottom: 1.25rem;
        }

        .wheat-line {
            width: 50px;
            height: 1px;
            background: rgba(212, 155, 68, 0.6);
        }

        .brand-slogan {
            font-size: 1.2rem;
            color: #E5D5C3;
            line-height: 1.5;
            margin-bottom: 1.75rem;
            font-weight: 400;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
        }

        .artisan-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.45rem 1.25rem;
            background: rgba(212, 155, 68, 0.2);
            border: 1px solid rgba(212, 155, 68, 0.45);
            border-radius: 50px;
            color: #F3CA85;
            font-size: 0.88rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            backdrop-filter: blur(8px);
        }

        /* -------------------------------------------------------------
           RIGHT COLUMN: Floating Login Card
        ------------------------------------------------------------- */
        .login-card-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1rem;
        }

        .login-card {
            background: #FFFFFF;
            border-radius: 22px;
            border: 1px solid rgba(230, 215, 195, 0.7);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.45);
            width: 100%;
            max-width: 440px;
            position: relative;
            overflow: hidden;
            animation: cardFadeIn 0.6s ease-out forwards;
        }

        @keyframes cardFadeIn {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        .card-header-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: #F8EFE4;
            border: 1px solid #E5D2BE;
            color: #9E5B18;
            font-size: 1.75rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(158, 91, 24, 0.12);
            margin-bottom: 0.85rem;
        }

        .card-title-text {
            font-family: 'Cinzel', serif;
            font-size: 2rem;
            font-weight: 700;
            color: #3A2010;
            letter-spacing: 0.04em;
            margin-bottom: 0.2rem;
        }

        .card-subtitle-text {
            font-size: 0.88rem;
            color: #7A6250;
            font-weight: 500;
        }

        /* Form Labels & Inputs */
        .form-label-warm {
            font-size: 0.74rem;
            font-weight: 700;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            color: #4A2E1B;
            margin-bottom: 0.35rem;
        }

        .input-group-warm {
            background: #FDFBF8;
            border: 1.5px solid #E2D5C3;
            border-radius: 10px;
            transition: all 0.25s ease;
            overflow: hidden;
            min-height: 48px;
        }

        .input-group-warm:focus-within {
            background: #FFFFFF;
            border-color: #B87326;
            box-shadow: 0 0 0 3.5px rgba(184, 115, 38, 0.12);
        }

        .input-group-warm .input-group-text {
            background: transparent;
            border: none;
            color: #8C6E54;
            font-size: 0.95rem;
            padding-left: 1rem;
            padding-right: 0.75rem;
        }

        .input-group-warm .form-control {
            background: transparent;
            border: none;
            color: #2D1A0E;
            font-size: 0.95rem;
            font-weight: 500;
            padding: 0.7rem 0.5rem;
        }

        .input-group-warm .form-control:focus {
            box-shadow: none;
            background: transparent;
            color: #2D1A0E;
        }

        .input-group-warm .form-control::placeholder {
            color: #A89684;
            font-weight: 400;
        }

        .input-group-warm .password-toggle {
            cursor: pointer;
            color: #8C6E54;
            transition: color 0.2s ease;
        }

        .input-group-warm .password-toggle:hover {
            color: #4A2E1B;
        }

        .link-warm {
            color: #9E5B18;
            font-size: 0.82rem;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .link-warm:hover {
            color: #6E3806;
            text-decoration: underline !important;
        }

        .form-check-input-warm {
            border: 1.5px solid #C8B9A6;
            background-color: #FDFBF8;
            border-radius: 0.35rem;
            width: 1.15em;
            height: 1.15em;
            cursor: pointer;
        }

        .form-check-input-warm:checked {
            background-color: #9E5B18;
            border-color: #9E5B18;
        }

        .form-check-input-warm:focus {
            box-shadow: 0 0 0 3px rgba(184, 115, 38, 0.15);
            border-color: #9E5B18;
        }

        .form-check-label-warm {
            color: #4A2E1B;
            font-weight: 500;
            font-size: 0.88rem;
            cursor: pointer;
        }

        /* Submit Button */
        .btn-bakery {
            background: linear-gradient(135deg, #B87326 0%, #8C4E18 100%);
            border: none;
            color: #FFFDF9;
            border-radius: 10px;
            min-height: 48px;
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: 0.03em;
            box-shadow: 0 6px 16px rgba(140, 78, 24, 0.3);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
        }

        .btn-bakery::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.22), transparent);
            transition: all 0.6s ease;
        }

        .btn-bakery:hover {
            background: linear-gradient(135deg, #CA8232 0%, #9C581E 100%);
            color: #FFFFFF;
            transform: translateY(-2px);
            box-shadow: 0 10px 22px rgba(140, 78, 24, 0.4);
        }

        .btn-bakery:hover::before {
            left: 100%;
        }

        .btn-bakery:active {
            transform: translateY(0);
            box-shadow: 0 3px 8px rgba(140, 78, 24, 0.3);
        }

        .border-warm {
            border-color: #EFE6DA !important;
        }

        /* Loader */
        .loader-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 253, 249, 0.88);
            backdrop-filter: blur(6px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            border-radius: 22px;
        }

        .spinner-warm {
            color: #9E5B18;
            width: 2.6rem;
            height: 2.6rem;
        }

        /* Toast */
        .toast {
            background: #2D1A0E;
            border: 1px solid rgba(212, 155, 68, 0.3);
            color: #FFFDF9;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
            border-radius: 10px;
        }
        .toast-header {
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        @media (max-width: 991.98px) {
            .bakery-branding {
                text-align: center;
                padding: 1.5rem 1rem;
            }
            .brand-main-title {
                font-size: 2.8rem;
            }
            .wheat-divider {
                justify-content: center;
            }
        }
    </style>
</head>
<body class="bakery-login-page">
    <div class="login-screen-container">
        <div class="login-grid">
            <div class="row align-items-center g-4 g-lg-5">
                <!-- Left Column: Bakery Branding -->
                <div class="col-lg-6 d-none d-lg-block">
                    <div class="bakery-branding animate-fade-in">
                        <div class="brand-emblem-wrapper">
                            <i class="fas fa-bread-slice"></i>
                        </div>
                        <h1 class="brand-main-title">LA VICKY</h1>
                        <div class="brand-sub-title">PANADERÍA</div>
                        <div class="wheat-divider">
                            <span class="wheat-line"></span>
                            <i class="fas fa-wheat-awn"></i>
                            <span class="wheat-line"></span>
                        </div>
                        <p class="brand-slogan">
                            Gestión de Panadería Profesional
                        </p>
                        <div class="artisan-badge">
                            <i class="fas fa-certificate me-1"></i> Tradición & Calidad Artesanal
                        </div>
                    </div>
                </div>

                <!-- Right Column: Floating Login Card -->
                <div class="col-12 col-lg-6 login-card-wrapper">
                    <div class="login-card">
                        
                        <div class="loader-overlay" id="loginLoader">
                            <div class="spinner-border spinner-warm" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </div>

                        <div class="card-body p-4 p-sm-5">
                            <!-- Card Header -->
                            <div class="text-center mb-4">
                                <div class="card-header-icon">
                                    <i class="fas fa-bread-slice"></i>
                                </div>
                                <h2 class="card-title-text">LA VICKY</h2>
                                <p class="card-subtitle-text mb-0">Gestión de Panadería Profesional</p>
                            </div>

                            <!-- Login Form -->
                            <form id="loginForm">
                                <!-- Email -->
                                <div class="mb-3">
                                    <label for="email" class="form-label form-label-warm">CORREO ELECTRÓNICO</label>
                                    <div class="input-group input-group-warm">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" id="email" name="email" class="form-control" required placeholder="admin@lavicky.com" value="<?php echo htmlspecialchars($remembered_email); ?>">
                                    </div>
                                </div>

                                <!-- Password -->
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label for="password" class="form-label form-label-warm mb-0">CONTRASEÑA</label>
                                        <a href="forgot_password.php" class="small text-decoration-none link-warm">¿Olvidaste tu contraseña?</a>
                                    </div>
                                    <div class="input-group input-group-warm">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" id="password" name="password" class="form-control" required minlength="6" placeholder="******">
                                        <span class="input-group-text password-toggle" id="togglePassword" title="Mostrar/ocultar contraseña"><i class="fas fa-eye"></i></span>
                                    </div>
                                </div>

                                <!-- Remember Me -->
                                <div class="mb-4 form-check d-flex align-items-center gap-2">
                                    <input type="checkbox" class="form-check-input form-check-input-warm mt-0" id="remember" name="remember" <?php echo !empty($remembered_email) ? 'checked' : ''; ?>>
                                    <label class="form-check-label form-check-label-warm" for="remember">Recordarme</label>
                                </div>

                                <!-- Submit Button -->
                                <div class="d-grid mb-4">
                                    <button type="submit" class="btn btn-bakery btn-lg shadow-sm" id="btnSubmit">
                                        <i class="fas fa-sign-in-alt me-2"></i> Iniciar Sesión
                                    </button>
                                </div>
                            </form>

                            <!-- Footer / Copyright -->
                            <div class="text-center pt-3 border-top border-warm">
                                <p class="small text-muted mb-0">&copy; <?php echo date('Y'); ?> Sistema Integral La Vicky</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
        <div id="loginToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-danger text-white" id="toastHeader">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong class="me-auto" id="toastTitle">Error</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toastBody"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const pwdInput = document.getElementById('password');
            const icon = this.querySelector('i');
            if (pwdInput.type === 'password') {
                pwdInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                pwdInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const remember = document.getElementById('remember').checked;
            
            // Validaciones
            if (!email || !password) {
                showToast('Error', 'Por favor complete todos los campos.', 'bg-danger');
                return;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showToast('Error', 'Formato de correo electrónico inválido.', 'bg-danger');
                return;
            }

            const loader = document.getElementById('loginLoader');
            const btnSubmit = document.getElementById('btnSubmit');
            
            loader.style.display = 'flex';
            btnSubmit.disabled = true;

            const formData = new FormData();
            formData.append('email', email);
            formData.append('password', password);
            formData.append('remember', remember ? 1 : 0);

            fetch('../backend/api.php?route=login', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Éxito', 'Iniciando sesión...', 'bg-success');
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 500);
                } else {
                    loader.style.display = 'none';
                    btnSubmit.disabled = false;
                    showToast('Error', data.message || 'Error al iniciar sesión.', 'bg-danger');
                }
            })
            .catch(error => {
                loader.style.display = 'none';
                btnSubmit.disabled = false;
                showToast('Error', 'Error de conexión con el servidor.', 'bg-danger');
                console.error('Error:', error);
            });
        });

        function showToast(title, message, bgClass) {
            const toastEl = document.getElementById('loginToast');
            const toastHeader = document.getElementById('toastHeader');
            const toastTitle = document.getElementById('toastTitle');
            const toastBody = document.getElementById('toastBody');
            
            toastHeader.className = `toast-header text-white ${bgClass}`;
            toastTitle.innerText = title;
            toastBody.innerText = message;
            
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        }

        // Mostrar errores desde la URL si hay redirect
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('error')) {
            const errorType = urlParams.get('error');
            let msg = 'Ocurrió un error al intentar iniciar sesión.';
            if (errorType === 'credenciales') msg = 'Correo o contraseña incorrectos.';
            else if (errorType === 'campos_vacios') msg = 'Por favor, complete todos los campos.';
            else if (errorType === 'bloqueado') msg = 'Cuenta bloqueada temporalmente por intentos fallidos.';
            else if (errorType === 'sesion') msg = 'Debe iniciar sesión para acceder.';
            
            showToast('Error', msg, 'bg-danger');
            
            // Clean url
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    </script>
</body>
</html>
