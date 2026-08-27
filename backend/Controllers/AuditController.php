<?php
namespace App\Controllers;

use App\Models\AuditModel;

class AuditController {
    /** @var AuditModel */
    private $auditModel;

    /**
     * @param AuditModel $auditModel Modelo de auditoría inyectado.
     */
    public function __construct(AuditModel $auditModel) {
        $this->auditModel = $auditModel;
    }

    /**
     * Devuelve los últimos registros de bitácora del sistema.
     *
     * @return void Emite JSON con la lista de eventos.
     */
    public function getBitacora() {
        header('Content-Type: application/json');
        $limit = min((int)($_GET['limit'] ?? 100), 500);
        echo json_encode(['success' => true, 'data' => $this->auditModel->getBitacora($limit)]);
    }

    /**
     * Devuelve los últimos intentos de acceso denegado.
     *
     * @return void Emite JSON con la lista de accesos denegados.
     */
    public function getDenied() {
        header('Content-Type: application/json');
        $limit = min((int)($_GET['limit'] ?? 100), 500);
        echo json_encode(['success' => true, 'data' => $this->auditModel->getDeniedAccess($limit)]);
    }
}
