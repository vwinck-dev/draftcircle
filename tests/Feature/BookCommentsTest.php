<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\BookFormat;
use App\Enums\SettingGroup;
use App\Enums\UserRole;
use App\Models\Book;
use App\Models\BookComment;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\CommentReplyNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

final class BookCommentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_comment_and_reply_at_any_depth_in_the_recipient_locale(): void
    {
        Notification::fake();
        $author = User::factory()->create(['locale' => 'pt_BR']);
        $replier = User::factory()->create(['locale' => 'en']);
        $book = $this->book();

        $this->actingAs($author)
            ->post(route('comments.store', $book), ['body' => 'Um comentário útil.'])
            ->assertRedirect();

        $comment = BookComment::query()->firstOrFail();
        $this->assertDatabaseHas('book_comment_votes', [
            'book_comment_id' => $comment->id,
            'user_id' => $author->id,
            'value' => 1,
        ]);

        $this->actingAs($replier)
            ->post(route('comments.reply', $comment), ['body' => 'Uma resposta útil.'])
            ->assertRedirect();

        $reply = BookComment::query()->whereNotNull('parent_id')->firstOrFail();

        Notification::assertSentTo(
            $author,
            CommentReplyNotification::class,
            fn (CommentReplyNotification $notification): bool => $notification->reply->is($reply)
                && $notification->locale === 'pt_BR',
        );

        $this->actingAs($author)
            ->post(route('comments.reply', $reply), ['body' => 'Uma resposta ainda mais profunda.'])
            ->assertRedirect();

        $this->actingAs($author)
            ->get(route('books.show', $book))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('comments', 1)
                ->has('comments.0.replies', 1)
                ->has('comments.0.replies.0.replies', 1)
                ->where('comments.0.replies.0.replies.0.body', 'Uma resposta ainda mais profunda.'));
    }

    public function test_users_can_upvote_downvote_and_toggle_comment_votes(): void
    {
        $author = User::factory()->create();
        $reader = User::factory()->create();
        $book = $this->book();

        $this->actingAs($author)
            ->post(route('comments.store', $book), ['body' => 'Vote neste comentário.'])
            ->assertRedirect();

        $comment = BookComment::query()->firstOrFail();

        $this->actingAs($reader)
            ->put(route('comments.vote', $comment), ['value' => -1])
            ->assertRedirect();
        $this->assertSame(0, (int) $comment->votes()->sum('value'));

        $this->put(route('comments.vote', $comment), ['value' => -1])->assertRedirect();
        $this->assertSame(1, (int) $comment->votes()->sum('value'));

        $this->actingAs($author)
            ->put(route('comments.vote', $comment), ['value' => -1])
            ->assertRedirect();

        $this->get(route('books.show', $book))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('comments.0.score', -1)
                ->where('comments.0.user_vote', -1));
    }

    public function test_configured_blocked_words_prevent_comments_and_identify_the_term(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $reader = User::factory()->create();
        $book = $this->book();

        Setting::query()->create([
            'key' => 'library_name',
            'value' => 'Library',
            'group' => SettingGroup::General,
            'type' => 'string',
        ]);

        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'group' => 'general',
            'settings' => ['blocked_comment_words' => "xgh\nextreme go horse"],
        ])->assertRedirect();

        $response = $this->actingAs($reader)
            ->post(route('comments.store', $book), ['body' => 'Essa XGH! não passa.']);

        $response->assertSessionHasErrors([
            'body' => trans('ui.comments.blocked_word', ['word' => 'xgh']),
        ]);
        $this->assertDatabaseCount('book_comments', 0);
    }

    public function test_deleting_and_restoring_a_comment_affects_the_whole_deep_thread(): void
    {
        $author = User::factory()->create();
        $book = $this->book();
        $root = BookComment::query()->create([
            'book_id' => $book->id,
            'user_id' => $author->id,
            'body' => 'Root.',
        ]);
        $reply = $root->replies()->create([
            'book_id' => $book->id,
            'user_id' => $author->id,
            'body' => 'Reply.',
        ]);
        $deepReply = $reply->replies()->create([
            'book_id' => $book->id,
            'user_id' => $author->id,
            'body' => 'Deep reply.',
        ]);

        $this->actingAs($author)->delete(route('comments.destroy', $root))->assertRedirect();

        $this->assertSoftDeleted($root);
        $this->assertSoftDeleted($reply);
        $this->assertSoftDeleted($deepReply);

        $root->restore();

        $this->assertNotSoftDeleted($root);
        $this->assertNotSoftDeleted($reply);
        $this->assertNotSoftDeleted($deepReply);
    }

    public function test_comment_deletion_respects_ownership_and_staff_permissions(): void
    {
        $author = User::factory()->create();
        $other = User::factory()->create();
        $librarian = User::factory()->create(['role' => UserRole::Librarian]);
        $book = $this->book();
        $comment = BookComment::query()->create([
            'book_id' => $book->id,
            'user_id' => $author->id,
            'body' => 'Keep or remove me.',
        ]);

        $this->actingAs($other)->delete(route('comments.destroy', $comment))->assertForbidden();
        $this->assertDatabaseHas('book_comments', ['id' => $comment->id]);

        $this->actingAs($librarian)->delete(route('comments.destroy', $comment))->assertRedirect();
        $this->assertSoftDeleted($comment);
    }

    public function test_staff_can_moderate_comments_and_hidden_comments_leave_the_catalog(): void
    {
        $student = User::factory()->create();
        $librarian = User::factory()->create(['role' => UserRole::Librarian]);
        $book = $this->book();
        $comment = BookComment::query()->create([
            'book_id' => $book->id,
            'user_id' => $student->id,
            'body' => 'Moderate me.',
        ]);

        $this->actingAs($student)->get(route('admin.comments.index'))->assertForbidden();

        $this->actingAs($librarian)
            ->get(route('admin.comments.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/Comments/Index')
                ->has('comments.data', 1)
                ->where('comments.data.0.id', $comment->id));

        $this->patch(route('admin.comments.update', $comment))->assertRedirect();
        $this->assertTrue($comment->refresh()->is_hidden);

        $this->get(route('books.show', $book))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Books/Show')
                ->has('comments', 0));

        $this->get(route('admin.comments.index', ['status' => 'hidden']))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('filters.status', 'hidden')
                ->has('comments.data', 1));

        $this->patch(route('admin.comments.update', $comment))->assertRedirect();
        $this->assertFalse($comment->refresh()->is_hidden);

        $this->delete(route('admin.comments.destroy', $comment))->assertRedirect();
        $this->assertSoftDeleted($comment);
    }

    public function test_hidden_replies_are_not_exposed_with_visible_comments(): void
    {
        $author = User::factory()->create();
        $book = $this->book();
        $comment = BookComment::query()->create([
            'book_id' => $book->id,
            'user_id' => $author->id,
            'body' => 'Visible comment.',
        ]);

        BookComment::query()->create([
            'book_id' => $book->id,
            'user_id' => $author->id,
            'parent_id' => $comment->id,
            'body' => 'Visible reply.',
        ]);
        BookComment::query()->create([
            'book_id' => $book->id,
            'user_id' => $author->id,
            'parent_id' => $comment->id,
            'body' => 'Hidden reply.',
            'is_hidden' => true,
        ]);

        $this->actingAs($author)
            ->get(route('books.show', $book))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('comments', 1)
                ->has('comments.0.replies', 1)
                ->where('comments.0.replies.0.body', 'Visible reply.'));
    }

    private function book(): Book
    {
        return Book::query()->create([
            'title' => 'The Commented Book',
            'author' => 'Reader One',
            'format' => BookFormat::Both,
            'total_copies' => 3,
            'available_copies' => 3,
            'is_active' => true,
        ]);
    }
}
