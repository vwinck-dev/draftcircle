<?php

declare(strict_types=1);

namespace App\Commands;

use App\Services\MaintenanceService;
use Illuminate\Console\Command;

/**
 * Command to process pending fines for users. This command is intended to be run periodically (e.g., via a scheduled task) to ensure that any pending fines are calculated and applied to user accounts, helping to maintain accurate records of fines and encourage timely returns of borrowed items.
 */
final class ProcessPendingFinesCommand extends Command
{
    protected $signature = 'library:process-pending-fines';

    public function __construct()
    {
        parent::__construct();
        $this->setDescription(trans('ui.console.fines_description'));
    }

    /**
     * Executes the command to process pending fines. It calls the "processPendingFines" method of the MaintenanceService,
     * which performs the actual processing and returns the count of processed fines.
     * The result is then displayed in the console output.
     */
    public function handle(MaintenanceService $maintenance): int
    {
        $this->info(trans('ui.console.fines_result', ['count' => $maintenance->processPendingFines()]));

        return self::SUCCESS;
    }
}
