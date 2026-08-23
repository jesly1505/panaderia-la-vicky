<?php
namespace App\Controllers;

use App\Core\AuditService;
use App\Core\Validator;
use App\Models\EmpresaModel;

class EmpresaController {
    private $empresaModel;
    private $audit;

    public function __construct(EmpresaModel $empresaModel, AuditService $audit) {
        $this->empresaModel = $empresaModel;
        $this->audit = $audit;
    }

    /** Perfil del negocio. */
    public function getPerfil() {
        header('Content-Type: application/json');
        $perfil = $this->empresaModel->getPerfil();
        echo json_encode(['success' => true, 'data' => $perfil]);
    }

    /** Actualiza el perfil del negocio (POST JSON). */
    public function updatePerfil() {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
            return;
        }

        $nombre = trim($data['nombre'] ?? '');
        $descripcion = trim($data['descripcion'] ?? '');
        $direccion = trim($data['direccion'] ?? '');
        $telefono = trim($data['telefono'] ?? '');
        $ruc = trim($data['ruc'] ?? '');
        $moneda = strtoupper(trim($data['moneda'] ?? ''));
        $tasa = $data['tasa_impuesto'] ?? '';

        $error = Validator::firstError([
            Validator::required($nombre, 'Nombre del negocio'),
            Validator::length($nombre, 100, 'Nombre del negocio'),
            Validator::length($descripcion, 255, 'Descripción', 0),
            Validator::length($direccion, 255, 'Dirección', 0),
            Validator::length($telefono, 30, 'Teléfono', 0),
            Validator::length($ruc, 30, 'RUC/NIT', 0),
            Validator::required($moneda, 'Moneda'),
            Validator::inList($moneda, ['USD', 'NIO', 'MXN', 'EUR'], 'Moneda'),
            Validator::required($tasa, 'Tasa de impuesto'),
            Validator::numeric($tasa, 'Tasa de impuesto'),
            Validator::min($tasa, 0, 'Tasa de impuesto'),
            Validator::max($tasa, 100, 'Tasa de impuesto'),
        ]);
        if ($error) {
            echo json_encode(['success' => false, 'message' => $error]);
            return;
        }

        $tasa = (float)round($tasa, 2);
        $normalize = function ($v) {
            return $v === '' ? null : $v;
        };

        $ok = $this->empresaModel->updatePerfil([
            'nombre' => $nombre,
            'descripcion' => $normalize($descripcion),
            'direccion' => $normalize($direccion),
            'telefono' => $normalize($telefono),
            'ruc' => $normalize($ruc),
            'moneda' => $moneda,
            'tasa_impuesto' => $tasa,
        ]);

        if ($ok) {
            $this->audit->log('Configuración', 'Perfil actualizado',
                "Perfil de la panadería actualizado ({$moneda}, impuesto {$tasa}%)");
            echo json_encode(['success' => true, 'message' => 'Perfil actualizado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar el perfil.']);
        }
    }
}
