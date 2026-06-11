<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Loans;

use App\Actions\Loans\ProcessPendingFinesAction;
use App\Enums\BookFormat;
use App\Enums\LoanStatus;
use App\Enums\LoanType;
use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

final class ProcessPendingFinesActionTest extends TestCase
{
    use RefreshDatabase;

    private ProcessPendingFinesAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(ProcessPendingFinesAction::class);
        Notification::fake();
    }

    public function test_generates_fine_for_each_overdue_loan(): void
    {
        $this->overdueLoan();
        $this->overdueLoan();

        $count = $this->action->execute(dailyAmount: 2.0);

        $this->assertSame(2, $count);
        $this->assertDatabaseCount('fines', 2);
    }

    public function test_does_not_process_active_loans(): void
    {
        $book = Book::query()->create([
            'title' => 'Book',
            'author' => 'Author',
            'format' => BookFormat::Physical,
            'total_copies' => 1,
            'available_copies' => 0,
            'is_active' => true,
        ]);

        Loan::query()->create([
            'user_id' => User::factory()->create()->id,
            'book_id' => $book->id,
            'type' => LoanType::Loan,
            'status' => LoanStatus::Active,
            'reserved_at' => CarbonImmutable::now()->subDays(14),
            'due_at' => CarbonImmutable::now()->addDay(),
        ]);

        $count = $this->action->execute(dailyAmount: 2.0);

        $this->assertSame(0, $count);
        $this->assertDatabaseCount('fines', 0);
    }

    public function test_returns_zero_when_no_overdue_loans(): void
    {
        $count = $this->action->execute(dailyAmount: 1.0);

        $this->assertSame(0, $count);
    }

    public function test_uses_provided_daily_amount(): void
    {
        $loan = $this->overdueLoan(daysOverdue: 2);

        $this->action->execute(dailyAmount: 5.0);

        $freshLoan = $loan->fresh();
        $this->assertNotNull($freshLoan);
        $fine = $freshLoan->fine;
        $this->assertNotNull($fine);
        $this->assertGreaterThanOrEqual(10.0, (float) $fine->amount);
    }

    private function overdueLoan(int $daysOverdue = 3): Loan
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
            'due_at' => CarbonImmutable::now()->subDays($daysOverdue),
        ]);
    }
}
