<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;

/**
 * Handles finding or creating a user based on Google OAuth data.
 */
final readonly class FindOrCreateGoogleUserAction
{
    /**
     * Finds or creates a user based on Google OAuth data.
     */
    public function execute(SocialiteUser $googleUser): User
    {
        // Use updateOrCreate to find an existing user by email or create a new one with the provided Google data.
        return User::query()->updateOrCreate(
            ['email' => (string) $googleUser->getEmail()],
            [
                'name' => (string) $googleUser->getName(),
                'google_id' => (string) $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'email_verified_at' => now(),
                'locale' => app()->getLocale(),
                'password' => Str::password(),
            ],
        );
    }
}
