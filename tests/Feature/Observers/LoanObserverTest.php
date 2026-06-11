<?php

declare(strict_types=1);

namespace Tests\Feature\Observers;

use App\Enums\BookFormat;
use App\Enums\LoanStatus;
use App\Enums\LoanType;
use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class LoanObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_flushes_admin_summary_cache_when_loan_is_created(): void
    {
        Cache::put('admin:summary', ['cached' => true], 300);
        $book = $this->book();

        Loan::query()->create([
            'user_id' => User::factory()->create()->id,
            'book_id' => $book->id,
            'type' => LoanType::Loan,
            'status' => LoanStatus::Active,
            'reserved_at' => CarbonImmutable::now(),
            'due_at' => CarbonImmutable::now()->addDays(14),
        ]);

        $this->assertNull(Cache::get('admin:summary'));
    }

    public function test_flushes_admin_summary_cache_when_loan_is_updated(): void
    {
        $loan = $this->loan();
        Cache::put('admin:summary', ['cached' => true], 300);

        $loan->update(['status' => LoanStatus::Returned]);

        $this->assertNull(Cache::get('admin:summary'));
    }

    public function test_flushes_admin_summary_cache_when_loan_status_changes_to_overdue(): void
    {
        $loan = $this->loan();
        Cache::put('admin:summary', ['cached' => true], 300);

        $loan->update(['status' => LoanStatus::Overdue]);

        $this->assertNull(Cache::get('admin:summary'));
    }

    private function loan(): Loan
    {
        $book = $this->book();

        return Loan::query()->create([
            'user_id' => User::factory()->create()->id,
            'book_id' => $book->id,
            'type' => LoanType::Loan,
            'status' => LoanStatus::Active,
            'reserved_at' => CarbonImmutable::now(),
            'due_at' => CarbonImmutable::now()->addDays(14),
        ]);
    }

    private function book(): Book
    {
        return Book::query()->create([
            'title' => 'Book',
            'author' => 'Author',
            'format' => BookFormat::Physical,
            'total_copies' => 3,
            'available_copies' => 3,
            'is_active' => true,
        ]);
    }
}
