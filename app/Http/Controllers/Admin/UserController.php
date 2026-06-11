<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Services\UserService;
use Inertia\Inertia;
use Inertia\Response;

final readonly class UserController
{
    public function __construct(private UserService $users) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Users/Index', ['users' => $this->users->list()]);
    }

    public function show(User $user): Response
    {
        return Inertia::render('Admin/Users/Show', ['user' => $user]);
    }
}
