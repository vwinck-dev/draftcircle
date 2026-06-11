<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Reading;

use App\Actions\Reading\SaveReadingProgressAction;
use App\Enums\BookFormat;
use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SaveReadingProgressActionTest extends TestCase
{
    use RefreshDatabase;

    private SaveReadingProgressAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(SaveReadingProgressAction::class);
    }

    public function test_creates_reading_progress_for_first_time(): void
    {
        $user = User::factory()->create();
        $book = $this->book();

        $progress = $this->action->execute(user: $user, book: $book, page: 5);

        $this->assertSame($user->id, $progress->user_id);
        $this->assertSame($book->id, $progress->book_id);
        $this->assertSame(5, $progress->current_page);
    }

    public function test_persists_progress_to_database(): void
    {
        $user = User::factory()->create();
        $book = $this->book();

        $this->action->execute(user: $user, book: $book, page: 10);

        $this->assertDatabaseHas('reading_progress', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'current_page' => 10,
        ]);
    }

    public function test_updates_existing_progress_instead_of_creating_duplicate(): void
    {
        $user = User::factory()->create();
        $book = $this->book();

        $this->action->execute(user: $user, book: $book, page: 3);
        $this->action->execute(user: $user, book: $book, page: 7);

        $this->assertDatabaseCount('reading_progress', 1);
        $this->assertDatabaseHas('reading_progress', ['current_page' => 7]);
    }

    public function test_different_users_can_have_progress_for_same_book(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $book = $this->book();

        $this->action->execute(user: $user1, book: $book, page: 2);
        $this->action->execute(user: $user2, book: $book, page: 8);

        $this->assertDatabaseCount('reading_progress', 2);
    }

    public function test_same_user_can_have_progress_for_different_books(): void
    {
        $user = User::factory()->create();
        $book1 = $this->book();
        $book2 = $this->book();

        $this->action->execute(user: $user, book: $book1, page: 1);
        $this->action->execute(user: $user, book: $book2, page: 4);

        $this->assertDatabaseCount('reading_progress', 2);
    }

    private function book(): Book
    {
        return Book::query()->create([
            'title' => 'Digital Book',
            'author' => 'Author',
            'format' => BookFormat::Digital,
            'total_copies' => 1,
            'available_copies' => 1,
            'is_active' => true,
        ]);
    }
}
