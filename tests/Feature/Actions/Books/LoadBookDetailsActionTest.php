<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Books;

use App\Actions\Books\LoadBookDetailsAction;
use App\Enums\BookFormat;
use App\Models\Book;
use App\Models\BookRating;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LoadBookDetailsActionTest extends TestCase
{
    use RefreshDatabase;

    private LoadBookDetailsAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(LoadBookDetailsAction::class);
    }

    public function test_loads_categories_relation(): void
    {
        $book = $this->book();
        $category = Category::query()->create(['name' => 'Fiction', 'slug' => 'fiction']);
        $book->categories()->attach($category);

        $loaded = $this->action->execute(book: $book);

        $this->assertTrue($loaded->relationLoaded('categories'));
        $this->assertCount(1, $loaded->categories);
    }

    public function test_loads_pages_relation(): void
    {
        $book = $this->book(['format' => BookFormat::Digital]);
        $book->pages()->create(['page_number' => 1, 'content' => 'Chapter 1']);

        $loaded = $this->action->execute(book: $book);

        $this->assertTrue($loaded->relationLoaded('pages'));
        $this->assertCount(1, $loaded->pages);
    }

    public function test_loads_average_rating(): void
    {
        $book = $this->book();
        $user = User::factory()->create();
        BookRating::query()->create(['book_id' => $book->id, 'user_id' => $user->id, 'rating' => 4]);

        $loaded = $this->action->execute(book: $book);

        $this->assertSame(4.0, (float) $loaded->ratings_avg_rating);
    }

    public function test_loads_ratings_count(): void
    {
        $book = $this->book();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        BookRating::query()->create(['book_id' => $book->id, 'user_id' => $user1->id, 'rating' => 3]);
        BookRating::query()->create(['book_id' => $book->id, 'user_id' => $user2->id, 'rating' => 5]);

        $loaded = $this->action->execute(book: $book);

        $this->assertSame(2, $loaded->ratings_count);
    }

    public function test_returns_null_average_when_no_ratings(): void
    {
        $book = $this->book();

        $loaded = $this->action->execute(book: $book);

        $this->assertNull($loaded->ratings_avg_rating);
    }

    /** @param array<string, mixed> $overrides */
    private function book(array $overrides = []): Book
    {
        return Book::query()->create(array_merge([
            'title' => 'Test Book',
            'author' => 'Author',
            'format' => BookFormat::Physical,
            'total_copies' => 1,
            'available_copies' => 1,
            'is_active' => true,
        ], $overrides));
    }
}
