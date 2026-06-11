<?php

declare(strict_types=1);

namespace App\Enums;

enum BookFormat: string
{
    case Physical = 'physical';
    case Digital = 'digital';
    case Both = 'both';

    public function isDigital(): bool
    {
        return $this !== self::Physical;
    }

    public function isPhysical(): bool
    {
        return $this !== self::Digital;
    }
}
