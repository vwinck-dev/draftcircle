<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Books;

use App\Actions\Books\DeleteBookAction;
use App\Enums\BookFormat;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DeleteBookActionTest extends TestCase
{
    use RefreshDatabase;

    private DeleteBookAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(DeleteBookAction::class);
    }

    public function test_soft_deletes_book(): void
    {
        $book = $this->book();

        $this->action->execute(book: $book);

        $this->assertSoftDeleted('books', ['id' => $book->id]);
    }

    public function test_deleted_book_is_not_found_in_default_queries(): void
    {
        $book = $this->book();

        $this->action->execute(book: $book);

        $this->assertNull(Book::find($book->id));
    }

    public function test_deleted_book_can_be_found_with_trashed(): void
    {
        $book = $this->book();

        $this->action->execute(book: $book);

        $this->assertNotNull(Book::withTrashed()->find($book->id));
    }

    private function book(): Book
    {
        return Book::query()->create([
            'title' => 'Book to Delete',
            'author' => 'Author',
            'format' => BookFormat::Physical,
            'total_copies' => 3,
            'available_copies' => 3,
            'is_active' => true,
        ]);
    }
}
