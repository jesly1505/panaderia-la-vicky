<?php

namespace App\Tests\Unit;

use App\Helpers\UnitConverter;
use PHPUnit\Framework\TestCase;

class UnitConverterTest extends TestCase {

    public function testKgToGrams(): void {
        $this->assertSame(1000.0, UnitConverter::convert(1, 'Kg', 'Gramos'));
    }

    public function testGramsToKg(): void {
        $this->assertSame(0.5, UnitConverter::convert(500, 'Gramos', 'Kg'));
    }

    public function testPoundsToGrams(): void {
        $this->assertEqualsWithDelta(907.184, UnitConverter::convert(2, 'Libras', 'Gramos'), 0.001);
    }

    public function testOzToKg(): void {
        $this->assertSame(0.0567, UnitConverter::convert(2, 'Onzas', 'Kg'));
    }

    public function testLitrosToMililitros(): void {
        $this->assertSame(1000.0, UnitConverter::convert(1, 'Litros', 'Mililitros'));
    }

    public function testMililitrosToLitros(): void {
        $this->assertSame(0.25, UnitConverter::convert(250, 'Mililitros', 'Litros'));
    }

    public function testUnidadesToUnidades(): void {
        $this->assertSame(5.0, UnitConverter::convert(5, 'Unidades', 'Unidades'));
    }

    public function testSameUnitReturnsAmount(): void {
        $this->assertSame(2.5, UnitConverter::convert(2.5, 'Kg', 'Kg'));
    }

    public function testCaseInsensitiveAliases(): void {
        $this->assertSame(1000.0, UnitConverter::convert(1, 'kg', 'gramos'));
        $this->assertSame(1000.0, UnitConverter::convert(1, 'KILO', 'g'));
    }

    public function testUnknownUnitThrows(): void {
        $this->expectException(\Exception::class);
        UnitConverter::convert(1, 'Metros', 'Kg');
    }

    public function testIncompatibleUnitsThrows(): void {
        $this->expectException(\Exception::class);
        UnitConverter::convert(1, 'Kg', 'Litros');
    }
}
