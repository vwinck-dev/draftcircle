<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Admin\GetAdminSummaryAction;

final readonly class AdminService
{
    public function __construct(
        private GetAdminSummaryAction $getSummary,
        private CacheService $cache,
    ) {}

    /** @return array<string, mixed> */
    public function summary(): array
    {
        return $this->cache->remember(
            key: 'admin:summary',
            seconds: 300,
            callback: fn (): array => $this->getSummary->execute(),
        );
    }
}
