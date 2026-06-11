<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LoanStatus;
use App\Enums\UserRole;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $registration_number
 * @property UserRole $role
 * @property bool $email_notifications
 * @property string $locale
 * @property string|null $avatar
 * @property-read string|null $avatar_url
 * @property string|null $google_id
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Loan> $loans
 * @property-read int|null $loans_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, BookRating> $ratings
 * @property-read int|null $ratings_count
 */
class User extends Authenticatable implements HasLocalePreference, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $appends = ['avatar_url'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'registration_number',
        'avatar',
        'google_id',
        'role',
        'email_notifications',
        'theme',
        'locale',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'email_notifications' => 'boolean',
        ];
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

    /** @return HasMany<BookRating, $this> */
    public function ratings(): HasMany
    {
        return $this->hasMany(BookRating::class);
    }

    /** @return HasMany<BookComment, $this> */
    public function comments(): HasMany
    {
        return $this->hasMany(BookComment::class);
    }

    public function isStaff(): bool
    {
        return $this->role->isStaff();
    }

    public function preferredLocale(): string
    {
        return $this->locale;
    }

    /** @return Attribute<string|null, never> */
    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                if (! $this->avatar) {
                    return null;
                }

                if (str_starts_with($this->avatar, 'http://') || str_starts_with($this->avatar, 'https://')) {
                    return $this->avatar;
                }

                return '/storage/'.ltrim($this->avatar, '/');
            },
        );
    }
}
