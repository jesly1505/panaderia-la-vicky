<?php

namespace App\Tests\Unit;

use App\Core\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase {
    public function testRoundsToTwoDecimals(): void {
        $this->assertSame(10.23, Money::round(10.234));
    }

    public function testRoundsUp(): void {
        $this->assertSame(10.24, Money::round(10.235));
    }

    public function testRoundsStringInput(): void {
        $this->assertSame(12.50, Money::round('12.499'));
    }

    public function testRoundsWholeNumber(): void {
        $this->assertSame(20.0, Money::round(20));
    }

    public function testRoundsNegative(): void {
        $this->assertSame(-3.46, Money::round(-3.456));
    }

    public function testZero(): void {
        $this->assertSame(0.0, Money::round(0));
    }
}
