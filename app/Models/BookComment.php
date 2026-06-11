<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * @property int|null $score
 */
final class BookComment extends Model
{
    use SoftDeletes;

    protected $fillable = ['book_id', 'user_id', 'parent_id', 'body', 'is_hidden'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_hidden' => 'boolean',
            'score' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        self::deleting(function (BookComment $comment): void {
            if (! $comment->isForceDeleting()) {
                $comment->replies()->each(function (BookComment $reply): void {
                    $reply->delete();
                });
            }
        });

        self::restoring(function (BookComment $comment): void {
            $comment->replies()->onlyTrashed()->each(function (BookComment $reply): void {
                $reply->restore();
            });
        });
    }

    /** @return BelongsTo<Book, $this> */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<BookComment, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(BookComment::class, 'parent_id');
    }

    /** @return HasMany<BookComment, $this> */
    public function replies(): HasMany
    {
        return $this->hasMany(BookComment::class, 'parent_id');
    }

    /** @return HasMany<BookComment, $this> */
    public function threadReplies(): HasMany
    {
        return $this->hasMany(BookComment::class, 'parent_id')
            ->where('is_hidden', false)
            ->oldest()
            ->with([
                'user:id,name,avatar',
                'viewerVote:id,book_comment_id,value',
                'threadReplies',
            ])
            ->withSum('votes as score', 'value');
    }

    /** @return HasMany<BookCommentVote, $this> */
    public function votes(): HasMany
    {
        return $this->hasMany(BookCommentVote::class);
    }

    /** @return HasOne<BookCommentVote, $this> */
    public function viewerVote(): HasOne
    {
        return $this->hasOne(BookCommentVote::class)->where('user_id', Auth::id() ?? 0);
    }
}
