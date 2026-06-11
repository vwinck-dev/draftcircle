<?php

declare(strict_types=1);

namespace App\Actions\Loans;

use App\Models\Loan;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Retrieves a list of loans for a specific user, with an option to include all loans or only the active ones, while also loading related book, user, and fine information.
 */
final readonly class ListUserLoansAction
{
    /**
     * Retrieves a list of loans for a specific user, with an option to include all loans or only the active ones, while also loading related book, user, and fine information.
     *
     * @return Collection<int, Loan>
     */
    public function execute(User $user, bool $all = false): Collection
    {
        return Loan::query()
            ->with(['book.categories', 'user', 'fine'])

            // If $all is false, filter the loans to include only those that belong to the specified user.
            ->when(! $all, fn ($query) => $query->where('user_id', $user->id))
            ->latest()
            ->get();
    }
}
