<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;

final readonly class LoanPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user, Book $book): bool
    {
        return $book->is_active && $book->format->isPhysical() && $book->available_copies > 0;
    }

    public function view(User $user, Loan $loan): bool
    {
        return $user->isStaff() || $loan->user_id === $user->id;
    }

    public function update(User $user, Loan $loan): bool
    {
        return $user->isStaff() || $loan->user_id === $user->id;
    }
}
