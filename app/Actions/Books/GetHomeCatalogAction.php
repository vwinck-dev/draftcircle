<?php

declare(strict_types=1);

namespace App\Actions\Books;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

/**
 * Gathers data for the home catalog page, including categories, featured books, and new arrivals.
 */
final readonly class GetHomeCatalogAction
{
    /**
     * Gathers data for the home catalog page, including categories, featured books, and new arrivals.
     *
     * @return array{categories: Collection<int, Category>, featured: Collection<int, Book>, newArrivals: Collection<int, Book>}
     */
    public function execute(): array
    {
        return [
            'categories' => Category::query()->withCount('books')->orderBy('name')->get(),
            'featured' => Book::query()->with('categories')->withAvg('ratings', 'rating')->withCount('ratings')->orderByDesc('loan_count')->limit(8)->get(),
            'newArrivals' => Book::query()->with('categories')->withAvg('ratings', 'rating')->withCount('ratings')->latest()->limit(8)->get(),
        ];
    }
}
