<?php

declare(strict_types=1);

namespace App\Actions\Loans;

use App\Enums\LoanStatus;
use App\Models\Loan;
use App\Services\NotificationService;
use Carbon\CarbonImmutable;

/**
 * Sends overdue notifications for loans that are past their due date,
 * updating the loan status to "Overdue" and returning the count of processed loans.
 */
final readonly class SendOverdueNotificationsAction
{
    public function __construct(private NotificationService $notifications) {}

    /**
     * Sends overdue notifications for loans that are past their due date,
     * updating the loan status to "Overdue" and returning the count of processed loans.
     */
    public function execute(): int
    {
        $processed = 0;

        Loan::query()
            ->with(['book', 'user'])
            ->whereIn('status', [LoanStatus::Active, LoanStatus::Overdue])
            ->where('due_at', '<', CarbonImmutable::now())
            ->each(function (Loan $loan) use (&$processed): void {
                $loan->update(['status' => LoanStatus::Overdue]);
                $this->notifications->sendOverdue(loan: $loan);
                $processed++;
            });

        return $processed;
    }
}
