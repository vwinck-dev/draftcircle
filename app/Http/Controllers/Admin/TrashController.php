<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Book;
use App\Models\BookComment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use LogicException;

final readonly class TrashController
{
    public function index(): Response
    {
        $books = Book::onlyTrashed()
            ->latest('deleted_at')
            ->paginate(10, ['*'], 'books_page')
            ->withQueryString()
            ->through(fn (Book $book): array => [
                'id' => $book->id,
                'title' => $book->title,
                'author' => $book->author,
                'format' => $book->format->value,
                'deleted_at' => $book->deleted_at?->toISOString(),
            ]);

        $comments = BookComment::onlyTrashed()
            ->with('user:id,name')
            ->where(function (Builder $query): void {
                $query->whereNull('parent_id')->orWhereHas('parent');
            })
            ->latest('deleted_at')
            ->paginate(10, ['*'], 'comments_page')
            ->withQueryString();

        $bookTitles = Book::withTrashed()
            ->whereIn('id', $comments->getCollection()->pluck('book_id'))
            ->pluck('title', 'id');

        $comments->through(function (BookComment $comment) use ($bookTitles): array {
            $user = $comment->user;

            if ($user === null) {
                throw new LogicException("Comment {$comment->id} has no associated user.");
            }

            return [
                'id' => $comment->id,
                'body' => $comment->body,
                'user_name' => $user->name,
                'book_title' => $bookTitles[$comment->book_id] ?? trans('ui.common.not_assigned'),
                'deleted_at' => $comment->deleted_at?->toISOString(),
            ];
        });

        return Inertia::render('Admin/Trash/Index', [
            'books' => $books,
            'comments' => $comments,
        ]);
    }

    public function restoreBook(int $book): RedirectResponse
    {
        Book::onlyTrashed()->findOrFail($book)->restore();

        return back()->with('success', trans('ui.flash.book_restored'));
    }

    public function destroyBook(int $book): RedirectResponse
    {
        $book = Book::onlyTrashed()->findOrFail($book);

        if ($book->hasActiveLoans()) {
            return back()->with('error', trans('ui.errors.book_has_active_loans'));
        }

        if ($book->cover_path && ! str_starts_with($book->cover_path, 'https://')) {
            Storage::disk('public')->delete($book->cover_path);
        }

        $book->forceDelete();

        return back()->with('success', trans('ui.flash.book_permanently_deleted'));
    }

    public function restoreComment(int $comment): RedirectResponse
    {
        BookComment::onlyTrashed()->findOrFail($comment)->restore();

        return back()->with('success', trans('ui.flash.comment_restored'));
    }

    public function destroyComment(int $comment): RedirectResponse
    {
        BookComment::onlyTrashed()->findOrFail($comment)->forceDelete();

        return back()->with('success', trans('ui.flash.comment_permanently_deleted'));
    }
}
