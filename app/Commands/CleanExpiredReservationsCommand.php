<?php

declare(strict_types=1);

namespace App\Commands;

use App\Services\MaintenanceService;
use Illuminate\Console\Command;

/**
 * Command to clean up expired reservations from the system. This command is intended to be run periodically (e.g., via a scheduled task) to ensure that expired reservations are removed and do not clutter the database or affect the availability of items.
 */
final class CleanExpiredReservationsCommand extends Command
{
    protected $signature = 'library:clean-expired-reservations';

    public function __construct()
    {
        parent::__construct();
        $this->setDescription(trans('ui.console.clean_description'));
    }

    /**
     * Executes the command to clean expired reservations. It calls the "cleanExpiredReservations" method of the MaintenanceService,
     * which performs the actual cleanup and returns the count of cleaned reservations.
     * The result is then displayed in the console output.
     */
    public function handle(MaintenanceService $maintenance): int
    {
        $this->info(trans('ui.console.clean_result', ['count' => $maintenance->cleanExpiredReservations()]));

        return self::SUCCESS;
    }
}
