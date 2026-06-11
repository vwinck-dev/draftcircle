<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use App\Enums\UserRole;

final readonly class UserData
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $registrationNumber,
        public UserRole $role,
    ) {}
}
