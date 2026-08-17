<?php

namespace App\Tests\Unit;

use App\Core\AuditService;
use App\Core\Security;
use PHPUnit\Framework\TestCase;

class SecurityTest extends TestCase {

    protected function setUp(): void {
        $_SESSION = [];
    }

    protected function tearDown(): void {
        $_SESSION = [];
    }

    private function makeSecurity(): Security {
        $audit = $this->createStub(AuditService::class);
        return new Security($audit);
    }

    public function testNotLoggedInByDefault(): void {
        $this->assertFalse($this->makeSecurity()->isLoggedIn());
    }

    public function testRoleIsNullByDefault(): void {
        $this->assertNull($this->makeSecurity()->role());
    }

    public function testIsLoggedInWithSession(): void {
        $_SESSION['usuario_id'] = 1;
        $_SESSION['rol'] = 'Administrador';
        $security = $this->makeSecurity();
        $this->assertTrue($security->isLoggedIn());
        $this->assertSame('Administrador', $security->role());
    }

    public function testRoleWithNoSession(): void {
        $_SESSION['usuario_id'] = 1;
        $this->assertNull($this->makeSecurity()->role());
    }

    public function testLoggedInButNoUsuarioId(): void {
        $_SESSION['rol'] = 'Cajero';
        $this->assertFalse($this->makeSecurity()->isLoggedIn());
    }
}
