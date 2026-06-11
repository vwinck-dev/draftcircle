<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Loans;

use App\Actions\Loans\ReturnLoanAction;
use App\Enums\BookFormat;
use App\Enums\LoanStatus;
use App\Enums\LoanType;
use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class ReturnLoanActionTest extends TestCase
{
    use RefreshDatabase;

    private ReturnLoanAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(ReturnLoanAction::class);
    }

    public function test_marks_active_loan_as_returned(): void
    {
        $loan = $this->loan(LoanStatus::Active);

        $returned = $this->action->execute(loan: $loan);

        $this->assertSame(LoanStatus::Returned, $returned->status);
        $this->assertNotNull($returned->returned_at);
    }

    public function test_marks_overdue_loan_as_returned(): void
    {
        $loan = $this->loan(LoanStatus::Overdue);

        $returned = $this->action->execute(loan: $loan);

        $this->assertSame(LoanStatus::Returned, $returned->status);
    }

    public function test_increments_available_copies_on_return(): void
    {
        $book = $this->book(['available_copies' => 1]);
        $loan = $this->loan(LoanStatus::Active, book: $book);

        $this->action->execute(loan: $loan);

        $this->assertSame(2, $book->refresh()->available_copies);
    }

    public function test_throws_when_loan_is_not_active_or_overdue(): void
    {
        $loan = $this->loan(LoanStatus::Returned);

        $this->expectException(ValidationException::class);

        $this->action->execute(loan: $loan);
    }

    public function test_throws_when_loan_is_pending(): void
    {
        $loan = $this->loan(LoanStatus::Pending);

        $this->expectException(ValidationException::class);

        $this->action->execute(loan: $loan);
    }

    public function test_throws_when_loan_is_cancelled(): void
    {
        $loan = $this->loan(LoanStatus::Cancelled);

        $this->expectException(ValidationException::class);

        $this->action->execute(loan: $loan);
    }

    public function test_returned_at_is_set_to_now(): void
    {
        $before = CarbonImmutable::now();
        $loan = $this->loan(LoanStatus::Active);

        $returned = $this->action->execute(loan: $loan);

        $this->assertGreaterThanOrEqual($before->timestamp, $returned->returned_at?->timestamp);
    }

    private function loan(LoanStatus $status, ?Book $book = null): Loan
    {
        $book ??= $this->book();

        return Loan::query()->create([
            'user_id' => User::factory()->create()->id,
            'book_id' => $book->id,
            'type' => LoanType::Loan,
            'status' => $status,
            'reserved_at' => CarbonImmutable::now()->subDays(3),
            'due_at' => CarbonImmutable::now()->addDay(),
        ]);
    }

    /** @param array<string, mixed> $overrides */
    private function book(array $overrides = []): Book
    {
        return Book::query()->create(array_merge([
            'title' => 'Test Book',
            'author' => 'Author',
            'format' => BookFormat::Physical,
            'total_copies' => 3,
            'available_copies' => 2,
            'is_active' => true,
        ], $overrides));
    }
}
