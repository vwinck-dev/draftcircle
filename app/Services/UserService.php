<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Users\ListUsersAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class UserService
{
    public function __construct(private ListUsersAction $listUsers) {}

    /** @return LengthAwarePaginator<int, \App\Models\User> */
    public function list(): LengthAwarePaginator
    {
        return $this->listUsers->execute();
    }
}
