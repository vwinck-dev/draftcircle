<?php

declare(strict_types=1);

namespace App\Actions\Books;

use App\Models\Book;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Provides book recommendations based on user preferences and borrowing history.
 */
final readonly class GetBookRecommendationsAction
{
    /**
     * Provides book recommendations based on user preferences and borrowing history.
     *
     * @param array<int, int> $categoryWeights
     * @param list<int> $seenBookIds
     * @return Collection<int, Book>
     */
    public function execute(array $categoryWeights, array $seenBookIds): Collection
    {
        $recommendations = new Collection;
        $categoryIds = array_keys($categoryWeights);

        // First, try to get recommendations based on categories the user has shown interest in.
        if ($categoryIds !== []) {
            $recommendations = $this->query()
                ->whereNotIn('id', $seenBookIds)
                ->whereHas('categories', fn (Builder $query): Builder => $query->whereIn('categories.id', $categoryIds))
                ->orderByDesc('loan_count')
                ->limit(32)
                ->get()
                ->sortByDesc(fn (Book $book): int => $this->interestScore(book: $book, categoryWeights: $categoryWeights))
                ->take(4)
                ->values();
        }

        // If we don't have enough recommendations based on categories, fill in with popular books.
        if ($recommendations->count() < 4) {
            $excludedIds = [...$seenBookIds, ...$recommendations->modelKeys()];
            $fallback = $this->query()
                ->whereNotIn('id', $excludedIds)
                ->orderByDesc('loan_count')
                ->latest()
                ->limit(4 - $recommendations->count())
                ->get();

            $recommendations = $recommendations->concat($fallback)->values();
        }

        return $recommendations;
    }

    /**
     * Calculates the interest score for a book based on category weights.
     *
     * @param array<int, int> $categoryWeights
     */
    private function interestScore(Book $book, array $categoryWeights): int
    {
        $score = 0;

        foreach ($book->categories as $category) {
            $score += $categoryWeights[$category->id] ?? 0;
        }

        return $score;
    }

    /**
     * Builds the base query for fetching books, including necessary relationships and filters.
     *
     * @return Builder<Book>
     */
    private function query(): Builder
    {
        return Book::query()
            ->with('categories')
            ->withAvg('ratings', 'rating')
            ->withCount('ratings')
            ->where('is_active', true);
    }
}
