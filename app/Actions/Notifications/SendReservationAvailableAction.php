<?php

declare(strict_types=1);

namespace App\Actions\Notifications;

use App\Models\Loan;
use App\Notifications\ReservationAvailableNotification;

/**
 * Sends a reservation available notification to the user associated with a given loan,
 * informing them that the reserved item is now available.
 */
final readonly class SendReservationAvailableAction
{
    /**
     * Sends a reservation available notification for a given loan, notifying
     * the user associated with the loan that the reserved item is now available.
     */
    public function execute(Loan $loan): void
    {
        $loan->user()->firstOrFail()->notify(new ReservationAvailableNotification(loan: $loan));
    }
}
