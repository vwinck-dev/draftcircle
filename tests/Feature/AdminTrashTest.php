<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\BookFormat;
use App\Enums\LoanStatus;
use App\Enums\LoanType;
use App\Enums\UserRole;
use App\Models\Book;
use App\Models\BookComment;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

final class AdminTrashTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_restore_and_permanently_delete_books(): void
    {
        $staff = User::factory()->create(['role' => UserRole::Librarian]);
        $student = User::factory()->create();
        $book = $this->book();
        Loan::query()->create([
            'book_id' => $book->id,
            'user_id' => $student->id,
            'type' => LoanType::Loan,
            'status' => LoanStatus::Returned,
        ]);

        $this->actingAs($student)->get(route('admin.trash.index'))->assertForbidden();

        $this->actingAs($staff)
            ->delete(route('admin.books.destroy', $book))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSoftDeleted($book);
        $this->get(route('books.show', $book))->assertNotFound();
        $this->actingAs($student)->get(route('loans.index'))->assertOk();

        $this->actingAs($staff)->get(route('admin.trash.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/Trash/Index')
                ->has('books.data', 1)
                ->where('books.data.0.id', $book->id));

        $this->patch(route('admin.trash.books.restore', $book->id))->assertRedirect();
        $this->assertNotSoftDeleted($book);

        $this->delete(route('admin.books.destroy', $book))->assertRedirect();
        $this->delete(route('admin.trash.books.destroy', $book->id))->assertRedirect();
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    public function test_comment_replies_follow_the_parent_through_trash_and_permanent_deletion(): void
    {
        $author = User::factory()->create();
        $staff = User::factory()->create(['role' => UserRole::Librarian]);
        $book = $this->book();
        $comment = BookComment::query()->create([
            'book_id' => $book->id,
            'user_id' => $author->id,
            'body' => 'Parent comment.',
        ]);
        $reply = BookComment::query()->create([
            'book_id' => $book->id,
            'user_id' => $staff->id,
            'parent_id' => $comment->id,
            'body' => 'Reply.',
        ]);

        $this->actingAs($author)->delete(route('comments.destroy', $comment))->assertRedirect();
        $this->assertSoftDeleted($comment);
        $this->assertSoftDeleted($reply);

        $this->actingAs($staff)
            ->get(route('admin.trash.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('comments.data', 1)
                ->where('comments.data.0.id', $comment->id));

        $this->patch(route('admin.trash.comments.restore', $comment->id))->assertRedirect();
        $this->assertNotSoftDeleted($comment);
        $this->assertNotSoftDeleted($reply);

        $this->delete(route('admin.comments.destroy', $comment))->assertRedirect();
        $this->delete(route('admin.trash.comments.destroy', $comment->id))->assertRedirect();
        $this->assertDatabaseMissing('book_comments', ['id' => $comment->id]);
        $this->assertDatabaseMissing('book_comments', ['id' => $reply->id]);
    }

    public function test_recommendations_are_shared_only_on_discovery_pages(): void
    {
        $user = User::factory()->create();
        $this->book();

        $this->actingAs($user)
            ->get(route('home'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('recommendations.books', 1));

        $this->get(route('loans.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('recommendations.personalized', false)
                ->has('recommendations.books', 0));
    }

    private function book(): Book
    {
        return Book::query()->create([
            'title' => 'Recoverable Book',
            'author' => 'Careful Librarian',
            'format' => BookFormat::Both,
            'total_copies' => 3,
            'available_copies' => 3,
            'is_active' => true,
        ]);
    }
}
