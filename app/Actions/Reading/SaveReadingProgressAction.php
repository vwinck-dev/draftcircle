<?php

declare(strict_types=1);

namespace App\Actions\Reading;

use App\Models\Book;
use App\Models\ReadingProgress;
use App\Models\User;

/**
 * Saves the reading progress for a given user and book.
 */
final readonly class SaveReadingProgressAction
{
    /**
     * Saves the reading progress for a given user and book, updating or creating a record in the "reading_progress" table with the current page number.
     */
    public function execute(User $user, Book $book, int $page): ReadingProgress
    {
        return ReadingProgress::query()->updateOrCreate(
            ['user_id' => $user->id, 'book_id' => $book->id],
            ['current_page' => $page],
        );
    }
}
