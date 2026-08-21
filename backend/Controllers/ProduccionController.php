<?php
namespace App\Controllers;

use App\Core\AuditService;
use App\Core\Interfaces\InsumoRepositoryInterface;
use App\Helpers\UnitConverter;
use App\Models\ProduccionModel;
use App\Utils\Logger;

class ProduccionController {
    private $model;
    private $insumoModel;
    private $audit;

    public function __construct(ProduccionModel $model, InsumoRepositoryInterface $insumoModel, AuditService $audit) {
        $this->model = $model;
        $this->insumoModel = $insumoModel;
        $this->audit = $audit;
    }

    public function getAll(): void {
        header('Content-Type: application/json');
        $filter = $_GET['filter'] ?? 'all';
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';

        $data = $this->model->getAll($filter, $startDate, $endDate);
        echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data) {
            echo json_encode(['success' => false, 'message' => 'Faltan datos de producción.']);
            return;
        }

        $producto_id        = $data['producto_id'] ?? 0;
        $cantidad_producida = $data['cantidad_producida'] ?? 0;
        $insumos            = $data['insumos_usados'] ?? $data['insumos'] ?? [];

        if (empty($producto_id) || $cantidad_producida <= 0) {
            echo json_encode(['success' => false, 'message' => 'El campo Cantidad debe ser mayor que 0.']);
            return;
        }

        if (empty($insumos)) {
            echo json_encode(['success' => false, 'message' => 'Debes agregar al menos un insumo para la producción.']);
            return;
        }

        $insumoModel = $this->insumoModel;

        // Sanitizar y convertir array de insumos (filtrar vacíos/viciosos)
        $insumos_validos = [];
        try {
            foreach ($insumos as $ins) {
                if (!empty($ins['insumo_id']) && isset($ins['cantidad_usada']) && $ins['cantidad_usada'] > 0) {
                    $infoInsumo = $insumoModel->getById($ins['insumo_id']);
                    if (!$infoInsumo) {
                        throw new \Exception("Insumo no encontrado.");
                    }

                    $unidad_usada = $ins['unidad_usada'] ?? null;
                    $unidad_base = $infoInsumo['unidad_medida'];

                    if ($unidad_usada && $unidad_usada !== $unidad_base) {
                        $cantidad_convertida = UnitConverter::convert($ins['cantidad_usada'], $unidad_usada, $unidad_base);
                    } else {
                        $cantidad_convertida = (float)$ins['cantidad_usada'];
                    }

                    $insumos_validos[] = [
                        'insumo_id' => (int)$ins['insumo_id'],
                        'cantidad_usada' => $cantidad_convertida,
                    ];
                }
            }
        } catch (Exception $e) {
            Logger::error($e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al procesar la conversión de unidades.']);
            return;
        }

        if (empty($insumos_validos)) {
            echo json_encode(['success' => false, 'message' => 'Las cantidades de insumos deben ser mayores a 0.']);
            return;
        }

        $result = $this->model->create($producto_id, $cantidad_producida, $insumos_validos);

        if ($result === true) {
            $this->audit->log('Producción', 'Producción manual', "Producción manual de {$cantidad_producida} unidades del producto ID {$producto_id}.");
            echo json_encode(['success' => true, 'message' => 'Producción manual registrada correctamente.']);
        } elseif (isset($result['insuficiente'])) {
            $msg = "No hay stock suficiente de: " . implode(", ", $result['insuficiente']);
            echo json_encode(['success' => false, 'message' => $msg]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al registrar la producción.']);
        }
    }
}
?>
