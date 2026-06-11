<?php

declare(strict_types=1);

namespace Tests\Feature\Requests;

use App\Enums\BookFormat;
use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ReadingProgressRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_save_reading_progress(): void
    {
        $user = User::factory()->create();
        $book = $this->book();

        $response = $this->actingAs($user)->put(route('books.progress', $book), ['page' => 5]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reading_progress', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'current_page' => 5,
        ]);
    }

    public function test_unauthenticated_user_cannot_save_progress(): void
    {
        $book = $this->book();

        $this->put(route('books.progress', $book), ['page' => 5])
            ->assertRedirect(route('login'));
    }

    public function test_page_is_required(): void
    {
        $user = User::factory()->create();
        $book = $this->book();

        $this->actingAs($user)
            ->put(route('books.progress', $book), [])
            ->assertSessionHasErrors('page');
    }

    public function test_page_must_be_integer(): void
    {
        $user = User::factory()->create();
        $book = $this->book();

        $this->actingAs($user)
            ->put(route('books.progress', $book), ['page' => 'five'])
            ->assertSessionHasErrors('page');
    }

    public function test_page_must_be_at_least_1(): void
    {
        $user = User::factory()->create();
        $book = $this->book();

        $this->actingAs($user)
            ->put(route('books.progress', $book), ['page' => 0])
            ->assertSessionHasErrors('page');
    }

    public function test_page_1_is_valid(): void
    {
        $user = User::factory()->create();
        $book = $this->book();

        $this->actingAs($user)
            ->put(route('books.progress', $book), ['page' => 1])
            ->assertSessionMissing('errors');
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
