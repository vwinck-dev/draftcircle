<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Student = 'student';
    case Librarian = 'librarian';
    case Admin = 'admin';

    public function isStaff(): bool
    {
        return $this !== self::Student;
    }
}
