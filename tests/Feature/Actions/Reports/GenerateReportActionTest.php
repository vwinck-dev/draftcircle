<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Reports;

use App\Actions\Reports\GenerateReportAction;
use App\DataTransferObjects\ReportData;
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

final class GenerateReportActionTest extends TestCase
{
    use RefreshDatabase;

    private GenerateReportAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(GenerateReportAction::class);
    }

    public function test_returns_report_data_instance(): void
    {
        $report = $this->action->execute(period: 'weekly');

        $this->assertInstanceOf(ReportData::class, $report);
    }

    public function test_sets_period_on_report(): void
    {
        $report = $this->action->execute(period: 'monthly');

        $this->assertSame('monthly', $report->period);
    }

    public function test_counts_books_in_metrics(): void
    {
        $this->book();
        $this->book();

        $report = $this->action->execute(period: 'weekly');

        $this->assertSame(2, $report->metrics['books']);
    }

    public function test_counts_users_in_metrics(): void
    {
        User::factory()->count(3)->create();

        $report = $this->action->execute(period: 'weekly');

        $this->assertSame(3, $report->metrics['users']);
    }

    public function test_counts_loans_in_metrics(): void
    {
        $book = $this->book();
        Loan::query()->create([
            'user_id' => User::factory()->create()->id,
            'book_id' => $book->id,
            'type' => LoanType::Loan,
            'status' => LoanStatus::Active,
            'reserved_at' => CarbonImmutable::now(),
            'due_at' => CarbonImmutable::now()->addDays(14),
        ]);

        $report = $this->action->execute(period: 'weekly');

        $this->assertSame(1, $report->metrics['loans']);
    }

    public function test_sums_outstanding_fines_in_metrics(): void
    {
        $book = $this->book();
        $loan = Loan::query()->create([
            'user_id' => User::factory()->create()->id,
            'book_id' => $book->id,
            'type' => LoanType::Loan,
            'status' => LoanStatus::Overdue,
            'reserved_at' => CarbonImmutable::now()->subDays(20),
            'due_at' => CarbonImmutable::now()->subDays(5),
        ]);
        Fine::query()->create(['loan_id' => $loan->id, 'user_id' => $loan->user_id, 'amount' => 12.50]);

        $report = $this->action->execute(period: 'weekly');

        $this->assertEqualsWithDelta(12.50, $report->metrics['outstanding_fines'], 0.01);
    }

    public function test_includes_generated_at_timestamp(): void
    {
        $report = $this->action->execute(period: 'daily');

        $this->assertNotEmpty($report->generatedAt);
    }

    private function book(): Book
    {
        return Book::query()->create([
            'title' => 'Book',
            'author' => 'Author',
            'format' => BookFormat::Physical,
            'total_copies' => 1,
            'available_copies' => 1,
            'is_active' => true,
        ]);
    }
}
