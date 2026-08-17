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
    <title>Iniciar Sesión - La Vicky</title>
    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6.4 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom Style -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a, #1e293b, #334155);
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        /* Animated background blobs */
        body::before, body::after {
            content: '';
            position: absolute;
            width: 40vw;
            height: 40vw;
            border-radius: 50%;
            filter: blur(80px);
            z-index: 0;
            animation: float 10s infinite ease-in-out alternate;
        }
        body::before {
            background: rgba(56, 189, 248, 0.25);
            top: -10vw;
            left: -10vw;
        }
        body::after {
            background: rgba(139, 92, 246, 0.25);
            bottom: -10vw;
            right: -10vw;
            animation-delay: -5s;
        }

        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(50px, 50px) scale(1.1); }
        }

        .container {
            z-index: 1;
            position: relative;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.03); /* Glass */
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            border-left: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            overflow: hidden;
            animation: cardFadeIn 0.8s ease-out forwards;
        }

        @keyframes cardFadeIn {
            0% { opacity: 0; transform: translateY(30px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.6);
        }

        /* Override text colors for dark glass theme */
        .text-dark { color: #f8fafc !important; }
        .text-muted { color: #94a3b8 !important; }
        .form-label { letter-spacing: 0.05em; color: #cbd5e1 !important; }
        .card-body { position: relative; z-index: 2; }

        .bg-primary-light {
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.15), rgba(139, 92, 246, 0.15)) !important;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .bg-primary-light i {
            color: #38bdf8 !important;
            filter: drop-shadow(0 0 8px rgba(56, 189, 248, 0.5));
        }

        /* Inputs */
        .input-group {
            background: rgba(15, 23, 42, 0.4);
            border-radius: 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .input-group:focus-within {
            background: rgba(15, 23, 42, 0.6);
            border-color: rgba(56, 189, 248, 0.5);
            box-shadow: 0 0 15px rgba(56, 189, 248, 0.15);
        }

        .input-group-text {
            background: transparent !important;
            border: none !important;
            color: #94a3b8 !important;
        }

        .form-control {
            background: transparent !important;
            border: none !important;
            color: #f8fafc !important;
        }
        
        .form-control::placeholder {
            color: #475569 !important;
        }

        .form-control:focus {
            box-shadow: none !important;
            background: transparent !important;
            color: #f8fafc !important;
        }

        /* Button */
        .btn-primary {
            background: linear-gradient(135deg, #38bdf8, #8b5cf6);
            border: none;
            border-radius: 0.75rem;
            padding: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.025em;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0; left: -100%; width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: all 0.5s ease;
            z-index: -1;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(139, 92, 246, 0.4);
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        /* Checkbox */
        .form-check-input {
            background-color: rgba(15, 23, 42, 0.4);
            border-color: rgba(255, 255, 255, 0.2);
        }
        .form-check-input:checked {
            background-color: #8b5cf6;
            border-color: #8b5cf6;
        }

        /* Loader */
        .loader-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(5px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            display: none;
            border-radius: 1.5rem;
        }

        /* Toast */
        .toast {
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
        }
        .toast-header {
            background: rgba(0, 0, 0, 0.2) !important;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            color: white !important;
        }
        .password-toggle { cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-5">
                <div class="login-card position-relative animate-fade-in">
                    
                    <div class="loader-overlay" id="loginLoader">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>

                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <div class="bg-primary-light d-inline-flex align-items-center justify-content-center rounded-circle p-3 mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-bread-slice text-primary fs-1"></i>
                            </div>
                            <h2 class="fw-bold text-dark">La Vicky</h2>
                            <p class="text-muted">Gestión de Panadería Profesional</p>
                        </div>
                        
                        <form id="loginForm">
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold small text-uppercase text-muted">Correo Electrónico</label>
                                <div class="input-group">
                                    <span class="input-group-text text-muted"><i class="fas fa-envelope"></i></span>
                                    <input type="email" id="email" name="email" class="form-control" required placeholder="admin@lavicky.com" value="<?php echo htmlspecialchars($remembered_email); ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label for="password" class="form-label fw-semibold small text-uppercase text-muted mb-0">Contraseña</label>
                                    <a href="forgot_password.php" class="small text-decoration-none text-primary">¿Olvidaste tu contraseña?</a>
                                </div>
                                <div class="input-group mt-2">
                                    <span class="input-group-text text-muted"><i class="fas fa-lock"></i></span>
                                    <input type="password" id="password" name="password" class="form-control" required minlength="6" placeholder="******">
                                    <span class="input-group-text password-toggle text-muted" id="togglePassword"><i class="fas fa-eye"></i></span>
                                </div>
                            </div>

                            <div class="mb-4 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember" <?php echo !empty($remembered_email) ? 'checked' : ''; ?>>
                                <label class="form-check-label text-muted small" for="remember">Recordarme</label>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg shadow-sm" id="btnSubmit">
                                    <i class="fas fa-sign-in-alt me-2"></i> Iniciar Sesión
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            <p class="text-muted small mb-0">&copy; <?php echo date('Y'); ?> Sistema Integral La Vicky</p>
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
