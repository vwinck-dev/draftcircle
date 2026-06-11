<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Books;

use App\Actions\Books\GetHomeCatalogAction;
use App\Enums\BookFormat;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GetHomeCatalogActionTest extends TestCase
{
    use RefreshDatabase;

    private GetHomeCatalogAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(GetHomeCatalogAction::class);
    }

    public function test_returns_categories_with_book_counts(): void
    {
        $tech = Category::query()->create(['name' => 'Technology', 'slug' => 'technology']);
        $book = $this->book();
        $book->categories()->attach($tech);

        $catalog = $this->action->execute();

        $techCategory = $catalog['categories']->firstWhere('id', $tech->id);
        $this->assertNotNull($techCategory);
        $this->assertSame(1, $techCategory->books_count);
    }

    public function test_categories_are_ordered_alphabetically(): void
    {
        Category::query()->create(['name' => 'Zoology', 'slug' => 'zoology']);
        Category::query()->create(['name' => 'Art', 'slug' => 'art']);

        $catalog = $this->action->execute();

        $firstCat = $catalog['categories']->first();
        $lastCat = $catalog['categories']->last();
        $this->assertNotNull($firstCat);
        $this->assertNotNull($lastCat);
        $this->assertSame('Art', $firstCat->name);
        $this->assertSame('Zoology', $lastCat->name);
    }

    public function test_featured_books_are_ordered_by_loan_count_descending(): void
    {
        $popular = $this->book(['title' => 'Popular', 'loan_count' => 50]);
        $niche = $this->book(['title' => 'Niche', 'loan_count' => 1]);

        $catalog = $this->action->execute();

        $firstFeatured = $catalog['featured']->first();
        $lastFeatured = $catalog['featured']->last();
        $this->assertNotNull($firstFeatured);
        $this->assertNotNull($lastFeatured);
        $this->assertSame($popular->id, $firstFeatured->id);
        $this->assertSame($niche->id, $lastFeatured->id);
    }

    public function test_new_arrivals_are_ordered_by_creation_date_descending(): void
    {
        $this->travelTo(now()->subHour());
        $older = $this->book(['title' => 'Older Book']);

        $this->travelBack();
        $newer = $this->book(['title' => 'Newer Book']);

        $catalog = $this->action->execute();

        $firstArrival = $catalog['newArrivals']->first();
        $lastArrival = $catalog['newArrivals']->last();
        $this->assertNotNull($firstArrival);
        $this->assertNotNull($lastArrival);
        $this->assertSame($newer->id, $firstArrival->id);
        $this->assertSame($older->id, $lastArrival->id);
    }

    public function test_limits_featured_books_to_8(): void
    {
        for ($i = 0; $i < 12; $i++) {
            $this->book(['title' => "Book {$i}"]);
        }

        $catalog = $this->action->execute();

        $this->assertCount(8, $catalog['featured']);
    }

    public function test_limits_new_arrivals_to_8(): void
    {
        for ($i = 0; $i < 12; $i++) {
            $this->book(['title' => "Book {$i}"]);
        }

        $catalog = $this->action->execute();

        $this->assertCount(8, $catalog['newArrivals']);
    }

    public function test_includes_rating_aggregates(): void
    {
        $book = $this->book();
        $user = \App\Models\User::factory()->create();
        \App\Models\BookRating::query()->create(['book_id' => $book->id, 'user_id' => $user->id, 'rating' => 4]);

        $catalog = $this->action->execute();

        $found = $catalog['newArrivals']->firstWhere('id', $book->id);
        $this->assertNotNull($found);
        $this->assertSame(4.0, (float) $found->ratings_avg_rating);
        $this->assertSame(1, $found->ratings_count);
    }

    /** @param array<string, mixed> $overrides */
    private function book(array $overrides = []): Book
    {
        return Book::query()->create(array_merge([
            'title' => 'Test Book',
            'author' => 'Author',
            'format' => BookFormat::Physical,
            'total_copies' => 3,
            'available_copies' => 3,
            'loan_count' => 0,
            'is_active' => true,
        ], $overrides));
    }
}
