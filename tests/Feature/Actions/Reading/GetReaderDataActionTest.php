<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Reading;

use App\Actions\Reading\GetReaderDataAction;
use App\Enums\BookFormat;
use App\Models\Book;
use App\Models\ReadingProgress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GetReaderDataActionTest extends TestCase
{
    use RefreshDatabase;

    private GetReaderDataAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(GetReaderDataAction::class);
    }

    public function test_returns_book_with_pages(): void
    {
        $user = User::factory()->create();
        $book = $this->book();
        $book->pages()->create(['page_number' => 1, 'content' => 'Page content']);

        $data = $this->action->execute(user: $user, book: $book);

        $this->assertSame($book->id, $data['book']->id);
        $this->assertTrue($data['book']->relationLoaded('pages'));
    }

    public function test_returns_current_page_from_reading_progress(): void
    {
        $user = User::factory()->create();
        $book = $this->book();
        ReadingProgress::query()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'current_page' => 42,
        ]);

        $data = $this->action->execute(user: $user, book: $book);

        $this->assertSame(42, $data['currentPage']);
    }

    public function test_returns_page_1_when_no_reading_progress_exists(): void
    {
        $user = User::factory()->create();
        $book = $this->book();

        $data = $this->action->execute(user: $user, book: $book);

        $this->assertSame(1, $data['currentPage']);
    }

    public function test_returns_correct_progress_for_specific_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $book = $this->book();
        ReadingProgress::query()->create(['user_id' => $user1->id, 'book_id' => $book->id, 'current_page' => 10]);
        ReadingProgress::query()->create(['user_id' => $user2->id, 'book_id' => $book->id, 'current_page' => 99]);

        $data = $this->action->execute(user: $user1, book: $book);

        $this->assertSame(10, $data['currentPage']);
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
