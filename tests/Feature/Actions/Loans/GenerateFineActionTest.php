<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Loans;

use App\Actions\Loans\GenerateFineAction;
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

final class GenerateFineActionTest extends TestCase
{
    use RefreshDatabase;

    private GenerateFineAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(GenerateFineAction::class);
    }

    public function test_creates_fine_for_overdue_loan(): void
    {
        $loan = $this->overdueLoan(daysOverdue: 3);

        $fine = $this->action->execute(loan: $loan, dailyAmount: 2.0);

        $this->assertInstanceOf(Fine::class, $fine);
        $this->assertSame($loan->id, $fine->loan_id);
        $this->assertSame($loan->user_id, $fine->user_id);
    }

    public function test_calculates_amount_from_days_overdue_and_daily_rate(): void
    {
        $loan = $this->overdueLoan(daysOverdue: 3);

        $fine = $this->action->execute(loan: $loan, dailyAmount: 2.0);

        $this->assertGreaterThanOrEqual(6.0, (float) $fine->amount);
    }

    public function test_minimum_fine_is_one_day_amount(): void
    {
        $loan = $this->overdueLoan(daysOverdue: 0);

        $fine = $this->action->execute(loan: $loan, dailyAmount: 5.0);

        $this->assertGreaterThanOrEqual(5.0, (float) $fine->amount);
    }

    public function test_updates_existing_fine_instead_of_creating_duplicate(): void
    {
        $loan = $this->overdueLoan(daysOverdue: 1);

        $this->action->execute(loan: $loan, dailyAmount: 1.0);
        $this->action->execute(loan: $loan, dailyAmount: 1.0);

        $this->assertDatabaseCount('fines', 1);
    }

    public function test_fine_amount_increases_on_subsequent_generation(): void
    {
        $loan = $this->overdueLoan(daysOverdue: 2);

        $first = $this->action->execute(loan: $loan, dailyAmount: 1.0);

        $loan->update(['due_at' => CarbonImmutable::now()->subDays(5)]);
        $refreshedLoan = $loan->fresh();
        $this->assertNotNull($refreshedLoan);
        $second = $this->action->execute(loan: $refreshedLoan, dailyAmount: 1.0);

        $this->assertGreaterThan((float) $first->amount, (float) $second->amount);
    }

    public function test_fine_is_persisted_in_database(): void
    {
        $loan = $this->overdueLoan(daysOverdue: 2);

        $fine = $this->action->execute(loan: $loan, dailyAmount: 3.0);

        $this->assertDatabaseHas('fines', ['id' => $fine->id, 'loan_id' => $loan->id]);
    }

    private function overdueLoan(int $daysOverdue): Loan
    {
        $book = Book::query()->create([
            'title' => 'Book',
            'author' => 'Author',
            'format' => BookFormat::Physical,
            'total_copies' => 1,
            'available_copies' => 0,
            'is_active' => true,
        ]);

        return Loan::query()->create([
            'user_id' => User::factory()->create()->id,
            'book_id' => $book->id,
            'type' => LoanType::Loan,
            'status' => LoanStatus::Overdue,
            'reserved_at' => CarbonImmutable::now()->subDays($daysOverdue + 14),
            'due_at' => $daysOverdue > 0
                ? CarbonImmutable::now()->subDays($daysOverdue)
                : CarbonImmutable::now()->addMinutes(1),
        ]);
    }
}
