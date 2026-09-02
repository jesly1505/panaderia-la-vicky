<?php

namespace App\Tests\Integration;

use App\Controllers\VentaController;
use App\Core\AuditService;
use App\Models\VentaModel;
use App\Models\ProductoModel;
use App\Utils\InventoryLogic;
use PHPUnit\Framework\TestCase;

/**
 * Tests de integración para VentaController.
 * Requiere conexión a la BD de prueba (la_vicky_db con seed.sql).
 */
class VentaControllerTest extends TestCase {
    private $conn;
    private $model;
    private $audit;

    protected function setUp(): void {
        $this->conn = (new \App\Core\Database())->getConnection();
        $productoModel = new ProductoModel($this->conn);
        $insumoModel = new \App\Models\InsumoModel($this->conn);
        $inventoryLogic = new InventoryLogic($productoModel, $insumoModel);
        $this->model = new VentaModel($this->conn, $inventoryLogic, $productoModel);
        $this->audit = new AuditService($this->conn);
        $_SESSION = [
            'usuario_id' => 1,
            'rol' => 'Administrador',
            'rol_id' => 1,
            'permisos' => ['ventas.ver', 'ventas.crear', 'ventas.editar'],
        ];
    }

    protected function tearDown(): void {
        $_SESSION = [];
        unset($_POST['venta_id'], $_POST['id']);
        unset($_SERVER['REQUEST_METHOD']);
    }

    public function testGetAllReturnsVentas(): void {
        $controller = new VentaController($this->model, $this->audit);
        ob_start();
        $controller->getAll();
        $output = ob_get_clean();
        $data = json_decode($output, true);

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['data']);
    }

    public function testGetTopProductsReturnsData(): void {
        $controller = new VentaController($this->model, $this->audit);
        ob_start();
        $controller->getTopProducts();
        $output = ob_get_clean();
        $data = json_decode($output, true);

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['data']);
    }

    public function testGetRevenueChartReturnsData(): void {
        $controller = new VentaController($this->model, $this->audit);
        $_GET['periodo'] = 'semanal';
        ob_start();
        $controller->getRevenueChart();
        $output = ob_get_clean();
        $data = json_decode($output, true);

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['data']);
        unset($_GET['periodo']);
    }

    public function testCancelWithoutIdReturnsValidationError(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        unset($_POST['venta_id'], $_POST['id']);
        $controller = new VentaController($this->model, $this->audit);
        ob_start();
        $controller->cancel();
        $output = ob_get_clean();
        $data = json_decode($output, true);

        $this->assertFalse($data['success']);
        $this->assertSame('El campo ID de venta debe ser un número entero.', $data['message']);
    }

    public function testCancelAcceptsVentaIdField(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        // El frontend envía { venta_id: id }; el controller debe aceptarlo (pasa validación de entero).
        // Usamos un id inexistente para probar la ruta completa sin mutar datos.
        $_POST['venta_id'] = '999999';
        $controller = new VentaController($this->model, $this->audit);
        ob_start();
        $controller->cancel();
        $output = ob_get_clean();
        $data = json_decode($output, true);

        // No debe ser el error de validación de ID entero (el bug reportado)
        $this->assertNotSame('El campo ID de venta debe ser un número entero.', $data['message']);
        // Debe pasar la validación y llegar hasta el modelo (venta inexistente => error de negocio, no validación)
        $this->assertSame('Error al cancelar la venta.', $data['message']);
    }
}
