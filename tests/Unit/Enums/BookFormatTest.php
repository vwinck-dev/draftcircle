<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\BookFormat;
use PHPUnit\Framework\TestCase;

final class BookFormatTest extends TestCase
{
    public function test_physical_is_not_digital(): void
    {
        $this->assertFalse(BookFormat::Physical->isDigital());
    }

    public function test_physical_is_physical(): void
    {
        $this->assertTrue(BookFormat::Physical->isPhysical());
    }

    public function test_digital_is_digital(): void
    {
        $this->assertTrue(BookFormat::Digital->isDigital());
    }

    public function test_digital_is_not_physical(): void
    {
        $this->assertFalse(BookFormat::Digital->isPhysical());
    }

    public function test_both_is_digital(): void
    {
        $this->assertTrue(BookFormat::Both->isDigital());
    }

    public function test_both_is_physical(): void
    {
        $this->assertTrue(BookFormat::Both->isPhysical());
    }

    public function test_enum_values_match_database_strings(): void
    {
        $this->assertSame('physical', BookFormat::Physical->value);
        $this->assertSame('digital', BookFormat::Digital->value);
        $this->assertSame('both', BookFormat::Both->value);
    }
}
