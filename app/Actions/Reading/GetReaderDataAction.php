<?php

declare(strict_types=1);

namespace App\Actions\Reading;

use App\Models\Book;
use App\Models\ReadingProgress;
use App\Models\User;

/**
 * Retrieves the book details along with the current reading progress for a given user and book.
 */
final readonly class GetReaderDataAction
{
    /**
     * Retrieves the book details along with the current reading progress for a given user and book.
     *
     * @return array{book: Book, currentPage: int}
     */
    public function execute(User $user, Book $book): array
    {
        $book->load('pages');
        $progress = ReadingProgress::query()->whereBelongsTo($user)->whereBelongsTo($book)->first();

        return ['book' => $book, 'currentPage' => $progress->current_page ?? 1];
    }
}
