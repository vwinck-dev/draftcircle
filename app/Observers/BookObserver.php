<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Book;
use App\Services\CacheService;

final readonly class BookObserver
{
    public function __construct(private CacheService $cache) {}

    public function saved(Book $book): void
    {
        $this->cache->flushBooks();
    }

    public function deleted(Book $book): void
    {
        $this->cache->flushBooks();
    }

    public function restored(Book $book): void
    {
        $this->cache->flushBooks();
    }
}
