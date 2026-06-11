<?php

declare(strict_types=1);

namespace App\Actions\Loans;

use App\Enums\LoanStatus;
use App\Enums\LoanType;
use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Carbon\CarbonImmutable;

/**
 * Handles the creation of a new loan or reservation for a book, ensuring that the available copies are updated accordingly and that the loan status is set based on the type (loan or reservation).
 */
final readonly class CreateLoanAction
{
    /**
     * Creates a new loan or reservation for a book, ensuring that the available copies are updated
     * accordingly and that the loan status is set based on the type (loan or reservation).
     */
    public function execute(User $user, Book $book, LoanType $type): Loan
    {
        $loan = Loan::query()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'type' => $type,
            'status' => $type === LoanType::Reservation ? LoanStatus::Pending : LoanStatus::Active,
            'reserved_at' => CarbonImmutable::now(),
            'due_at' => $type === LoanType::Loan ? CarbonImmutable::now()->addDays(14) : null,
        ]);

        $book->decrement('available_copies');
        $book->increment('loan_count');

        return $loan->load(['book', 'user']);
    }
}
