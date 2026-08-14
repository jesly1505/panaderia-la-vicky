<?php
namespace App\Controllers;

use App\Core\AuditService;
use App\Models\PermisoModel;
use App\Models\UserModel;

class AuthController {
    private $userModel;
    private $permisoModel;
    private $audit;

    public function __construct(UserModel $userModel, PermisoModel $permisoModel, AuditService $audit) {
        $this->userModel = $userModel;
        $this->permisoModel = $permisoModel;
        $this->audit = $audit;
    }

    public function login() {
        $this->handleLogin(false);
    }

    public function loginRedirect() {
        $this->handleLogin(true);
    }

    private function handleLogin($redirect = false) {
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($email) || empty($password)) {
            if ($redirect) {
                header("Location: ../frontend/login.html?error=campos_vacios");
                exit();
            }
            echo json_encode(['success' => false, 'message' => 'Por favor, complete todos los campos.']);
            return;
        }

        $usuario = $this->userModel->findByEmail($email);

        // Cuenta temporalmente bloqueada por intentos fallidos
        if ($usuario && !empty($usuario['bloqueado_hasta']) && strtotime($usuario['bloqueado_hasta']) > time()) {
            if ($redirect) {
                header("Location: ../frontend/login.html?error=bloqueado");
                exit();
            }
            echo json_encode(['success' => false, 'message' => 'Cuenta bloqueada temporalmente por demasiados intentos fallidos.']);
            return;
        }

        if ($usuario && $usuario['estado'] === 'activo' && password_verify($password, $usuario['password_hash'])) {
            $this->userModel->resetFailedAttempts($usuario['id']);
            $this->userModel->updateLastAccess($usuario['id']);
            session_regenerate_id(true); // Evita fijación de sesión

            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['rol'] = $usuario['rol_nombre'];
            $_SESSION['usuario'] = $usuario['nombre'];
            $_SESSION['permisos'] = $this->permisoModel->getPermisosByRol($usuario['rol_id']);

            $this->audit->log('Seguridad', 'Inicio de sesión', "Usuario logueado: {$usuario['email']}");

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
            $this->userModel->registerFailedAttempt($email);
            if ($redirect) {
                header("Location: ../frontend/login.html?error=credenciales");
                exit();
            }
            echo json_encode(['success' => false, 'message' => 'Correo o contraseña incorrectos.']);
        }
    }

    public function logout() {
        $this->audit->log('Seguridad', 'Cierre de sesión', "Cierre de sesión de: " . ($_SESSION['usuario'] ?? ''));
        session_unset();
        session_destroy();
        echo json_encode(['success' => true, 'message' => 'Sesión cerrada exitosamente']);
    }

    public function checkSession() {
        echo json_encode([
            'logged_in' => isset($_SESSION['usuario_id']),
            'usuario' => $_SESSION['usuario'] ?? null,
            'user' => [
                'id' => $_SESSION['usuario_id'] ?? null,
                'nombre' => $_SESSION['nombre'] ?? null,
                'rol' => $_SESSION['rol'] ?? null
            ],
            'permisos' => $_SESSION['permisos'] ?? []
        ]);
    }
}
