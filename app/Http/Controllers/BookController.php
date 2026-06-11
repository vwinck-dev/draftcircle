<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\RateBookRequest;
use App\Http\Requests\ReadingProgressRequest;
use App\Models\Book;
use App\Models\BookComment;
use App\Models\User;
use App\Services\BookService;
use App\Services\ReadingService;
use App\Support\AuthenticatedUser;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use LogicException;

final readonly class BookController
{
    public function __construct(
        private BookService $books,
        private ReadingService $reading,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Books/Index', ['books' => $this->books->search(filters: $request->only(['search', 'category', 'format', 'available', 'sort']))]);
    }

    public function show(Book $book): Response
    {
        $book = $this->books->details(book: $book);

        $comments = $book->comments()
            ->with([
                'user:id,name,avatar',
                'viewerVote:id,book_comment_id,value',
                'threadReplies',
            ])
            ->withSum('votes as score', 'value')
            ->whereNull('parent_id')
            ->where('is_hidden', false)
            ->latest()
            ->get();

        $ratings = $book->ratings()->get();
        $total = $ratings->count();
        $dist = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        foreach ($ratings as $rating) {
            $score = (int) $rating->rating;
            if (isset($dist[$score])) {
                $dist[$score]++;
            }
        }
        $ratingDistribution = [
            1 => $dist[1],
            2 => $dist[2],
            3 => $dist[3],
            4 => $dist[4],
            5 => $dist[5],
            'total' => $total,
            'average' => round((float) ($book->ratings_avg_rating ?? 0), 1),
        ];

        $userRating = auth()->check()
            ? $book->ratings()->where('user_id', auth()->id())->value('rating')
            : null;

        return Inertia::render('Books/Show', [
            'book' => $book,
            'comments' => $this->commentTree($comments),
            'ratingDistribution' => $ratingDistribution,
            'userRating' => $userRating,
        ]);
    }

    public function reader(Request $request, Book $book): Response
    {
        abort_unless($book->format->isDigital(), 404);

        return Inertia::render('Books/Reader', $this->reading->data(user: AuthenticatedUser::from($request), book: $book));
    }

    public function rate(RateBookRequest $request, Book $book): RedirectResponse
    {
        $this->books->rate(user: AuthenticatedUser::from($request), book: $book, rating: $request->integer('rating'));

        return back()->with('success', trans('ui.flash.rating_saved'));
    }

    public function progress(ReadingProgressRequest $request, Book $book): RedirectResponse
    {
        $this->reading->saveProgress(user: AuthenticatedUser::from($request), book: $book, page: $request->integer('page'));

        return back();
    }

    /**
     * @param Collection<int, BookComment> $comments
     * @return list<array<string, mixed>>
     */
    private function commentTree(Collection $comments): array
    {
        return array_values($comments->map(function (BookComment $comment): array {
            $user = $comment->user;

            if (! $user instanceof User) {
                throw new LogicException("Comment {$comment->id} has no associated user.");
            }

            return [
                'id' => $comment->id,
                'book_id' => $comment->book_id,
                'user_id' => $comment->user_id,
                'parent_id' => $comment->parent_id,
                'body' => $comment->body,
                'created_at' => $comment->created_at,
                'score' => $comment->score ?? 0,
                'user_vote' => $comment->viewerVote?->value,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar_url' => $user->avatar_url,
                ],
                'replies' => $this->commentTree($comment->threadReplies),
            ];
        })->all());
    }
}
