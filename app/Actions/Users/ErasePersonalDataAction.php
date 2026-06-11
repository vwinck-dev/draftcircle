<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Models\ReadingProgress;
use App\Models\User;

/**
 * Handles the erasure of personal data for a user, including their ratings, reading progress, and notifications.
 */
final class ErasePersonalDataAction
{
    /**
     * Erases personal data for a user, including their ratings, reading progress, and notifications.
     * This method deletes all records associated with the user in the "ratings", "reading_progress", and
     * "notifications" tables, effectively removing any trace of the user's interactions with the system
     * while retaining the user's account for potential future use.
     */
    public function handle(User $user): void
    {
        $user->ratings()->delete();

        ReadingProgress::query()->where('user_id', $user->id)->delete();

        $user->notifications()->delete();
    }
}
