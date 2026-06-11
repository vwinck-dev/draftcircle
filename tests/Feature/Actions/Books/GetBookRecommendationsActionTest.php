<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Books;

use App\Actions\Books\GetBookRecommendationsAction;
use App\Enums\BookFormat;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GetBookRecommendationsActionTest extends TestCase
{
    use RefreshDatabase;

    private GetBookRecommendationsAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(GetBookRecommendationsAction::class);
    }

    public function test_returns_empty_collection_when_no_books_exist(): void
    {
        $results = $this->action->execute(categoryWeights: [], seenBookIds: []);

        $this->assertCount(0, $results);
    }

    public function test_returns_fallback_books_when_no_category_weights(): void
    {
        $this->book(['title' => 'Book A', 'loan_count' => 10]);
        $this->book(['title' => 'Book B', 'loan_count' => 5]);

        $results = $this->action->execute(categoryWeights: [], seenBookIds: []);

        $this->assertCount(2, $results);
    }

    public function test_prefers_books_matching_category_weights(): void
    {
        $preferred = Category::query()->create(['name' => 'Preferred', 'slug' => 'preferred']);
        $other = Category::query()->create(['name' => 'Other', 'slug' => 'other']);

        $interestBook = $this->book(['title' => 'Interest Book', 'loan_count' => 1]);
        $interestBook->categories()->attach($preferred);

        $otherBook = $this->book(['title' => 'Other Book', 'loan_count' => 100]);
        $otherBook->categories()->attach($other);

        $results = $this->action->execute(
            categoryWeights: [$preferred->id => 5],
            seenBookIds: [],
        );

        $firstResult = $results->first();
        $this->assertNotNull($firstResult);
        $this->assertSame($interestBook->id, $firstResult->id);
    }

    public function test_excludes_already_seen_books(): void
    {
        $seenBook = $this->book(['title' => 'Seen Book', 'loan_count' => 100]);
        $unseenBook = $this->book(['title' => 'Unseen Book', 'loan_count' => 1]);

        $results = $this->action->execute(
            categoryWeights: [],
            seenBookIds: [$seenBook->id],
        );

        $this->assertFalse($results->contains('id', $seenBook->id));
        $this->assertTrue($results->contains('id', $unseenBook->id));
    }

    public function test_supplements_with_fallback_when_category_matches_are_insufficient(): void
    {
        $preferredCat = Category::query()->create(['name' => 'Science', 'slug' => 'science']);
        $scienceBook = $this->book(['title' => 'Science Book', 'loan_count' => 5]);
        $scienceBook->categories()->attach($preferredCat);

        // 3 books outside the preferred category as fallback
        for ($i = 0; $i < 3; $i++) {
            $this->book(['title' => "Generic Book {$i}", 'loan_count' => 1]);
        }

        $results = $this->action->execute(
            categoryWeights: [$preferredCat->id => 3],
            seenBookIds: [],
        );

        $this->assertCount(4, $results);
        $this->assertTrue($results->contains('id', $scienceBook->id));
    }

    public function test_returns_maximum_four_recommendations(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->book(['title' => "Book {$i}"]);
        }

        $results = $this->action->execute(categoryWeights: [], seenBookIds: []);

        $this->assertCount(4, $results);
    }

    public function test_only_returns_active_books(): void
    {
        $this->book(['title' => 'Active', 'is_active' => true]);
        $this->book(['title' => 'Inactive', 'is_active' => false]);

        $results = $this->action->execute(categoryWeights: [], seenBookIds: []);

        $this->assertCount(1, $results);
        $firstResult = $results->first();
        $this->assertNotNull($firstResult);
        $this->assertSame('Active', $firstResult->title);
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
