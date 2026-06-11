<?php

declare(strict_types=1);

namespace Tests\Feature\Policies;

use App\Enums\BookFormat;
use App\Enums\UserRole;
use App\Models\Book;
use App\Models\User;
use App\Policies\BookPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class BookPolicyTest extends TestCase
{
    use RefreshDatabase;

    private BookPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new BookPolicy;
    }

    public function test_any_user_can_view_any_book(): void
    {
        $student = User::factory()->create(['role' => UserRole::Student]);

        $this->assertTrue($this->policy->viewAny($student));
    }

    public function test_any_user_can_view_single_book(): void
    {
        $student = User::factory()->create(['role' => UserRole::Student]);
        $book = $this->book();

        $this->assertTrue($this->policy->view($student, $book));
    }

    public function test_student_cannot_create_book(): void
    {
        $student = User::factory()->create(['role' => UserRole::Student]);

        $this->assertFalse($this->policy->create($student));
    }

    public function test_librarian_can_create_book(): void
    {
        $librarian = User::factory()->create(['role' => UserRole::Librarian]);

        $this->assertTrue($this->policy->create($librarian));
    }

    public function test_admin_can_create_book(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->assertTrue($this->policy->create($admin));
    }

    public function test_student_cannot_update_book(): void
    {
        $student = User::factory()->create(['role' => UserRole::Student]);
        $book = $this->book();

        $this->assertFalse($this->policy->update($student, $book));
    }

    public function test_librarian_can_update_book(): void
    {
        $librarian = User::factory()->create(['role' => UserRole::Librarian]);
        $book = $this->book();

        $this->assertTrue($this->policy->update($librarian, $book));
    }

    public function test_student_cannot_delete_book(): void
    {
        $student = User::factory()->create(['role' => UserRole::Student]);
        $book = $this->book();

        $this->assertFalse($this->policy->delete($student, $book));
    }

    public function test_admin_can_delete_book(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $book = $this->book();

        $this->assertTrue($this->policy->delete($admin, $book));
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
