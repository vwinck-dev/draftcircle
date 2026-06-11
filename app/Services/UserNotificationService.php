<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Notifications\ListNotificationsAction;
use App\Actions\Notifications\MarkAllNotificationsReadAction;
use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;

final readonly class UserNotificationService
{
    public function __construct(
        private ListNotificationsAction $listNotifications,
        private MarkAllNotificationsReadAction $markAllRead,
    ) {}

    /** @return DatabaseNotificationCollection<int, DatabaseNotification> */
    public function list(User $user): DatabaseNotificationCollection
    {
        return $this->listNotifications->execute(user: $user);
    }

    public function markAllRead(User $user): int
    {
        return $this->markAllRead->execute(user: $user);
    }
}
