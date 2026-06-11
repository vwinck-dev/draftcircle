<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Loans;

use App\Actions\Loans\CleanExpiredReservationsAction;
use App\Enums\BookFormat;
use App\Enums\LoanStatus;
use App\Enums\LoanType;
use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CleanExpiredReservationsActionTest extends TestCase
{
    use RefreshDatabase;

    private CleanExpiredReservationsAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(CleanExpiredReservationsAction::class);
    }

    public function test_cancels_reservation_older_than_48_hours(): void
    {
        $book = $this->book();
        $this->reservation(book: $book, reservedHoursAgo: 49);

        $count = $this->action->execute();

        $this->assertSame(1, $count);
        $this->assertDatabaseHas('loans', ['book_id' => $book->id, 'status' => LoanStatus::Cancelled->value]);
    }

    public function test_restores_available_copies_when_cancelling(): void
    {
        $book = $this->book(['available_copies' => 2]);
        $this->reservation(book: $book, reservedHoursAgo: 50);

        $this->action->execute();

        $this->assertSame(3, $book->refresh()->available_copies);
    }

    public function test_does_not_cancel_reservation_within_48_hours(): void
    {
        $book = $this->book();
        $this->reservation(book: $book, reservedHoursAgo: 47);

        $count = $this->action->execute();

        $this->assertSame(0, $count);
        $this->assertDatabaseHas('loans', ['book_id' => $book->id, 'status' => LoanStatus::Pending->value]);
    }

    public function test_does_not_touch_active_loans(): void
    {
        $book = $this->book();
        Loan::query()->create([
            'user_id' => User::factory()->create()->id,
            'book_id' => $book->id,
            'type' => LoanType::Loan,
            'status' => LoanStatus::Active,
            'reserved_at' => CarbonImmutable::now()->subDays(10),
            'due_at' => CarbonImmutable::now()->subDay(),
        ]);

        $count = $this->action->execute();

        $this->assertSame(0, $count);
    }

    public function test_processes_multiple_expired_reservations(): void
    {
        $book = $this->book(['available_copies' => 1]);
        $this->reservation(book: $book, reservedHoursAgo: 72);
        $this->reservation(book: $book, reservedHoursAgo: 96);

        $count = $this->action->execute();

        $this->assertSame(2, $count);
    }

    public function test_returns_zero_when_no_expired_reservations(): void
    {
        $count = $this->action->execute();

        $this->assertSame(0, $count);
    }

    private function reservation(Book $book, int $reservedHoursAgo): Loan
    {
        return Loan::query()->create([
            'user_id' => User::factory()->create()->id,
            'book_id' => $book->id,
            'type' => LoanType::Reservation,
            'status' => LoanStatus::Pending,
            'reserved_at' => CarbonImmutable::now()->subHours($reservedHoursAgo),
        ]);
    }

    /** @param array<string, mixed> $overrides */
    private function book(array $overrides = []): Book
    {
        return Book::query()->create(array_merge([
            'title' => 'Book',
            'author' => 'Author',
            'format' => BookFormat::Physical,
            'total_copies' => 3,
            'available_copies' => 3,
            'is_active' => true,
        ], $overrides));
    }
}
