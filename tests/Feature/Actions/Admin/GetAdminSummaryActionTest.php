<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Admin;

use App\Actions\Admin\GetAdminSummaryAction;
use App\Enums\BookFormat;
use App\Enums\LoanStatus;
use App\Enums\LoanType;
use App\Models\Book;
use App\Models\Fine;
use App\Models\Loan;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GetAdminSummaryActionTest extends TestCase
{
    use RefreshDatabase;

    private GetAdminSummaryAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(GetAdminSummaryAction::class);
    }

    public function test_counts_total_books(): void
    {
        $this->book();
        $this->book();

        $summary = $this->action->execute();

        $this->assertSame(2, $summary['books']);
    }

    public function test_counts_total_users(): void
    {
        User::factory()->count(3)->create();

        $summary = $this->action->execute();

        $this->assertSame(3, $summary['users']);
    }

    public function test_counts_active_loans(): void
    {
        $this->loan(LoanStatus::Active);
        $this->loan(LoanStatus::Overdue);
        $this->loan(LoanStatus::Returned);

        $summary = $this->action->execute();

        $this->assertSame(2, $summary['activeLoans']);
    }

    public function test_counts_overdue_loans(): void
    {
        $this->loan(LoanStatus::Overdue);
        $this->loan(LoanStatus::Overdue);
        $this->loan(LoanStatus::Active);

        $summary = $this->action->execute();

        $this->assertSame(2, $summary['overdueLoans']);
    }

    public function test_counts_pending_reservations(): void
    {
        $this->loan(LoanStatus::Pending);
        $this->loan(LoanStatus::Active);

        $summary = $this->action->execute();

        $this->assertSame(1, $summary['pendingReservations']);
    }

    public function test_counts_available_copies(): void
    {
        $this->book(['available_copies' => 3]);
        $this->book(['available_copies' => 2]);

        $summary = $this->action->execute();

        $this->assertSame(5, $summary['availableCopies']);
    }

    public function test_sums_outstanding_fines(): void
    {
        $loan = $this->loan(LoanStatus::Overdue);
        Fine::query()->create(['loan_id' => $loan->id, 'user_id' => $loan->user_id, 'amount' => 15.50]);

        $summary = $this->action->execute();

        $this->assertEqualsWithDelta(15.50, $summary['outstandingFines'], 0.01);
    }

    public function test_includes_popular_books_ordered_by_loan_count(): void
    {
        $popular = $this->book(['title' => 'Popular', 'loan_count' => 100]);
        $this->book(['title' => 'Niche', 'loan_count' => 1]);

        $summary = $this->action->execute();

        $topBook = $summary['popularBooks']->first();
        $this->assertNotNull($topBook);
        $this->assertSame($popular->id, $topBook->id);
    }

    public function test_limits_popular_books_to_5(): void
    {
        for ($i = 0; $i < 8; $i++) {
            $this->book(['title' => "Book {$i}", 'loan_count' => $i]);
        }

        $summary = $this->action->execute();

        $this->assertCount(5, $summary['popularBooks']);
    }

    public function test_includes_recent_loans(): void
    {
        $loan = $this->loan(LoanStatus::Active);

        $summary = $this->action->execute();

        $recentLoan = $summary['recentLoans']->first();
        $this->assertNotNull($recentLoan);
        $this->assertSame($loan->id, $recentLoan['id']);
        $this->assertArrayHasKey('book', $recentLoan);
        $this->assertArrayHasKey('user', $recentLoan);
        $this->assertArrayHasKey('status', $recentLoan);
    }

    public function test_summary_keys_are_all_present(): void
    {
        $summary = $this->action->execute();

        $expectedKeys = [
            'books', 'users', 'activeLoans', 'overdueLoans', 'pendingReservations',
            'returnedThisMonth', 'newUsersThisMonth', 'availableCopies',
            'outstandingFines', 'popularBooks', 'recentLoans',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $summary);
        }
    }

    private function loan(LoanStatus $status): Loan
    {
        $book = $this->book();

        return Loan::query()->create([
            'user_id' => User::factory()->create()->id,
            'book_id' => $book->id,
            'type' => LoanType::Loan,
            'status' => $status,
            'reserved_at' => CarbonImmutable::now(),
            'due_at' => CarbonImmutable::now()->addDays(14),
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
            'loan_count' => 0,
            'is_active' => true,
        ], $overrides));
    }
}
