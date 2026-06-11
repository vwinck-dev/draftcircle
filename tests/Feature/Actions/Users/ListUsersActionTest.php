<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Users;

use App\Actions\Users\ListUsersAction;
use App\Enums\BookFormat;
use App\Enums\LoanStatus;
use App\Enums\LoanType;
use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ListUsersActionTest extends TestCase
{
    use RefreshDatabase;

    private ListUsersAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(ListUsersAction::class);
    }

    public function test_returns_all_users_paginated(): void
    {
        User::factory()->count(5)->create();

        $paginator = $this->action->execute();

        $this->assertSame(5, $paginator->total());
    }

    public function test_paginates_at_20_per_page(): void
    {
        User::factory()->count(25)->create();

        $paginator = $this->action->execute();

        $this->assertSame(20, $paginator->perPage());
        $this->assertCount(20, $paginator->items());
    }

    public function test_orders_users_alphabetically_by_name(): void
    {
        User::factory()->create(['name' => 'Zebra User']);
        User::factory()->create(['name' => 'Apple User']);

        $paginator = $this->action->execute();

        $this->assertSame('Apple User', $paginator->items()[0]->name);
        $this->assertSame('Zebra User', $paginator->items()[1]->name);
    }

    public function test_includes_loan_count_for_each_user(): void
    {
        $user = User::factory()->create();
        $book = Book::query()->create([
            'title' => 'Book',
            'author' => 'Author',
            'format' => BookFormat::Physical,
            'total_copies' => 1,
            'available_copies' => 0,
            'is_active' => true,
        ]);
        Loan::query()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'type' => LoanType::Loan,
            'status' => LoanStatus::Active,
            'reserved_at' => CarbonImmutable::now(),
            'due_at' => CarbonImmutable::now()->addDays(14),
        ]);

        $paginator = $this->action->execute();

        $found = collect($paginator->items())->firstWhere('id', $user->id);
        $this->assertNotNull($found);
        $this->assertSame(1, $found->loans_count);
    }
}
