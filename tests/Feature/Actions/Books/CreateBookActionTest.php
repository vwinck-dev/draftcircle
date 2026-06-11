<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Books;

use App\Actions\Books\CreateBookAction;
use App\DataTransferObjects\BookData;
use App\Events\NewBookAddedEvent;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

final class CreateBookActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateBookAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(CreateBookAction::class);
    }

    public function test_creates_book_with_given_data(): void
    {
        $data = $this->bookData();

        $book = $this->action->execute(data: $data);

        $this->assertInstanceOf(Book::class, $book);
        $this->assertSame('Clean Code', $book->title);
        $this->assertSame('Robert Martin', $book->author);
        $this->assertDatabaseHas('books', ['title' => 'Clean Code', 'author' => 'Robert Martin']);
    }

    public function test_sets_available_copies_equal_to_total_copies(): void
    {
        $data = $this->bookData(['total_copies' => 5]);

        $book = $this->action->execute(data: $data);

        $this->assertSame(5, $book->available_copies);
        $this->assertSame(5, $book->total_copies);
    }

    public function test_syncs_category_ids(): void
    {
        $cat1 = Category::query()->create(['name' => 'Fiction', 'slug' => 'fiction']);
        $cat2 = Category::query()->create(['name' => 'Science', 'slug' => 'science']);
        $data = $this->bookData(['category_ids' => [$cat1->id, $cat2->id]]);

        $book = $this->action->execute(data: $data);

        $this->assertCount(2, $book->categories);
        $this->assertTrue($book->categories->contains($cat1->id));
        $this->assertTrue($book->categories->contains($cat2->id));
    }

    public function test_creates_pages_for_digital_book(): void
    {
        $data = $this->bookData([
            'format' => 'digital',
            'pages' => [['content' => 'Page one content'], ['content' => 'Page two content']],
        ]);

        $book = $this->action->execute(data: $data);

        $this->assertCount(2, $book->pages);
        $firstPage = $book->pages->first();
        $this->assertNotNull($firstPage);
        $this->assertSame(1, $firstPage->page_number);
        $this->assertSame('Page one content', $firstPage->content);
    }

    public function test_does_not_create_pages_for_physical_book(): void
    {
        $data = $this->bookData([
            'format' => 'physical',
            'pages' => [['content' => 'ignored']],
        ]);

        $book = $this->action->execute(data: $data);

        $this->assertCount(0, $book->pages);
    }

    public function test_dispatches_new_book_added_event(): void
    {
        Event::fake([NewBookAddedEvent::class]);
        $data = $this->bookData();

        $this->action->execute(data: $data);

        Event::assertDispatched(NewBookAddedEvent::class);
    }

    public function test_book_loads_categories_and_pages_relation(): void
    {
        $data = $this->bookData();

        $book = $this->action->execute(data: $data);

        $this->assertTrue($book->relationLoaded('categories'));
        $this->assertTrue($book->relationLoaded('pages'));
    }

    /** @param array<string, mixed> $overrides */
    private function bookData(array $overrides = []): BookData
    {
        return BookData::fromArray(array_merge([
            'title' => 'Clean Code',
            'author' => 'Robert Martin',
            'publisher' => 'Prentice Hall',
            'year' => 2008,
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
