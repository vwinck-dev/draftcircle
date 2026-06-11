<?php

declare(strict_types=1);

namespace App\Actions\Notifications;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;

/**
 * Retrieves a list of notifications for a given user, ordered by the most recent, and limited to the latest 100 notifications.
 */
final readonly class ListNotificationsAction
{
    /**
     * Retrieves a list of notifications for a given user, ordered by the most recent, and limited to the latest 100 notifications.
     *
     * @return DatabaseNotificationCollection<int, DatabaseNotification>
     */
    public function execute(User $user): DatabaseNotificationCollection
    {
        return $user->notifications()->latest()->limit(100)->get();
    }
}
