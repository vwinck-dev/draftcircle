<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Services\AdminService;
use Inertia\Inertia;
use Inertia\Response;

final readonly class DashboardController
{
    public function __construct(private AdminService $admin) {}

    public function __invoke(): Response
    {
        return Inertia::render('Admin/Dashboard', ['summary' => $this->admin->summary()]);
    }
}
