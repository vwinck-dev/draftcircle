<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Book;
use App\Models\User;

final readonly class BookPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Book $book): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, Book $book): bool
    {
        return $user->isStaff();
    }

    public function delete(User $user, Book $book): bool
    {
        return $user->isStaff() && ! $book->hasActiveLoans();
    }
}
