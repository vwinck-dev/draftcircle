<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\FineGeneratedEvent;
use App\Notifications\FineGeneratedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

final readonly class SendFineNotificationListener implements ShouldQueue
{
    public function handle(FineGeneratedEvent $event): void
    {
        $event->fine->user()->firstOrFail()->notify(new FineGeneratedNotification(fine: $event->fine));
    }
}
