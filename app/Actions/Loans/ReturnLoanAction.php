<?php

declare(strict_types=1);

namespace App\Actions\Loans;

use App\Enums\LoanStatus;
use App\Events\BookReturnedEvent;
use App\Models\Loan;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

/**
 * Handles the return of a loan, updating the loan status, incrementing the available copies of the book, and dispatching an event to notify that a book has been returned.
 */
final readonly class ReturnLoanAction
{
    /**
     * Returns a loan, updating the loan status, incrementing the available copies of the book, and dispatching an event to notify that a book has been returned.
     *
     * @throws ValidationException
     */
    public function execute(Loan $loan): Loan
    {
        if (! in_array($loan->status, [LoanStatus::Active, LoanStatus::Overdue], true)) {
            throw ValidationException::withMessages(['loan' => trans('ui.errors.active_loan_required')]);
        }

        $loan->update(['status' => LoanStatus::Returned, 'returned_at' => CarbonImmutable::now()]);

        $loan->book()->increment('available_copies');

        BookReturnedEvent::dispatch($loan);

        return $loan->fresh(['book', 'user', 'fine']) ?? $loan;
    }
}
