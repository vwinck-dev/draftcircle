<?php

declare(strict_types=1);

namespace App\Actions\Books;

use App\Models\Book;

/**
 * Loads detailed information for a specific book, including its categories, pages, average rating, and rating count.
 */
final readonly class LoadBookDetailsAction
{
    /**
     * Loads detailed information for a specific book, including its categories, pages, average rating, and rating count.
     */
    public function execute(Book $book): Book
    {
        return $book->load(['categories', 'pages'])->loadAvg('ratings', 'rating')->loadCount('ratings');
    }
}
