<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Books;

use App\Actions\Books\SearchBooksAction;
use App\Enums\BookFormat;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SearchBooksActionTest extends TestCase
{
    use RefreshDatabase;

    private SearchBooksAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(SearchBooksAction::class);
    }

    public function test_returns_all_active_books_with_no_filters(): void
    {
        $this->book(['title' => 'Active Book', 'is_active' => true]);
        $this->book(['title' => 'Inactive Book', 'is_active' => false]);

        $results = $this->action->execute(filters: []);

        $this->assertSame(1, $results->total());
        $this->assertSame('Active Book', $results->items()[0]->title);
    }

    public function test_filters_by_title_search(): void
    {
        $this->book(['title' => 'Domain-Driven Design']);
        $this->book(['title' => 'Clean Code']);

        $results = $this->action->execute(filters: ['search' => 'Domain']);

        $this->assertSame(1, $results->total());
        $this->assertSame('Domain-Driven Design', $results->items()[0]->title);
    }

    public function test_filters_by_author_search(): void
    {
        $this->book(['title' => 'Book 1', 'author' => 'Eric Evans']);
        $this->book(['title' => 'Book 2', 'author' => 'Robert Martin']);

        $results = $this->action->execute(filters: ['search' => 'Evans']);

        $this->assertSame(1, $results->total());
        $this->assertSame('Eric Evans', $results->items()[0]->author);
    }

    public function test_filters_by_category(): void
    {
        $tech = Category::query()->create(['name' => 'Technology', 'slug' => 'technology']);
        $fiction = Category::query()->create(['name' => 'Fiction', 'slug' => 'fiction']);
        $techBook = $this->book(['title' => 'Tech Book']);
        $fictionBook = $this->book(['title' => 'Fiction Book']);
        $techBook->categories()->attach($tech);
        $fictionBook->categories()->attach($fiction);

        $results = $this->action->execute(filters: ['category' => $tech->id]);

        $this->assertSame(1, $results->total());
        $this->assertSame('Tech Book', $results->items()[0]->title);
    }

    public function test_filters_by_format(): void
    {
        $this->book(['title' => 'Physical Book', 'format' => BookFormat::Physical]);
        $this->book(['title' => 'Digital Book', 'format' => BookFormat::Digital]);

        $results = $this->action->execute(filters: ['format' => 'digital']);

        $this->assertSame(1, $results->total());
        $this->assertSame('Digital Book', $results->items()[0]->title);
    }

    public function test_filters_by_availability(): void
    {
        $this->book(['title' => 'Available', 'available_copies' => 3]);
        $this->book(['title' => 'Unavailable', 'available_copies' => 0]);

        $results = $this->action->execute(filters: ['available' => true]);

        $this->assertSame(1, $results->total());
        $this->assertSame('Available', $results->items()[0]->title);
    }

    public function test_sorts_by_title_when_sort_is_title(): void
    {
        $this->book(['title' => 'Zebra']);
        $this->book(['title' => 'Apple']);

        $results = $this->action->execute(filters: ['sort' => 'title']);

        $this->assertSame('Apple', $results->items()[0]->title);
        $this->assertSame('Zebra', $results->items()[1]->title);
    }

    public function test_sorts_by_latest_by_default(): void
    {
        $this->travelTo(now()->subHour());
        $first = $this->book(['title' => 'First Created']);

        $this->travelBack();
        $second = $this->book(['title' => 'Second Created']);

        $results = $this->action->execute(filters: []);

        $this->assertSame($second->id, $results->items()[0]->id);
        $this->assertSame($first->id, $results->items()[1]->id);
    }

    public function test_returns_paginated_results(): void
    {
        for ($i = 0; $i < 15; $i++) {
            $this->book(['title' => "Book {$i}"]);
        }

        $results = $this->action->execute(filters: []);

        $this->assertSame(12, $results->perPage());
        $this->assertSame(15, $results->total());
        $this->assertCount(12, $results->items());
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
}
