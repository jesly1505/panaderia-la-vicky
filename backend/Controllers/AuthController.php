<?php
namespace App\Controllers;

use App\Core\AuditService;
use App\Models\PermisoModel;
use App\Models\UserModel;

class AuthController {
    private UserModel $userModel;
    private PermisoModel $permisoModel;
    private AuditService $audit;

    public function __construct(UserModel $userModel, PermisoModel $permisoModel, AuditService $audit) {
        $this->userModel = $userModel;
        $this->permisoModel = $permisoModel;
        $this->audit = $audit;
    }

    public function login(): void {
        $this->handleLogin(false);
    }

    public function loginRedirect(): void {
        $this->handleLogin(true);
    }

    private function handleLogin(bool $redirect = false): void {
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($email) || empty($password)) {
            if ($redirect) {
                header("Location: ../frontend/login.php?error=campos_vacios");
                exit();
            }
            echo json_encode(['success' => false, 'message' => 'Por favor, complete todos los campos.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $usuario = $this->userModel->findByEmail($email);

        // Cuenta temporalmente bloqueada por intentos fallidos
        if ($usuario && !empty($usuario['bloqueado_hasta']) && strtotime($usuario['bloqueado_hasta']) > time()) {
            if ($redirect) {
                header("Location: ../frontend/login.php?error=bloqueado");
                exit();
            }
            echo json_encode(['success' => false, 'message' => 'Cuenta bloqueada temporalmente por demasiados intentos fallidos.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        if ($usuario && $usuario['estado'] === 'activo' && password_verify($password, $usuario['password_hash'])) {
            $this->userModel->resetFailedAttempts($usuario['id']);
            $this->userModel->updateLastAccess($usuario['id']);
            
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            session_regenerate_id(true); // Evita fijación de sesión

            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['rol'] = $usuario['rol_nombre'];
            $_SESSION['usuario'] = $usuario['nombre'];
            $_SESSION['permisos'] = $this->permisoModel->getPermisosByRol($usuario['rol_id']);

            $this->audit->log('Seguridad', 'Inicio de sesión', "Usuario logueado: {$usuario['email']}");

            // Manejo de cookie "Recordarme"
            if (!empty($_POST['remember'])) {
                setcookie('remember_email', $email, time() + (30 * 24 * 60 * 60), '/', '', false, true);
            } else {
                setcookie('remember_email', '', time() - 3600, '/');
            }

            if ($redirect) {
                header("Location: ../frontend/index.php");
                exit();
            }

            echo json_encode([
                'success' => true,
                'message' => 'Login exitoso',
                'user' => ['nombre' => $usuario['nombre'], 'rol' => $usuario['rol_nombre']]
            ], JSON_UNESCAPED_UNICODE);
        } else {
            $this->userModel->registerFailedAttempt($email);
            $this->audit->log('Seguridad', 'Intento de login fallido', "Email: $email");

            if ($redirect) {
                header("Location: ../frontend/login.php?error=credenciales");
                exit();
            }
            echo json_encode(['success' => false, 'message' => 'Correo o contraseña incorrectos.'], JSON_UNESCAPED_UNICODE);
        }
    }

    public function logout(): void {
        $this->audit->log('Seguridad', 'Cierre de sesión', "Cierre de sesión de: " . ($_SESSION['usuario'] ?? ''));
        session_unset();
        session_destroy();
        echo json_encode(['success' => true, 'message' => 'Sesión cerrada exitosamente'], JSON_UNESCAPED_UNICODE);
    }

    public function checkSession(): void {
        echo json_encode([
            'logged_in' => isset($_SESSION['usuario_id']),
            'usuario' => $_SESSION['usuario'] ?? null,
            'user' => [
                'id' => $_SESSION['usuario_id'] ?? null,
                'nombre' => $_SESSION['nombre'] ?? null,
                'rol' => $_SESSION['rol'] ?? null
            ],
            'permisos' => $_SESSION['permisos'] ?? []
        ], JSON_UNESCAPED_UNICODE);
    }

    public function forgotPassword(): void {
        $email = trim($_POST['email'] ?? '');
        if (empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Por favor ingrese su correo electrónico.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $usuario = $this->userModel->findByEmail($email);
        if ($usuario) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $this->userModel->setResetToken($email, $token, $expires);

            $this->audit->log('Seguridad', 'Recuperación de contraseña solicitada', "Token generado para $email");

            echo json_encode([
                'success' => true,
                'message' => 'Si el correo está registrado, se han generado las instrucciones de recuperación.'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'success' => true,
                'message' => 'Si el correo está registrado, se han generado las instrucciones de recuperación.'
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function resetPassword(): void {
        $token = trim($_POST['token'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($token) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $usuario = $this->userModel->findByResetToken($token);
        if (!$usuario) {
            echo json_encode(['success' => false, 'message' => 'El token es inválido o ha expirado.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        if ($this->userModel->updatePassword($usuario['id'], $password)) {
            $this->audit->log('Seguridad', 'Contraseña restablecida', "El usuario {$usuario['email']} ha actualizado su contraseña");
            echo json_encode(['success' => true, 'message' => 'Contraseña actualizada exitosamente. Ya puede iniciar sesión.'], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar la contraseña.'], JSON_UNESCAPED_UNICODE);
        }
    }
}
