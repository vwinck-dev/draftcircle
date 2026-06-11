<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BookFormat;
use App\Enums\LoanStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $title
 * @property string $author
 * @property string|null $cover_path
 * @property string|null $isbn
 * @property BookFormat $format
 * @property int $total_copies
 * @property int $available_copies
 * @property bool $is_active
 * @property-read string $cover_url
 * @property-read float|null $ratings_avg_rating
 * @property-read int|null $ratings_count
 */
final class Book extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'author',
        'publisher',
        'year',
        'language',
        'isbn',
        'description',
        'cover_path',
        'format',
        'total_copies',
        'available_copies',
        'loan_count',
        'is_active',
    ];

    protected $appends = ['cover_url'];

    /** @return Attribute<string, never> */
    protected function coverUrl(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                if ($this->cover_path) {
                    if (str_starts_with(haystack: $this->cover_path, needle: 'https://')) {
                        return $this->cover_path;
                    }

                    return asset(path: "storage/{$this->cover_path}");
                }

                return asset(path: 'images/book-placeholder.svg');
            }
        );
    }

    /** @return BelongsToMany<Category, $this> */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'book_categories');
    }

    /** @return HasMany<BookRating, $this> */
    public function ratings(): HasMany
    {
        return $this->hasMany(BookRating::class);
    }

    /** @return HasMany<Loan, $this> */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function hasActiveLoans(): bool
    {
        return $this->loans()
            ->whereIn('status', [LoanStatus::Pending, LoanStatus::Active, LoanStatus::Overdue])
            ->exists();
    }

    /** @return HasMany<BookPage, $this> */
    public function pages(): HasMany
    {
        return $this->hasMany(BookPage::class)->orderBy('page_number');
    }

    /** @return HasMany<BookComment, $this> */
    public function comments(): HasMany
    {
        return $this->hasMany(BookComment::class);
    }

    /** @param Builder<Book> $query */
    public function scopeAvailable(Builder $query): void
    {
        $query->where('available_copies', '>', 0);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'format' => BookFormat::class,
            'is_active' => 'boolean',
            'year' => 'integer',
            'total_copies' => 'integer',
            'available_copies' => 'integer',
            'loan_count' => 'integer',
        ];
    }
}
