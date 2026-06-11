<?php

declare(strict_types=1);

namespace App\Actions\Loans;

use App\Enums\LoanStatus;
use App\Models\Loan;

/**
 * Handles the processing of pending fines for overdue loans, generating fines based on a specified daily amount and returning the count of processed loans.
 */
final readonly class ProcessPendingFinesAction
{
    public function __construct(private GenerateFineAction $generateFine) {}

    /**
     * Processes pending fines for overdue loans, generating fines based on a specified daily amount and returning the count of processed loans.
     */
    public function execute(float $dailyAmount): int
    {
        $processed = 0;

        Loan::query()
            ->where('status', LoanStatus::Overdue)
            ->each(function (Loan $loan) use (&$processed, $dailyAmount): void {
                $this->generateFine->execute(loan: $loan, dailyAmount: $dailyAmount);
                $processed++;
            });

        return $processed;
    }
}
