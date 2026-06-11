<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Loans;

use App\Actions\Loans\ListUserLoansAction;
use App\Enums\BookFormat;
use App\Enums\LoanStatus;
use App\Enums\LoanType;
use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ListUserLoansActionTest extends TestCase
{
    use RefreshDatabase;

    private ListUserLoansAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(ListUserLoansAction::class);
    }

    public function test_returns_only_loans_belonging_to_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->createLoan($user);
        $this->createLoan($other);

        $loans = $this->action->execute(user: $user);

        $this->assertCount(1, $loans);
        $firstLoan = $loans->first();
        $this->assertNotNull($firstLoan);
        $this->assertSame($user->id, $firstLoan->user_id);
    }

    public function test_returns_all_loans_when_all_flag_is_true(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->createLoan($user);
        $this->createLoan($other);

        $loans = $this->action->execute(user: $user, all: true);

        $this->assertCount(2, $loans);
    }

    public function test_returns_loans_ordered_by_most_recent_first(): void
    {
        $user = User::factory()->create();

        $this->travelTo(now()->subHour());
        $first = $this->createLoan($user);

        $this->travelBack();
        $second = $this->createLoan($user);

        $loans = $this->action->execute(user: $user);

        $firstLoan = $loans->first();
        $lastLoan = $loans->last();
        $this->assertNotNull($firstLoan);
        $this->assertNotNull($lastLoan);
        $this->assertSame($second->id, $firstLoan->id);
        $this->assertSame($first->id, $lastLoan->id);
    }

    public function test_eager_loads_book_categories_and_fine(): void
    {
        $user = User::factory()->create();
        $this->createLoan($user);

        $loans = $this->action->execute(user: $user);

        $loan = $loans->first();
        $this->assertNotNull($loan);
        $this->assertTrue($loan->relationLoaded('book'));
        $this->assertTrue($loan->relationLoaded('user'));
        $this->assertTrue($loan->relationLoaded('fine'));
    }

    public function test_returns_empty_collection_when_user_has_no_loans(): void
    {
        $user = User::factory()->create();

        $loans = $this->action->execute(user: $user);

        $this->assertCount(0, $loans);
    }

    private function createLoan(User $user): Loan
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
            'user_id' => $user->id,
            'book_id' => $book->id,
            'type' => LoanType::Loan,
            'status' => LoanStatus::Active,
            'reserved_at' => CarbonImmutable::now(),
            'due_at' => CarbonImmutable::now()->addDays(14),
        ]);
    }
}
