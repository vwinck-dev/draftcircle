<?php

declare(strict_types=1);

namespace App\Actions\Loans;

use App\Enums\LoanStatus;
use App\Enums\LoanType;
use App\Models\Loan;
use Carbon\CarbonImmutable;

/**
 * Handles the cleanup of expired reservations by marking them as cancelled and updating the available copies of the associated books.
 */
final readonly class CleanExpiredReservationsAction
{
    /**
     * Cleans up expired reservations by marking them as cancelled and updating the available copies of the associated books.
     * Returns the number of processed reservations.
     */
    public function execute(): int
    {
        $processed = 0;

        // Find all pending reservations that were made more than 48 hours ago and mark them as cancelled,
        // while also incrementing the available copies of the associated books.
        Loan::query()
            ->with('book')
            ->where('type', LoanType::Reservation)
            ->where('status', LoanStatus::Pending)
            ->where('reserved_at', '<', CarbonImmutable::now()->subHours(48))
            ->each(function (Loan $loan) use (&$processed): void {
                $loan->update(['status' => LoanStatus::Cancelled]);
                $loan->book()->increment('available_copies');
                $processed++;
            });

        return $processed;
    }
}
