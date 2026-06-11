<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Loans;

use App\Actions\Loans\SendOverdueNotificationsAction;
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

final class SendOverdueNotificationsActionTest extends TestCase
{
    use RefreshDatabase;

    private SendOverdueNotificationsAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(SendOverdueNotificationsAction::class);
        Notification::fake();
    }

    public function test_marks_active_past_due_loans_as_overdue(): void
    {
        $loan = $this->loan(LoanStatus::Active, dueAt: CarbonImmutable::now()->subDay());

        $this->action->execute();

        $this->assertDatabaseHas('loans', ['id' => $loan->id, 'status' => LoanStatus::Overdue->value]);
    }

    public function test_returns_count_of_processed_loans(): void
    {
        $this->loan(LoanStatus::Active, dueAt: CarbonImmutable::now()->subDay());
        $this->loan(LoanStatus::Active, dueAt: CarbonImmutable::now()->subDays(2));

        $count = $this->action->execute();

        $this->assertSame(2, $count);
    }

    public function test_also_processes_already_overdue_loans_not_yet_notified(): void
    {
        $loan = $this->loan(LoanStatus::Overdue, dueAt: CarbonImmutable::now()->subDays(3));

        $count = $this->action->execute();

        $this->assertSame(1, $count);
        $this->assertDatabaseHas('loans', ['id' => $loan->id, 'status' => LoanStatus::Overdue->value]);
    }

    public function test_ignores_loans_not_yet_due(): void
    {
        $this->loan(LoanStatus::Active, dueAt: CarbonImmutable::now()->addDay());

        $count = $this->action->execute();

        $this->assertSame(0, $count);
    }

    public function test_ignores_returned_loans(): void
    {
        $this->loan(LoanStatus::Returned, dueAt: CarbonImmutable::now()->subDay());

        $count = $this->action->execute();

        $this->assertSame(0, $count);
    }

    public function test_returns_zero_when_no_overdue_loans(): void
    {
        $count = $this->action->execute();

        $this->assertSame(0, $count);
    }

    private function loan(LoanStatus $status, CarbonImmutable $dueAt): Loan
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
            'status' => $status,
            'reserved_at' => CarbonImmutable::now()->subDays(14),
            'due_at' => $dueAt,
        ]);
    }
}
