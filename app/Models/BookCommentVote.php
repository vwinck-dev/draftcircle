<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class BookCommentVote extends Model
{
    protected $fillable = ['book_comment_id', 'user_id', 'value'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['value' => 'integer'];
    }

    /** @return BelongsTo<BookComment, $this> */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(BookComment::class, 'book_comment_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
