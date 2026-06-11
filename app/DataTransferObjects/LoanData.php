<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use App\Enums\LoanStatus;
use App\Enums\LoanType;
use Carbon\CarbonImmutable;

final readonly class LoanData
{
    public function __construct(
        public int $userId,
        public int $bookId,
        public LoanType $type,
        public LoanStatus $status,
        public ?CarbonImmutable $reservedAt,
        public ?CarbonImmutable $dueAt,
    ) {}
}
