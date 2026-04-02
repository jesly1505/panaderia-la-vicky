<?php
require_once __DIR__ . '/../Models/UserModel.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    public function login() {
        // ... (existing AJAX login remains as is)
        $this->handleLogin(false);
    }

    public function loginRedirect() {
        $this->handleLogin(true);
    }

    private function handleLogin($redirect = false) {
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        // LOG PARA DEPURACIÓN
        error_log("Login attempt - Email: '$email', Password: '" . (empty($password) ? 'EMPTY' : 'PROVIDED') . "'");

        if (empty($email) || empty($password)) {
            if ($redirect) {
                header("Location: ../frontend/login.html?error=campos_vacios");
                exit();
            }
            echo json_encode(['success' => false, 'message' => 'Por favor, complete todos los campos.']);
            return;
        }

        $usuario = $this->userModel->findByEmail($email);

        if ($usuario && $usuario['estado'] === 'activo' && password_verify($password, $usuario['password_hash'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['rol'] = $usuario['rol_nombre'];
            $_SESSION['usuario'] = $usuario['nombre'];

            if ($redirect) {
                header("Location: ../frontend/index.php");
                exit();
            }

            echo json_encode([
                'success' => true, 
                'message' => 'Login exitoso',
                'user' => ['nombre' => $usuario['nombre'], 'rol' => $usuario['rol_nombre']]
            ]);
        } else {
            if ($redirect) {
                header("Location: ../frontend/login.html?error=credenciales");
                exit();
            }
            echo json_encode(['success' => false, 'message' => 'Correo o contraseña incorrectos.']);
        }
    }

    public function logout() {
        session_destroy();
        echo json_encode(['success' => true, 'message' => 'Sesión cerrada exitosamente']);
    }
}
