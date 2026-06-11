<?php

declare(strict_types=1);

namespace App\Actions\Books;

use App\Models\Book;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * Handles searching for books based on various filters such as search term, category, format, availability, and sorting options.
 */
final readonly class SearchBooksAction
{
    /**
     * Searches for books based on provided filters, including search term, category, format, availability, and sorting options.
     * Returns a paginated list of books matching the criteria.
     *
     * @param array{search?: string, category?: int, format?: string, available?: bool, sort?: string} $filters
     * @return LengthAwarePaginator<int, Book>
     */
    public function execute(array $filters): LengthAwarePaginator
    {
        // Build the query to search for books based on the provided filters.
        return Book::query()
            ->with('categories')
            ->withAvg('ratings', 'rating')
            ->where('is_active', true)

            // Apply search filter if provided, allowing users to search by title, author, or description.
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('author', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })

            // Apply category filter if provided, ensuring we only return books that belong to the specified category.
            ->when($filters['category'] ?? null, function (Builder $query, int $category): void {
                $query->whereHas('categories', fn (Builder $query): Builder => $query->whereKey($category));
            })

            // Apply format filter if provided, allowing users to filter by book format (e.g., digital, physical).
            ->when($filters['format'] ?? null, fn (Builder $query, string $format): Builder => $query->where('format', $format))

            // Apply availability filter if requested, ensuring we only return books that have available copies.
            ->when($filters['available'] ?? false, fn (Builder $query): Builder => $query->available())

            // Apply sorting based on the provided sort option, allowing users to sort by title or by relevance (default).
            ->when(
                ($filters['sort'] ?? 'relevance') === 'title',
                fn (Builder $query): Builder => $query->orderBy('title'),
                fn (Builder $query): Builder => $query->latest(),
            )
            ->paginate(perPage: 12)
            ->withQueryString();
    }
}
