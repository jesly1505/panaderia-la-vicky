<?php
namespace App\Core;

/**
 * Router ligero basado en el parámetro ?route=.
 * Cada ruta apunta a [Controlador, método] y se resuelve a través del Container.
 *
 * Control de acceso por ruta:
 *   - $roles   = null + $permiso = null  -> ruta pública (no requiere sesión)
 *   - $roles   = []                      -> cualquier usuario autenticado
 *   - $roles   = [nombres]               -> solo los roles indicados (RBAC, legacy)
 *   - $permiso = 'codigo'                -> solo usuarios con ese permiso (RBAC por permiso)
 *      Si ambos se definen, gana el permiso ($permiso).
 *
 * Métodos HTTP permitidos ($methods):
 *   - null -> cualquier método (compatibilidad)
 *   - ['GET'], ['POST'], ['GET','POST'] -> se valida y responde 405 si no coincide.
 */
class Router {
    private array $routes = [];

    public function register(string $route, string $controller, string $method, bool $json = true, ?array $roles = null, ?array $methods = null, ?string $permiso = null): void {
        $this->routes[$route] = [
            'controller' => $controller,
            'method'     => $method,
            'json'       => $json,
            'roles'      => $roles,
            'methods'    => $methods,
            'permiso'    => $permiso,
        ];
    }

    public function dispatch(?string $route, Container $container): void {
        $route = $route ?? '';

        if (!isset($this->routes[$route])) {
            http_response_code(404);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Endpoint no válido o no especificado.']);
            return;
        }

        $definition = $this->routes[$route];

        // Validación de método HTTP.
        // HEAD se trata como GET (RFC 9110): mismo comportamiento sin cuerpo.
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        if ($requestMethod === 'HEAD') {
            $requestMethod = 'GET';
        }
        if ($definition['methods'] !== null && !in_array($requestMethod, $definition['methods'], true)) {
            http_response_code(405);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'Método no permitido. Se esperaba: ' . implode('/', $definition['methods']) . '.',
            ]);
            return;
        }

        if ($definition['json']) {
            header('Content-Type: application/json; charset=utf-8');
        }

        // Middleware de seguridad (RBAC)
        if ($definition['permiso'] !== null) {
            $security = $container->get(Security::class);
            $security->requirePermiso($definition['permiso'], $route);
        } elseif ($definition['roles'] !== null) {
            $security = $container->get(Security::class);
            if ($definition['roles'] === []) {
                $security->requireLogin();
            } else {
                $security->requireRole($definition['roles'], $route);
            }
        }

        $controller = $container->get($definition['controller']);
        call_user_func([$controller, $definition['method']]);
    }
}
