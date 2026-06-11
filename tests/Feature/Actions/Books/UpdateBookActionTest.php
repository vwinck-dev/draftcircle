<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Books;

use App\Actions\Books\UpdateBookAction;
use App\DataTransferObjects\BookData;
use App\Enums\BookFormat;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UpdateBookActionTest extends TestCase
{
    use RefreshDatabase;

    private UpdateBookAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(UpdateBookAction::class);
    }

    public function test_updates_basic_book_fields(): void
    {
        $book = $this->book();

        $this->action->execute(book: $book, data: $this->bookData(['title' => 'Updated Title']));

        $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => 'Updated Title']);
    }

    public function test_preserves_available_copies_proportional_to_checked_out(): void
    {
        // 3 total, 1 available → 2 checked out; change to 5 total → 3 available (5 - 2)
        $book = $this->book(['total_copies' => 3, 'available_copies' => 1]);

        $this->action->execute(book: $book, data: $this->bookData(['total_copies' => 5]));

        $freshBook = $book->fresh();
        $this->assertNotNull($freshBook);
        $this->assertSame(3, $freshBook->available_copies);
    }

    public function test_available_copies_cannot_go_below_zero(): void
    {
        // 3 total, 3 checked out (0 available); reduce to 2 total → 0 available
        $book = $this->book(['total_copies' => 3, 'available_copies' => 0]);

        $this->action->execute(book: $book, data: $this->bookData(['total_copies' => 2]));

        $freshBook = $book->fresh();
        $this->assertNotNull($freshBook);
        $this->assertSame(0, $freshBook->available_copies);
    }

    public function test_syncs_categories(): void
    {
        $book = $this->book();
        $old = Category::query()->create(['name' => 'Old', 'slug' => 'old']);
        $new = Category::query()->create(['name' => 'New', 'slug' => 'new']);
        $book->categories()->attach($old);

        $this->action->execute(book: $book, data: $this->bookData(['category_ids' => [$new->id]]));

        $fresh = $book->fresh(['categories']);
        $this->assertNotNull($fresh);
        $this->assertCount(1, $fresh->categories);
        $firstCategory = $fresh->categories->first();
        $this->assertNotNull($firstCategory);
        $this->assertSame($new->id, $firstCategory->id);
    }

    public function test_deletes_pages_when_changing_to_physical_format(): void
    {
        $book = $this->book(['format' => BookFormat::Digital]);
        $book->pages()->create(['page_number' => 1, 'content' => 'Content']);

        $this->action->execute(book: $book, data: $this->bookData(['format' => 'physical']));

        $this->assertDatabaseCount('book_pages', 0);
    }

    public function test_replaces_pages_for_digital_book_when_pages_provided(): void
    {
        $book = $this->book(['format' => BookFormat::Digital]);
        $book->pages()->create(['page_number' => 1, 'content' => 'Old content']);

        $data = $this->bookData([
            'format' => 'digital',
            'pages' => [['content' => 'New page one'], ['content' => 'New page two']],
        ]);

        $this->action->execute(book: $book, data: $data);

        $fresh = $book->fresh(['pages']);
        $this->assertNotNull($fresh);
        $this->assertCount(2, $fresh->pages);
        $firstPage = $fresh->pages->first();
        $this->assertNotNull($firstPage);
        $this->assertSame('New page one', $firstPage->content);
    }

    public function test_keeps_existing_cover_path_when_no_new_cover_provided(): void
    {
        $book = $this->book(['cover_path' => 'books/existing-cover.jpg']);

        $this->action->execute(book: $book, data: $this->bookData(['cover_path' => null]));

        $this->assertDatabaseHas('books', ['id' => $book->id, 'cover_path' => 'books/existing-cover.jpg']);
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
            'is_active' => true,
        ], $overrides));
    }

    /** @param array<string, mixed> $overrides */
    private function bookData(array $overrides = []): BookData
    {
        return BookData::fromArray(array_merge([
            'title' => 'Test Book',
            'author' => 'Author',
            'publisher' => null,
            'year' => 2020,
            'language' => 'en',
            'isbn' => null,
            'description' => null,
            'cover_path' => null,
            'format' => 'physical',
            'total_copies' => 3,
            'is_active' => true,
            'category_ids' => [],
            'pages' => [],
        ], $overrides));
    }
}
