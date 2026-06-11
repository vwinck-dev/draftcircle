<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Loan;
use App\Services\CacheService;

final readonly class LoanObserver
{
    public function __construct(private CacheService $cache) {}

    public function saved(Loan $loan): void
    {
        $this->cache->flushLoans();
    }

    public function deleted(Loan $loan): void
    {
        $this->cache->flushLoans();
    }
}
