<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\UserNotificationService;
use App\Support\AuthenticatedUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final readonly class NotificationController
{
    public function __construct(private UserNotificationService $notifications) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Notifications/Index', ['notifications' => $this->notifications->list(user: AuthenticatedUser::from($request))]);
    }

    public function update(Request $request): RedirectResponse
    {
        $count = $this->notifications->markAllRead(user: AuthenticatedUser::from($request));

        return back()->with(
            'success',
            trans_choice('ui.flash.notifications_marked_read', $count, ['count' => $count]),
        );
    }
}
