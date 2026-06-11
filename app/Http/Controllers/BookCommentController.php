<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookComment;
use App\Notifications\CommentReplyNotification;
use App\Services\CommentModerationService;
use App\Support\AuthenticatedUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class BookCommentController extends Controller
{
    public function __construct(private readonly CommentModerationService $moderation) {}

    public function store(Request $request, Book $book): RedirectResponse
    {
        $request->validate(['body' => ['required', 'string', 'max:2000']]);
        $body = $request->string('body')->trim()->toString();
        $this->ensureAllowed($body);
        $user = AuthenticatedUser::from($request);

        DB::transaction(function () use ($book, $body, $user): void {
            $comment = $book->comments()->create([
                'user_id' => $user->id,
                'body' => $body,
            ]);
            $comment->votes()->create(['user_id' => $user->id, 'value' => 1]);
        });

        return back();
    }

    public function reply(Request $request, BookComment $comment): RedirectResponse
    {
        $request->validate(['body' => ['required', 'string', 'max:2000']]);
        $body = $request->string('body')->trim()->toString();
        $this->ensureAllowed($body);
        $user = AuthenticatedUser::from($request);

        $reply = DB::transaction(function () use ($comment, $body, $user): BookComment {
            $reply = $comment->replies()->create([
                'book_id' => $comment->book_id,
                'user_id' => $user->id,
                'body' => $body,
            ]);
            $reply->votes()->create(['user_id' => $user->id, 'value' => 1]);

            return $reply;
        });

        if ($comment->user_id !== $reply->user_id) {
            $recipient = $comment->user()->firstOrFail();
            $recipient->notify(
                (new CommentReplyNotification($reply->load(['user', 'book'])))
                    ->locale($recipient->preferredLocale())
            );
        }

        return back();
    }

    public function destroy(Request $request, BookComment $comment): RedirectResponse
    {
        $user = AuthenticatedUser::from($request);

        if ($comment->user_id !== $user->id && ! $user->isStaff()) {
            abort(403);
        }

        $comment->delete();

        return back();
    }

    private function ensureAllowed(string $body): void
    {
        $blockedWord = $this->moderation->blockedWord($body);

        if ($blockedWord !== null) {
            throw ValidationException::withMessages([
                'body' => trans('ui.comments.blocked_word', ['word' => $blockedWord]),
            ]);
        }
    }
}
