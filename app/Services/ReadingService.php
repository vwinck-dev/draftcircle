<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Reading\GetReaderDataAction;
use App\Actions\Reading\SaveReadingProgressAction;
use App\Models\Book;
use App\Models\ReadingProgress;
use App\Models\User;

final readonly class ReadingService
{
    public function __construct(
        private SaveReadingProgressAction $saveProgress,
        private GetReaderDataAction $getReaderData,
    ) {}

    public function saveProgress(User $user, Book $book, int $page): ReadingProgress
    {
        return $this->saveProgress->execute(user: $user, book: $book, page: $page);
    }

    /** @return array{book: Book, currentPage: int} */
    public function data(User $user, Book $book): array
    {
        return $this->getReaderData->execute(user: $user, book: $book);
    }
}
