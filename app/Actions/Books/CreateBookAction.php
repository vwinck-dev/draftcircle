<?php

declare(strict_types=1);

namespace App\Actions\Books;

use App\DataTransferObjects\BookData;
use App\Events\NewBookAddedEvent;
use App\Models\Book;

/**
 * Handles the creation of a new book, including syncing categories and pages if it's a digital format.
 */
final readonly class CreateBookAction
{
    /**
     * Creates a new book with the provided data, including syncing categories and pages if it's a digital format.
     */
    public function execute(BookData $data): Book
    {
        $book = Book::query()->create([
            'title' => $data->title,
            'author' => $data->author,
            'publisher' => $data->publisher,
            'year' => $data->year,
            'language' => $data->language,
            'isbn' => $data->isbn,
            'description' => $data->description,
            'cover_path' => $data->coverPath,
            'format' => $data->format,
            'total_copies' => $data->totalCopies,
            'available_copies' => $data->totalCopies,
            'is_active' => $data->isActive,
        ]);

        $book->categories()->sync($data->categoryIds);

        $this->syncPages($book, $data);

        NewBookAddedEvent::dispatch($book);

        return $book->load(['categories', 'pages']);
    }

    /**
     * Syncs the pages of a digital book. For non-digital formats, this is a no-op.
     */
    private function syncPages(Book $book, BookData $data): void
    {
        // Only sync pages if the book format is digital. For physical formats, we don't need to store page content.
        if (! $data->format->isDigital()) {
            return;
        }

        foreach ($data->pages as $index => $content) {
            $book->pages()->create(['page_number' => $index + 1, 'content' => $content]);
        }
    }
}
