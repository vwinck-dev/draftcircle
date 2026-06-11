<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Books\CreateBookAction;
use App\Actions\Books\DeleteBookAction;
use App\Actions\Books\GetBookRecommendationsAction;
use App\Actions\Books\GetHomeCatalogAction;
use App\Actions\Books\LoadBookDetailsAction;
use App\Actions\Books\RateBookAction;
use App\Actions\Books\SearchBooksAction;
use App\Actions\Books\UpdateBookAction;
use App\DataTransferObjects\BookData;
use App\Models\Book;
use App\Models\BookRating;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final readonly class BookService
{
    public function __construct(
        private CreateBookAction $createBook,
        private UpdateBookAction $updateBook,
        private DeleteBookAction $deleteBook,
        private SearchBooksAction $searchBooks,
        private GetHomeCatalogAction $getHomeCatalog,
        private GetBookRecommendationsAction $getBookRecommendations,
        private RateBookAction $rateBook,
        private LoadBookDetailsAction $loadBookDetails,
        private CacheService $cache,
    ) {}

    /**
     * @param array{search?: string, category?: int, format?: string, available?: bool, sort?: string} $filters
     * @return LengthAwarePaginator<int, Book>
     */
    public function search(array $filters): LengthAwarePaginator
    {
        return $this->searchBooks->execute(filters: $filters);
    }

    /** @return array{categories: Collection<int, \App\Models\Category>, featured: Collection<int, Book>, newArrivals: Collection<int, Book>} */
    public function homeCatalog(): array
    {
        return $this->cache->remember(
            key: 'home:catalog',
            seconds: 1800,
            callback: fn (): array => $this->getHomeCatalog->execute(),
        );
    }

    /**
     * @param array<int, int> $categoryWeights
     * @param list<int> $seenBookIds
     * @return Collection<int, Book>
     */
    public function recommendations(array $categoryWeights, array $seenBookIds): Collection
    {
        return $this->getBookRecommendations->execute(categoryWeights: $categoryWeights, seenBookIds: $seenBookIds);
    }

    public function create(BookData $data): Book
    {
        return $this->createBook->execute(data: $data);
    }

    public function update(Book $book, BookData $data): Book
    {
        return $this->updateBook->execute(book: $book, data: $data);
    }

    public function delete(Book $book): void
    {
        $this->deleteBook->execute(book: $book);
    }

    public function rate(User $user, Book $book, int $rating): BookRating
    {
        return $this->rateBook->execute(user: $user, book: $book, rating: $rating);
    }

    public function details(Book $book): Book
    {
        return $this->loadBookDetails->execute(book: $book);
    }
}
