<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Retrieves a paginated list of users along with the count of their active loans, ordered by their names.
 */
final readonly class ListUsersAction
{
    /**
     * Retrieves a paginated list of users along with the count of their active loans, ordered by their names.
     *
     * @return LengthAwarePaginator<int, User>
     */
    public function execute(): LengthAwarePaginator
    {
        return User::query()->withCount('loans')->orderBy('name')->paginate(perPage: 20);
    }
}
