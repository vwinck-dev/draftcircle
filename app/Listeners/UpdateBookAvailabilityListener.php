<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\BookReturnedEvent;
use App\Services\CacheService;
use Illuminate\Contracts\Queue\ShouldQueue;

final readonly class UpdateBookAvailabilityListener implements ShouldQueue
{
    public function __construct(private CacheService $cache) {}

    public function handle(BookReturnedEvent $event): void
    {
        $this->cache->flushBooks();
        $this->cache->flushLoans();
    }
}
