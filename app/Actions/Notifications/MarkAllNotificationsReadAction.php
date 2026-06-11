<?php

declare(strict_types=1);

namespace App\Actions\Notifications;

use App\Models\User;

/**
 * Marks all unread notifications for a given user as read, updating the "read_at" timestamp to the current time.
 * The method returns the count of notifications that were marked as read.
 */
final readonly class MarkAllNotificationsReadAction
{
    /**
     * Marks all unread notifications for a given user as read, updating the "read_at" timestamp to the current time.
     * The method returns the count of notifications that were marked as read.
     */
    public function execute(User $user): int
    {
        return $user->unreadNotifications()->update(['read_at' => now()]);
    }
}
