<?php

declare(strict_types=1);

namespace App\Actions\Books;

use App\Models\Book;
use App\Models\BookRating;
use App\Models\User;
use App\Services\CacheService;

/**
 * Handles the logic for rating a book, allowing users to submit or update their ratings and ensuring the cache is updated accordingly.
 */
final readonly class RateBookAction
{
    public function __construct(private CacheService $cache) {}

    /**
     * Rates a book by a user, creating or updating the rating as necessary, and then flushes the relevant cache to ensure updated data is displayed.
     */
    public function execute(User $user, Book $book, int $rating): BookRating
    {
        $bookRating = BookRating::query()->updateOrCreate(
            ['user_id' => $user->id, 'book_id' => $book->id],
            ['rating' => $rating],
        );

        $this->cache->flushBooks();

        return $bookRating;
    }
}
