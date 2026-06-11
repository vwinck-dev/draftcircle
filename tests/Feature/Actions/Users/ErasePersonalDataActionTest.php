<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Users;

use App\Actions\Users\ErasePersonalDataAction;
use App\Enums\BookFormat;
use App\Models\Book;
use App\Models\BookRating;
use App\Models\ReadingProgress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ErasePersonalDataActionTest extends TestCase
{
    use RefreshDatabase;

    private ErasePersonalDataAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(ErasePersonalDataAction::class);
    }

    public function test_deletes_user_book_ratings(): void
    {
        $user = User::factory()->create();
        $book = $this->book();
        BookRating::query()->create(['user_id' => $user->id, 'book_id' => $book->id, 'rating' => 4]);

        $this->action->handle(user: $user);

        $this->assertDatabaseCount('book_ratings', 0);
    }

    public function test_does_not_delete_other_users_ratings(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $book = $this->book();
        BookRating::query()->create(['user_id' => $other->id, 'book_id' => $book->id, 'rating' => 3]);

        $this->action->handle(user: $user);

        $this->assertDatabaseCount('book_ratings', 1);
    }

    public function test_deletes_user_reading_progress(): void
    {
        $user = User::factory()->create();
        $book = $this->book();
        ReadingProgress::query()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'current_page' => 5,
            'total_pages' => 100,
        ]);

        $this->action->handle(user: $user);

        $this->assertDatabaseCount('reading_progress', 0);
    }

    public function test_does_not_delete_other_users_reading_progress(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $book = $this->book();
        ReadingProgress::query()->create([
            'user_id' => $other->id,
            'book_id' => $book->id,
            'current_page' => 5,
            'total_pages' => 100,
        ]);

        $this->action->handle(user: $user);

        $this->assertDatabaseCount('reading_progress', 1);
    }

    public function test_deletes_user_notifications(): void
    {
        $user = User::factory()->create();
        $user->notify(new \Illuminate\Auth\Notifications\VerifyEmail);

        $this->action->handle(user: $user);

        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_preserves_user_account_after_erasure(): void
    {
        $user = User::factory()->create();

        $this->action->handle(user: $user);

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    private function book(): Book
    {
        return Book::query()->create([
            'title' => 'Book',
            'author' => 'Author',
            'format' => BookFormat::Physical,
            'total_copies' => 1,
            'available_copies' => 1,
            'is_active' => true,
        ]);
    }
}
