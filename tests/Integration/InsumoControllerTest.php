<?php

namespace App\Tests\Integration;

use App\Controllers\InsumoController;
use App\Core\AuditService;
use App\Models\InsumoModel;
use PHPUnit\Framework\TestCase;

/**
 * Tests de integración para InsumoController.
 * Requiere conexión a la BD de prueba (la_vicky_db con seed.sql).
 */
class InsumoControllerTest extends TestCase {
    private $conn;
    private $model;
    private $audit;

    protected function setUp(): void {
        $this->conn = (new \App\Core\Database())->getConnection();
        $this->model = new InsumoModel($this->conn);
        $this->audit = new AuditService($this->conn);
        $_SESSION = [
            'usuario_id' => 1,
            'rol' => 'Administrador',
            'rol_id' => 1,
            'permisos' => ['inventario.ver', 'inventario.crear', 'inventario.editar', 'inventario.eliminar'],
        ];
    }

    protected function tearDown(): void {
        $_SESSION = [];
    }

    public function testGetAllReturnsInsumos(): void {
        $controller = new InsumoController($this->model, $this->audit);
        ob_start();
        $controller->getAll();
        $output = ob_get_clean();
        $data = json_decode($output, true);

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['data']);
        $this->assertNotEmpty($data['data']);
    }

    public function testAddInsumoCreatesRecord(): void {
        $testName = 'Insumo Test Int ' . time();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'nombre' => $testName,
            'unidad_medida' => 'Kg',
            'proveedor_id' => '',
            'stock_inicial' => '10.00',
            'stock_minimo' => '2.00',
            'precio_costo' => '25.00',
        ];

        $controller = new InsumoController($this->model, $this->audit);
        ob_start();
        $controller->add();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertTrue($response['success']);

        $check = $this->conn->prepare("SELECT * FROM insumos WHERE nombre = :n");
        $check->execute([':n' => $testName]);
        $row = $check->fetch(\PDO::FETCH_ASSOC);
        $this->assertNotEmpty($row);
        $this->assertSame('Kg', $row['unidad_medida']);

        // Cleanup
        $this->conn->exec("DELETE FROM insumos WHERE nombre = " . $this->conn->quote($testName));
        unset($_POST, $_SERVER['REQUEST_METHOD']);
    }
}
