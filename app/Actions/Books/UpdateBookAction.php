<?php

declare(strict_types=1);

namespace App\Actions\Books;

use App\DataTransferObjects\BookData;
use App\Models\Book;

/**
 * Handles the updating of a book's information, including its details, categories, and pages, while ensuring that the available copies are adjusted based on the total copies and any currently checked-out copies.
 */
final readonly class UpdateBookAction
{
    /**
     * Updates a book's information, including its details, categories, and pages, while ensuring
     * that the available copies are adjusted based on the total copies and any currently checked-out copies.
     */
    public function execute(Book $book, BookData $data): Book
    {
        $checkedOutCopies = max(0, $book->total_copies - $book->available_copies);

        $book->update([
            'title' => $data->title,
            'author' => $data->author,
            'publisher' => $data->publisher,
            'year' => $data->year,
            'language' => $data->language,
            'isbn' => $data->isbn,
            'description' => $data->description,
            'cover_path' => $data->coverPath ?? $book->cover_path,
            'format' => $data->format,
            'total_copies' => $data->totalCopies,
            'available_copies' => max(0, $data->totalCopies - $checkedOutCopies),
            'is_active' => $data->isActive,
        ]);

        $book->categories()->sync($data->categoryIds);

        // Only sync pages if the book format is digital. For physical formats, we don't need to store page content.
        if (! $data->format->isDigital()) {
            $book->pages()->delete();
        } elseif ($data->pagesProvided) {
            $book->pages()->delete();

            foreach ($data->pages as $index => $content) {
                $book->pages()->create(['page_number' => $index + 1, 'content' => $content]);
            }
        }

        return $book->fresh(['categories', 'pages']) ?? $book;
    }
}
