<?php

declare(strict_types=1);

namespace Tests\Feature\Requests;

use App\Enums\BookFormat;
use App\Enums\UserRole;
use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class BookRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_create_book_with_valid_data(): void
    {
        $staff = User::factory()->create(['role' => UserRole::Librarian]);

        $response = $this->actingAs($staff)->post(route('admin.books.store'), $this->validBookPayload());

        $response->assertRedirect();
        $this->assertDatabaseHas('books', ['title' => 'Valid Title', 'author' => 'Valid Author']);
    }

    public function test_student_is_forbidden_from_creating_book(): void
    {
        $student = User::factory()->create(['role' => UserRole::Student]);

        $response = $this->actingAs($student)->post(route('admin.books.store'), $this->validBookPayload());

        $response->assertForbidden();
    }

    public function test_title_is_required(): void
    {
        $staff = User::factory()->create(['role' => UserRole::Librarian]);
        $payload = array_merge($this->validBookPayload(), ['title' => '']);

        $response = $this->actingAs($staff)->post(route('admin.books.store'), $payload);

        $response->assertSessionHasErrors('title');
    }

    public function test_author_is_required(): void
    {
        $staff = User::factory()->create(['role' => UserRole::Librarian]);
        $payload = array_merge($this->validBookPayload(), ['author' => '']);

        $response = $this->actingAs($staff)->post(route('admin.books.store'), $payload);

        $response->assertSessionHasErrors('author');
    }

    public function test_format_must_be_valid_enum_value(): void
    {
        $staff = User::factory()->create(['role' => UserRole::Librarian]);
        $payload = array_merge($this->validBookPayload(), ['format' => 'invalid-format']);

        $response = $this->actingAs($staff)->post(route('admin.books.store'), $payload);

        $response->assertSessionHasErrors('format');
    }

    public function test_year_must_be_between_1000_and_2100(): void
    {
        $staff = User::factory()->create(['role' => UserRole::Librarian]);

        $this->actingAs($staff)
            ->post(route('admin.books.store'), array_merge($this->validBookPayload(), ['year' => 500]))
            ->assertSessionHasErrors('year');

        $this->actingAs($staff)
            ->post(route('admin.books.store'), array_merge($this->validBookPayload(), ['year' => 2200]))
            ->assertSessionHasErrors('year');
    }

    public function test_total_copies_must_be_non_negative_integer(): void
    {
        $staff = User::factory()->create(['role' => UserRole::Librarian]);
        $payload = array_merge($this->validBookPayload(), ['total_copies' => -1]);

        $response = $this->actingAs($staff)->post(route('admin.books.store'), $payload);

        $response->assertSessionHasErrors('total_copies');
    }

    public function test_isbn_must_be_unique(): void
    {
        $staff = User::factory()->create(['role' => UserRole::Librarian]);
        Book::query()->create([
            'title' => 'Existing Book',
            'author' => 'Author',
            'format' => BookFormat::Physical,
            'total_copies' => 1,
            'available_copies' => 1,
            'is_active' => true,
            'isbn' => '978-0-13-468599-1',
        ]);

        $payload = array_merge($this->validBookPayload(), ['isbn' => '978-0-13-468599-1']);

        $response = $this->actingAs($staff)->post(route('admin.books.store'), $payload);

        $response->assertSessionHasErrors('isbn');
    }

    public function test_isbn_uniqueness_ignores_current_book_on_update(): void
    {
        $staff = User::factory()->create(['role' => UserRole::Librarian]);
        $book = Book::query()->create([
            'title' => 'My Book',
            'author' => 'Author',
            'format' => BookFormat::Physical,
            'total_copies' => 1,
            'available_copies' => 1,
            'is_active' => true,
            'isbn' => '978-0-13-468599-1',
        ]);

        $payload = array_merge($this->validBookPayload(), ['isbn' => '978-0-13-468599-1', '_method' => 'PUT']);

        $response = $this->actingAs($staff)
            ->put(route('admin.books.update', $book), $payload);

        $response->assertSessionMissing('errors');
    }

    public function test_is_active_is_required(): void
    {
        $staff = User::factory()->create(['role' => UserRole::Librarian]);
        $payload = $this->validBookPayload();
        unset($payload['is_active']);

        $response = $this->actingAs($staff)->post(route('admin.books.store'), $payload);

        $response->assertSessionHasErrors('is_active');
    }

    /** @return array<string, mixed> */
    private function validBookPayload(): array
    {
        return [
            'title' => 'Valid Title',
            'author' => 'Valid Author',
            'publisher' => null,
            'year' => 2020,
            'language' => 'en',
            'isbn' => null,
            'description' => null,
            'format' => BookFormat::Physical->value,
            'total_copies' => 3,
            'is_active' => true,
            'category_ids' => [],
        ];
    }
}
