<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Books;

use App\Actions\Books\RateBookAction;
use App\Enums\BookFormat;
use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class RateBookActionTest extends TestCase
{
    use RefreshDatabase;

    private RateBookAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(RateBookAction::class);
    }

    public function test_creates_rating_for_book(): void
    {
        $user = User::factory()->create();
        $book = $this->book();

        $rating = $this->action->execute(user: $user, book: $book, rating: 4);

        $this->assertSame($user->id, $rating->user_id);
        $this->assertSame($book->id, $rating->book_id);
        $this->assertSame(4, $rating->rating);
    }

    public function test_persists_rating_to_database(): void
    {
        $user = User::factory()->create();
        $book = $this->book();

        $this->action->execute(user: $user, book: $book, rating: 3);

        $this->assertDatabaseHas('book_ratings', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
        ]);
    }

    public function test_updates_existing_rating_instead_of_creating_duplicate(): void
    {
        $user = User::factory()->create();
        $book = $this->book();

        $this->action->execute(user: $user, book: $book, rating: 2);
        $this->action->execute(user: $user, book: $book, rating: 5);

        $this->assertDatabaseCount('book_ratings', 1);
        $this->assertDatabaseHas('book_ratings', ['user_id' => $user->id, 'rating' => 5]);
    }

    public function test_different_users_can_rate_same_book(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $book = $this->book();

        $this->action->execute(user: $user1, book: $book, rating: 3);
        $this->action->execute(user: $user2, book: $book, rating: 5);

        $this->assertDatabaseCount('book_ratings', 2);
    }

    public function test_flushes_book_cache_after_rating(): void
    {
        $user = User::factory()->create();
        $book = $this->book();
        Cache::put('home:catalog', ['cached' => true], 3600);

        $this->action->execute(user: $user, book: $book, rating: 4);

        $this->assertNull(Cache::get('home:catalog'));
    }

    private function book(): Book
    {
        return Book::query()->create([
            'title' => 'Test Book',
            'author' => 'Author',
            'format' => BookFormat::Physical,
            'total_copies' => 3,
            'available_copies' => 3,
            'is_active' => true,
        ]);
    }
}
