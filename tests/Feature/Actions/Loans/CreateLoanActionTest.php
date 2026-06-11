<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Loans;

use App\Actions\Loans\CreateLoanAction;
use App\Enums\BookFormat;
use App\Enums\LoanStatus;
use App\Enums\LoanType;
use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CreateLoanActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateLoanAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(CreateLoanAction::class);
    }

    public function test_creates_reservation_with_pending_status(): void
    {
        $user = User::factory()->create();
        $book = $this->book();

        $loan = $this->action->execute(user: $user, book: $book, type: LoanType::Reservation);

        $this->assertSame(LoanStatus::Pending, $loan->status);
        $this->assertSame(LoanType::Reservation, $loan->type);
        $this->assertNull($loan->due_at);
    }

    public function test_creates_loan_with_active_status_and_due_date(): void
    {
        $user = User::factory()->create();
        $book = $this->book();

        $loan = $this->action->execute(user: $user, book: $book, type: LoanType::Loan);

        $this->assertSame(LoanStatus::Active, $loan->status);
        $this->assertSame(LoanType::Loan, $loan->type);
        $this->assertNotNull($loan->due_at);
        $this->assertEqualsWithDelta(14, now()->diffInDays($loan->due_at), 1);
    }

    public function test_decrements_available_copies_on_reservation(): void
    {
        $user = User::factory()->create();
        $book = $this->book(['available_copies' => 3]);

        $this->action->execute(user: $user, book: $book, type: LoanType::Reservation);

        $this->assertSame(2, $book->refresh()->available_copies);
    }

    public function test_increments_loan_count_on_creation(): void
    {
        $user = User::factory()->create();
        $book = $this->book(['loan_count' => 0]);

        $this->action->execute(user: $user, book: $book, type: LoanType::Reservation);

        $this->assertSame(1, $book->refresh()->loan_count);
    }

    public function test_loan_is_persisted_in_database(): void
    {
        $user = User::factory()->create();
        $book = $this->book();

        $loan = $this->action->execute(user: $user, book: $book, type: LoanType::Loan);

        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => LoanStatus::Active->value,
        ]);
    }

    public function test_loan_loads_book_and_user_relations(): void
    {
        $user = User::factory()->create();
        $book = $this->book();

        $loan = $this->action->execute(user: $user, book: $book, type: LoanType::Loan);

        $this->assertNotNull($loan->user);
        $this->assertNotNull($loan->book);
        $this->assertSame($user->id, $loan->user->id);
        $this->assertSame($book->id, $loan->book->id);
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
            'loan_count' => 0,
            'is_active' => true,
        ], $overrides));
    }
}
