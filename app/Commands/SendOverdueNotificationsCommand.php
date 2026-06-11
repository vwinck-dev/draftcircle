<?php

declare(strict_types=1);

namespace App\Commands;

use App\Services\MaintenanceService;
use Illuminate\Console\Command;

/**
 * Command to send notifications about overdue books. This command is intended to be run periodically (e.g., via a scheduled task) to remind users to return their borrowed items, helping to maintain accurate records of due dates and encourage timely returns.
 */
final class SendOverdueNotificationsCommand extends Command
{
    protected $signature = 'library:send-overdue-notifications';

    public function __construct()
    {
        parent::__construct();
        $this->setDescription(trans('ui.console.notifications_description'));
    }

    /**
     * Executes the command to send notifications about overdue books. It calls the "sendOverdueNotifications" method of the MaintenanceService,
     * which performs the actual sending of notifications and returns the count of sent notifications.
     * The result is then displayed in the console output.
     */
    public function handle(MaintenanceService $maintenance): int
    {
        $this->info(trans('ui.console.notifications_result', ['count' => $maintenance->sendOverdueNotifications()]));

        return self::SUCCESS;
    }
}
