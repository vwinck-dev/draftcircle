<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\BookReservedEvent;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

final readonly class SendReservationNotificationListener implements ShouldQueue
{
    public function __construct(private NotificationService $notifications) {}

    public function handle(BookReservedEvent $event): void
    {
        $this->notifications->sendReservationAvailable(loan: $event->loan);
    }
}
