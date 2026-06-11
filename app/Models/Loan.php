<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LoanStatus;
use App\Enums\LoanType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $user_id
 * @property int $book_id
 * @property LoanType $type
 * @property LoanStatus $status
 * @property \Carbon\CarbonImmutable|null $reserved_at
 * @property \Carbon\CarbonImmutable|null $due_at
 * @property \Carbon\CarbonImmutable|null $returned_at
 * @property-read User|null $user
 * @property-read Book|null $book
 */
final class Loan extends Model
{
    protected $fillable = [
        'user_id',
        'book_id',
        'type',
        'status',
        'reserved_at',
        'due_at',
        'returned_at',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Book, $this> */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class)->withTrashed();
    }

    /** @return HasOne<Fine, $this> */
    public function fine(): HasOne
    {
        return $this->hasOne(Fine::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'type' => LoanType::class,
            'status' => LoanStatus::class,
            'reserved_at' => 'datetime',
            'due_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }
}
