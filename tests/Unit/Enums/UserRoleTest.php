<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\UserRole;
use PHPUnit\Framework\TestCase;

final class UserRoleTest extends TestCase
{
    public function test_student_is_not_staff(): void
    {
        $this->assertFalse(UserRole::Student->isStaff());
    }

    public function test_librarian_is_staff(): void
    {
        $this->assertTrue(UserRole::Librarian->isStaff());
    }

    public function test_admin_is_staff(): void
    {
        $this->assertTrue(UserRole::Admin->isStaff());
    }

    public function test_enum_values_match_database_strings(): void
    {
        $this->assertSame('student', UserRole::Student->value);
        $this->assertSame('librarian', UserRole::Librarian->value);
        $this->assertSame('admin', UserRole::Admin->value);
    }
}
