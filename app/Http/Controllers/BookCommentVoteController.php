<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BookComment;
use App\Support\AuthenticatedUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class BookCommentVoteController extends Controller
{
    public function update(Request $request, BookComment $comment): RedirectResponse
    {
        abort_if($comment->is_hidden, 404);

        $validated = $request->validate([
            'value' => ['required', 'integer', Rule::in([-1, 1])],
        ]);

        $vote = $comment->votes()
            ->where('user_id', AuthenticatedUser::from($request)->id)
            ->first();
        $value = (int) $validated['value'];

        if ($vote?->value === $value) {
            $vote->delete();
        } else {
            $comment->votes()->updateOrCreate(
                ['user_id' => AuthenticatedUser::from($request)->id],
                ['value' => $value],
            );
        }

        return back();
    }
}
