<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Loans\CleanExpiredReservationsAction;
use App\Actions\Loans\ProcessPendingFinesAction;
use App\Actions\Loans\SendOverdueNotificationsAction;

final readonly class MaintenanceService
{
    public function __construct(
        private SendOverdueNotificationsAction $sendOverdueNotifications,
        private CleanExpiredReservationsAction $cleanExpiredReservations,
        private ProcessPendingFinesAction $processPendingFines,
        private BookService $books,
        private SettingService $settings,
    ) {}

    public function sendOverdueNotifications(): int
    {
        return $this->sendOverdueNotifications->execute();
    }

    public function cleanExpiredReservations(): int
    {
        return $this->cleanExpiredReservations->execute();
    }

    public function processPendingFines(float $dailyAmount = 1.0): int
    {
        return $this->processPendingFines->execute(dailyAmount: $dailyAmount);
    }

    public function warmCache(): void
    {
        $this->books->homeCatalog();
        $this->settings->all();
    }
}
