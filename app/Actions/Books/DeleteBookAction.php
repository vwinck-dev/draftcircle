<?php

declare(strict_types=1);

namespace App\Actions\Books;

use App\Models\Book;

/**
 * Handles the deletion of a book and all its related data (e.g., pages, loans).
 */
final readonly class DeleteBookAction
{
    /**
     * Deletes a book and all its related data (e.g., pages, loans).
     */
    public function execute(Book $book): void
    {
        $book->delete();
    }
}
