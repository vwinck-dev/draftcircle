<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\NewBookAddedEvent;
use App\Models\User;
use App\Notifications\NewBookNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

final readonly class NotifyUsersAboutNewBookListener implements ShouldQueue
{
    public function handle(NewBookAddedEvent $event): void
    {
        User::query()->chunkById(
            count: 100,
            callback: fn ($users) => Notification::send($users, new NewBookNotification(book: $event->book)),
        );
    }
}
