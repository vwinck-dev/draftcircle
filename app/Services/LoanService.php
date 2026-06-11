<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Loans\CreateLoanAction;
use App\Actions\Loans\GenerateFineAction;
use App\Actions\Loans\ListUserLoansAction;
use App\Actions\Loans\ReturnLoanAction;
use App\Actions\Loans\ValidateBookAvailabilityAction;
use App\Enums\LoanType;
use App\Events\BookReservedEvent;
use App\Models\Book;
use App\Models\Fine;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final readonly class LoanService
{
    public function __construct(
        private ValidateBookAvailabilityAction $validateAvailability,
        private CreateLoanAction $createLoan,
        private ReturnLoanAction $returnLoan,
        private GenerateFineAction $generateFine,
        private ListUserLoansAction $listUserLoans,
    ) {}

    public function reserveBook(User $user, Book $book): Loan
    {
        $this->validateAvailability->execute(book: $book);
        $loan = $this->createLoan->execute(user: $user, book: $book, type: LoanType::Reservation);
        BookReservedEvent::dispatch($loan);

        return $loan;
    }

    public function returnBook(Loan $loan): Loan
    {
        return $this->returnLoan->execute(loan: $loan);
    }

    public function generateFine(Loan $loan, float $dailyAmount = 1.0): Fine
    {
        return $this->generateFine->execute(loan: $loan, dailyAmount: $dailyAmount);
    }

    /** @return Collection<int, Loan> */
    public function listFor(User $user, bool $all = false): Collection
    {
        return $this->listUserLoans->execute(user: $user, all: $all);
    }
}
