<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\BookComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final readonly class BookCommentController
{
    public function index(Request $request): Response
    {
        $status = $request->string('status')->toString();

        $comments = BookComment::query()
            ->with(['book:id,title', 'user:id,name,avatar', 'parent.user:id,name'])
            ->whereHas('book')
            ->when($status === 'visible', fn ($query) => $query->where('is_hidden', false))
            ->when($status === 'hidden', fn ($query) => $query->where('is_hidden', true))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Comments/Index', [
            'comments' => $comments,
            'filters' => ['status' => in_array($status, ['visible', 'hidden'], true) ? $status : 'all'],
        ]);
    }

    public function update(BookComment $comment): RedirectResponse
    {
        $comment->update(['is_hidden' => ! $comment->is_hidden]);

        return back()->with('success', trans(
            $comment->is_hidden ? 'ui.flash.comment_hidden' : 'ui.flash.comment_restored'
        ));
    }

    public function destroy(BookComment $comment): RedirectResponse
    {
        $comment->delete();

        return back()->with('success', trans('ui.flash.comment_deleted'));
    }
}
