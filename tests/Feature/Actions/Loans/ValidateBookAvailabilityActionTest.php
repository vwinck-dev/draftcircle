<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Loans;

use App\Actions\Loans\ValidateBookAvailabilityAction;
use App\Enums\BookFormat;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class ValidateBookAvailabilityActionTest extends TestCase
{
    use RefreshDatabase;

    private ValidateBookAvailabilityAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(ValidateBookAvailabilityAction::class);
    }

    public function test_passes_for_active_physical_book_with_copies(): void
    {
        $this->expectNotToPerformAssertions();
        $book = $this->book();

        $this->action->execute(book: $book);
    }

    public function test_throws_when_book_is_inactive(): void
    {
        $book = $this->book(['is_active' => false]);

        $this->expectException(ValidationException::class);

        $this->action->execute(book: $book);
    }

    public function test_throws_when_book_is_digital_only(): void
    {
        $book = $this->book(['format' => BookFormat::Digital]);

        $this->expectException(ValidationException::class);

        $this->action->execute(book: $book);
    }

    public function test_throws_when_no_available_copies(): void
    {
        $book = $this->book(['available_copies' => 0]);

        $this->expectException(ValidationException::class);

        $this->action->execute(book: $book);
    }

    public function test_passes_for_both_format_with_copies(): void
    {
        $this->expectNotToPerformAssertions();
        $book = $this->book(['format' => BookFormat::Both]);

        $this->action->execute(book: $book);
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
