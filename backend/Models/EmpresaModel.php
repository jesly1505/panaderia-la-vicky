<?php
namespace App\Models;

use PDO;

class EmpresaModel {
    private $conn;

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    /** Garantiza que exista la fila única (id = 1) del perfil del negocio. */
    private function ensureRow(): void {
        $this->conn->exec("INSERT IGNORE INTO empresa (id, nombre) VALUES (1, 'Panadería La Vicky')");
    }

    /** Perfil de la panadería (datos que se muestran en la factura). */
    public function getPerfil(): array {
        $this->ensureRow();
        $stmt = $this->conn->query("SELECT id, nombre, descripcion, direccion, telefono, ruc, moneda, tasa_impuesto
                                    FROM empresa WHERE id = 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: [];
    }

    /** Actualiza el perfil del negocio. Devuelve true si todo fue OK. */
    public function updatePerfil(array $d): bool {
        $this->ensureRow();
        $sql = "UPDATE empresa
                SET nombre = :nombre, descripcion = :descripcion, direccion = :direccion,
                    telefono = :telefono, ruc = :ruc, moneda = :moneda, tasa_impuesto = :tasa_impuesto
                WHERE id = 1";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':nombre' => $d['nombre'],
            ':descripcion' => $d['descripcion'],
            ':direccion' => $d['direccion'],
            ':telefono' => $d['telefono'],
            ':ruc' => $d['ruc'],
            ':moneda' => $d['moneda'],
            ':tasa_impuesto' => $d['tasa_impuesto'],
        ]);
    }
}
