<?php
namespace App\Controllers;

use App\Models\AuditModel;

class AuditController {
    private $auditModel;

    public function __construct(AuditModel $auditModel) {
        $this->auditModel = $auditModel;
    }

    public function getBitacora() {
        $limit = min((int)($_GET['limit'] ?? 100), 500);
        echo json_encode(['success' => true, 'data' => $this->auditModel->getBitacora($limit)]);
    }

    public function getDenied() {
        $limit = min((int)($_GET['limit'] ?? 100), 500);
        echo json_encode(['success' => true, 'data' => $this->auditModel->getDeniedAccess($limit)]);
    }
}
