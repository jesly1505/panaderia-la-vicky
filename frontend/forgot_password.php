<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - La Vicky</title>
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
                            <span class="visually-hidden">Enviando...</span>
                        </div>
                    </div>

                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <div class="bg-primary-light d-inline-flex align-items-center justify-content-center rounded-circle p-3 mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-key text-primary fs-1"></i>
                            </div>
                            <h3 class="fw-bold text-dark">Recuperar Contraseña</h3>
                            <p class="text-muted small">Ingresa tu correo electrónico y te enviaremos instrucciones para restablecer tu contraseña.</p>
                        </div>
                        
                        <div id="successMsg" class="alert alert-success shadow-sm py-2 px-3 small mb-4" style="display: none;">
                            <i class="fas fa-check-circle me-2"></i> Instrucciones enviadas. Revisa tu bandeja de entrada.
                        </div>

                        <form id="forgotForm">
                            <div class="mb-4">
                                <label for="email" class="form-label fw-semibold small text-uppercase text-muted">Correo Electrónico</label>
                                <div class="input-group">
                                    <span class="input-group-text text-muted"><i class="fas fa-envelope"></i></span>
                                    <input type="email" id="email" name="email" class="form-control" required placeholder="admin@lavicky.com">
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 mb-3">
                                <button type="submit" class="btn btn-primary btn-lg shadow-sm" id="btnSubmit">
                                    <i class="fas fa-paper-plane me-2"></i> Enviar Instrucciones
                                </button>
                            </div>
                            <div class="text-center">
                                <a href="login.php" class="text-decoration-none small text-muted"><i class="fas fa-arrow-left me-1"></i> Volver al Login</a>
                            </div>
                        </form>
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
        document.getElementById('forgotForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value.trim();
            
            if (!email) {
                showToast('Error', 'Por favor ingrese su correo.', 'bg-danger');
                return;
            }

            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                showToast('Error', 'Por favor ingrese un correo válido.', 'bg-danger');
                return;
            }

            const loader = document.getElementById('loginLoader');
            const btnSubmit = document.getElementById('btnSubmit');
            
            loader.style.display = 'flex';
            btnSubmit.disabled = true;

            const formData = new FormData();
            formData.append('email', email);

            fetch('../backend/api.php?route=forgot_password', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                loader.style.display = 'none';
                if (data.success) {
                    document.getElementById('forgotForm').style.display = 'none';
                    document.getElementById('successMsg').style.display = 'block';
                    
                    // Solo para propósitos de prueba local sin envío de correos
                    if(data.dev_token) {
                        const testLink = `reset_password.php?token=${data.dev_token}`;
                        document.getElementById('successMsg').innerHTML += `<br><br><small><i>Modo local: <a href="${testLink}">Enlace de restablecimiento de prueba</a></i></small>`;
                    }
                } else {
                    btnSubmit.disabled = false;
                    showToast('Error', data.message, 'bg-danger');
                }
            })
            .catch(error => {
                loader.style.display = 'none';
                btnSubmit.disabled = false;
                showToast('Error', 'Error de conexión con el servidor.', 'bg-danger');
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
    </script>
</body>
</html>
