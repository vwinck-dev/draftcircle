<?php

declare(strict_types=1);

namespace Tests\Feature\Policies;

use App\Enums\BookFormat;
use App\Enums\LoanStatus;
use App\Enums\LoanType;
use App\Enums\UserRole;
use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use App\Policies\LoanPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LoanPolicyTest extends TestCase
{
    use RefreshDatabase;

    private LoanPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new LoanPolicy;
    }

    public function test_any_user_can_view_any_loan_list(): void
    {
        $user = User::factory()->create(['role' => UserRole::Student]);

        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_can_create_loan_for_active_physical_book_with_copies(): void
    {
        $user = User::factory()->create();
        $book = $this->book(['format' => BookFormat::Physical, 'available_copies' => 1, 'is_active' => true]);

        $this->assertTrue($this->policy->create($user, $book));
    }

    public function test_cannot_create_loan_for_inactive_book(): void
    {
        $user = User::factory()->create();
        $book = $this->book(['is_active' => false]);

        $this->assertFalse($this->policy->create($user, $book));
    }

    public function test_cannot_create_loan_for_digital_only_book(): void
    {
        $user = User::factory()->create();
        $book = $this->book(['format' => BookFormat::Digital]);

        $this->assertFalse($this->policy->create($user, $book));
    }

    public function test_cannot_create_loan_when_no_copies_available(): void
    {
        $user = User::factory()->create();
        $book = $this->book(['available_copies' => 0]);

        $this->assertFalse($this->policy->create($user, $book));
    }

    public function test_can_create_loan_for_both_format_book(): void
    {
        $user = User::factory()->create();
        $book = $this->book(['format' => BookFormat::Both, 'available_copies' => 1, 'is_active' => true]);

        $this->assertTrue($this->policy->create($user, $book));
    }

    public function test_staff_can_view_any_loan(): void
    {
        $staff = User::factory()->create(['role' => UserRole::Librarian]);
        $student = User::factory()->create(['role' => UserRole::Student]);
        $loan = $this->loan($student);

        $this->assertTrue($this->policy->view($staff, $loan));
    }

    public function test_owner_can_view_own_loan(): void
    {
        $student = User::factory()->create(['role' => UserRole::Student]);
        $loan = $this->loan($student);

        $this->assertTrue($this->policy->view($student, $loan));
    }

    public function test_other_student_cannot_view_loan(): void
    {
        $owner = User::factory()->create(['role' => UserRole::Student]);
        $other = User::factory()->create(['role' => UserRole::Student]);
        $loan = $this->loan($owner);

        $this->assertFalse($this->policy->view($other, $loan));
    }

    public function test_staff_can_update_any_loan(): void
    {
        $staff = User::factory()->create(['role' => UserRole::Admin]);
        $student = User::factory()->create();
        $loan = $this->loan($student);

        $this->assertTrue($this->policy->update($staff, $loan));
    }

    public function test_owner_can_update_own_loan(): void
    {
        $student = User::factory()->create();
        $loan = $this->loan($student);

        $this->assertTrue($this->policy->update($student, $loan));
    }

    public function test_other_user_cannot_update_loan(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $loan = $this->loan($owner);

        $this->assertFalse($this->policy->update($other, $loan));
    }

    private function loan(User $user): Loan
    {
        $book = $this->book();

        return Loan::query()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'type' => LoanType::Loan,
            'status' => LoanStatus::Active,
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
            'is_active' => true,
        ], $overrides));
    }
}
