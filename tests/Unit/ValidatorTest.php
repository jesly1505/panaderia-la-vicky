<?php

namespace App\Tests\Unit;

use App\Core\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase {

    public function testRequiredFailsOnEmpty(): void {
        $this->assertNotNull(Validator::required('', 'Nombre'));
        $this->assertNotNull(Validator::required('   ', 'Nombre'));
        $this->assertNotNull(Validator::required(null, 'Nombre'));
    }

    public function testRequiredPasses(): void {
        $this->assertNull(Validator::required('Pan', 'Nombre'));
    }

    public function testNumeric(): void {
        $this->assertNull(Validator::numeric('10.5', 'Monto'));
        $this->assertNull(Validator::numeric(7, 'Monto'));
        $this->assertNotNull(Validator::numeric('abc', 'Monto'));
        $this->assertNotNull(Validator::numeric(null, 'Monto'));
    }

    public function testInteger(): void {
        $this->assertNull(Validator::integer(3, 'ID'));
        $this->assertNull(Validator::integer('42', 'ID'));
        $this->assertNull(Validator::integer('3.0', 'ID'));
        $this->assertNotNull(Validator::integer(3.5, 'ID'));
        $this->assertNotNull(Validator::integer('abc', 'ID'));
    }

    public function testGreaterThan(): void {
        $this->assertNull(Validator::greaterThan(10, 0, 'Monto'));
        $this->assertNotNull(Validator::greaterThan(0, 0, 'Monto'));
        $this->assertNotNull(Validator::greaterThan(-1, 0, 'Monto'));
        $this->assertNotNull(Validator::greaterThan('x', 0, 'Monto'));
    }

    public function testMin(): void {
        $this->assertNull(Validator::min(0, 0, 'Stock'));
        $this->assertNull(Validator::min(5, 0, 'Stock'));
        $this->assertNotNull(Validator::min(-1, 0, 'Stock'));
    }

    public function testEmail(): void {
        $this->assertNull(Validator::email('a@b.com', 'Email'));
        $this->assertNull(Validator::email('', 'Email'));
        $this->assertNull(Validator::email(null, 'Email'));
        $this->assertNotNull(Validator::email('no-es-correo', 'Email'));
    }

    public function testLength(): void {
        $this->assertNull(Validator::length('pan', 100, 'Nombre'));
        $this->assertNotNull(Validator::length('', 100, 'Nombre'));
        $this->assertNotNull(Validator::length(str_repeat('a', 101), 100, 'Nombre'));
        $this->assertNull(Validator::length('', 20, 'Teléfono', 0));
    }

    public function testInList(): void {
        $this->assertNull(Validator::inList('efectivo', ['efectivo', 'tarjeta'], 'Método'));
        $this->assertNotNull(Validator::inList('cripto', ['efectivo', 'tarjeta'], 'Método'));
    }

    public function testDate(): void {
        $this->assertNull(Validator::date('2026-08-14', 'Fecha'));
        $this->assertNull(Validator::date('', 'Fecha'));
        $this->assertNull(Validator::date(null, 'Fecha'));
        $this->assertNotNull(Validator::date('14-08-2026', 'Fecha'));
        $this->assertNotNull(Validator::date('2026-13-45', 'Fecha'));
    }

    public function testFirstErrorReturnsFirstOnly(): void {
        $errors = [
            null,
            'Primer error',
            'Segundo error',
        ];
        $this->assertSame('Primer error', Validator::firstError($errors));
    }

    public function testFirstErrorReturnsNullWhenAllPass(): void {
        $this->assertNull(Validator::firstError([null, null]));
        $this->assertNull(Validator::firstError([]));
    }
}
