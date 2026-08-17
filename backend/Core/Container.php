<?php
namespace App\Core;

/**
 * Contenedor de dependencias minimalista con auto-wiring por reflexión.
 * - Los bindings con factories tienen prioridad (necesarios para interfaces y PDO).
 * - Las clases concretas se resuelven automáticamente inyectando sus dependencias tipadas.
 * - Todas las dependencias se resuelven como singletons (misma instancia).
 */
class Container {
    private array $bindings = [];
    private array $instances = [];

    public function set(string $abstract, callable $factory): void {
        $this->bindings[$abstract] = $factory;
    }

    public function get(string $abstract) {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }
        if (isset($this->bindings[$abstract])) {
            return $this->instances[$abstract] = ($this->bindings[$abstract])($this);
        }
        if (!class_exists($abstract)) {
            throw new \RuntimeException("No se pudo resolver la dependencia: {$abstract}");
        }

        $reflection = new \ReflectionClass($abstract);
        if ($reflection->isInterface() || $reflection->isAbstract()) {
            throw new \RuntimeException("No hay binding registrado para la abstracción: {$abstract}");
        }

        $constructor = $reflection->getConstructor();
        if ($constructor === null) {
            return $this->instances[$abstract] = $reflection->newInstance();
        }

        $args = [];
        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();
            if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                $args[] = $this->get($type->getName());
            } elseif ($param->isOptional()) {
                break;
            } else {
                throw new \RuntimeException("No se puede resolver el parámetro \${$param->getName()} de {$abstract}");
            }
        }

        return $this->instances[$abstract] = $reflection->newInstanceArgs($args);
    }
}
