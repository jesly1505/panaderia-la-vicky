<?php
namespace App\Core;

/**
 * Guard de seguridad (RBAC).
 * Verifica la sesión y el rol del usuario antes de ejecutar un endpoint.
 * Ante una denegación por rol registra el intento en accesos_denegados.
 */
class Security {
    private $audit;

    public function __construct(AuditService $audit) {
        $this->audit = $audit;
    }

    public function isLoggedIn(): bool {
        return !empty($_SESSION['usuario_id']);
    }

    public function role(): ?string {
        return $_SESSION['rol'] ?? null;
    }

    /** True si el usuario actual posee el permiso indicado. */
    public function hasPermiso(string $permiso): bool {
        return in_array($permiso, $_SESSION['permisos'] ?? [], true);
    }

    /** Exige una sesión activa. Devuelve 401 JSON si no la hay. */
    public function requireLogin(): void {
        if ($this->isLoggedIn()) {
            return;
        }
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'No autenticado.']);
        exit;
    }

    /** Exige una sesión activa con uno de los roles permitidos. Devuelve 403 JSON si no. */
    public function requireRole(array $roles, string $modulo): void {
        $this->requireLogin();
        if (in_array($this->role(), $roles, true)) {
            return;
        }
        $this->audit->denied($modulo);
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Acceso denegado. No tienes permisos para esta acción.']);
        exit;
    }

    /** Exige una sesión activa con el permiso indicado. Devuelve 403 JSON si no. */
    public function requirePermiso(string $permiso, string $modulo): void {
        $this->requireLogin();
        if ($this->hasPermiso($permiso)) {
            return;
        }
        $this->audit->denied($modulo);
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Acceso denegado. No tienes permisos para esta acción.']);
        exit;
    }
}
