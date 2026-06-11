<?php

declare(strict_types=1);

namespace Tests\Feature\Requests;

use App\Enums\BookFormat;
use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RateBookRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_rate_book(): void
    {
        $user = User::factory()->create();
        $book = $this->book();

        $response = $this->actingAs($user)
            ->post(route('books.ratings.store', $book), ['rating' => 4]);

        $response->assertRedirect();
        $this->assertDatabaseHas('book_ratings', ['user_id' => $user->id, 'book_id' => $book->id, 'rating' => 4]);
    }

    public function test_unauthenticated_user_cannot_rate(): void
    {
        $book = $this->book();

        $this->post(route('books.ratings.store', $book), ['rating' => 4])
            ->assertRedirect(route('login'));
    }

    public function test_rating_must_be_between_1_and_5(): void
    {
        $user = User::factory()->create();
        $book = $this->book();

        $this->actingAs($user)
            ->post(route('books.ratings.store', $book), ['rating' => 0])
            ->assertSessionHasErrors('rating');

        $this->actingAs($user)
            ->post(route('books.ratings.store', $book), ['rating' => 6])
            ->assertSessionHasErrors('rating');
    }

    public function test_rating_1_is_valid(): void
    {
        $user = User::factory()->create();
        $book = $this->book();

        $this->actingAs($user)
            ->post(route('books.ratings.store', $book), ['rating' => 1])
            ->assertSessionMissing('errors');
    }

    public function test_rating_5_is_valid(): void
    {
        $user = User::factory()->create();
        $book = $this->book();

        $this->actingAs($user)
            ->post(route('books.ratings.store', $book), ['rating' => 5])
            ->assertSessionMissing('errors');
    }

    public function test_rating_is_required(): void
    {
        $user = User::factory()->create();
        $book = $this->book();

        $this->actingAs($user)
            ->post(route('books.ratings.store', $book), [])
            ->assertSessionHasErrors('rating');
    }

    public function test_rating_must_be_integer(): void
    {
        $user = User::factory()->create();
        $book = $this->book();

        $this->actingAs($user)
            ->post(route('books.ratings.store', $book), ['rating' => 'four'])
            ->assertSessionHasErrors('rating');
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
