<?php

declare(strict_types=1);

namespace App\Actions\Notifications;

use App\Models\Loan;
use App\Notifications\OverdueLoanNotification;

/**
 * Sends overdue notifications for loans that are past their due date,
 * updating the loan status to "Overdue" and returning the count of processed loans.
 */
final readonly class SendOverdueNotificationAction
{
    /**
     * Sends an overdue notification for a given loan, notifying the user associated with the loan about the overdue status.
     */
    public function execute(Loan $loan): void
    {
        $loan->user()->firstOrFail()->notify(new OverdueLoanNotification(loan: $loan));
    }
}
