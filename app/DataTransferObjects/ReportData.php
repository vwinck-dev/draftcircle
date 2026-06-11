<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

final readonly class ReportData
{
    /** @param array<string, int|float|string> $metrics */
    public function __construct(
        public string $period,
        public array $metrics,
        public string $generatedAt,
    ) {}
}
