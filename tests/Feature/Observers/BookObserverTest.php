<?php

declare(strict_types=1);

namespace Tests\Feature\Observers;

use App\Enums\BookFormat;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class BookObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_flushes_catalog_cache_when_book_is_created(): void
    {
        Cache::put('home:catalog', ['cached' => true], 3600);
        Cache::put('categories:all', ['cached' => true], 3600);

        Book::query()->create([
            'title' => 'New Book',
            'author' => 'Author',
            'format' => BookFormat::Physical,
            'total_copies' => 1,
            'available_copies' => 1,
            'is_active' => true,
        ]);

        $this->assertNull(Cache::get('home:catalog'));
        $this->assertNull(Cache::get('categories:all'));
    }

    public function test_flushes_catalog_cache_when_book_is_updated(): void
    {
        $book = Book::query()->create([
            'title' => 'Book',
            'author' => 'Author',
            'format' => BookFormat::Physical,
            'total_copies' => 1,
            'available_copies' => 1,
            'is_active' => true,
        ]);
        Cache::put('home:catalog', ['cached' => true], 3600);

        $book->update(['title' => 'Updated Title']);

        $this->assertNull(Cache::get('home:catalog'));
    }

    public function test_flushes_catalog_cache_when_book_is_soft_deleted(): void
    {
        $book = Book::query()->create([
            'title' => 'Book',
            'author' => 'Author',
            'format' => BookFormat::Physical,
            'total_copies' => 1,
            'available_copies' => 1,
            'is_active' => true,
        ]);
        Cache::put('home:catalog', ['cached' => true], 3600);

        $book->delete();

        $this->assertNull(Cache::get('home:catalog'));
    }

    public function test_flushes_catalog_cache_when_book_is_restored(): void
    {
        $book = Book::query()->create([
            'title' => 'Book',
            'author' => 'Author',
            'format' => BookFormat::Physical,
            'total_copies' => 1,
            'available_copies' => 1,
            'is_active' => true,
        ]);
        $book->delete();
        Cache::put('home:catalog', ['cached' => true], 3600);

        $book->restore();

        $this->assertNull(Cache::get('home:catalog'));
    }
}
