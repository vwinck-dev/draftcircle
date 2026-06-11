<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Notifications\SendOverdueNotificationAction;
use App\Actions\Notifications\SendReservationAvailableAction;
use App\Models\Loan;

final readonly class NotificationService
{
    public function __construct(
        private SendOverdueNotificationAction $sendOverdue,
        private SendReservationAvailableAction $sendReservationAvailable,
    ) {}

    public function sendOverdue(Loan $loan): void
    {
        $this->sendOverdue->execute(loan: $loan);
    }

    public function sendReservationAvailable(Loan $loan): void
    {
        $this->sendReservationAvailable->execute(loan: $loan);
    }
}
