<?php

declare(strict_types=1);

namespace App\Commands;

use App\Services\MaintenanceService;
use Illuminate\Console\Command;

/**
 * Command to warm the library cache. This command is intended to be run periodically (e.g., via a scheduled task) to ensure that the library's cache is preloaded with frequently accessed data, improving performance and reducing response times for users.
 */
final class WarmLibraryCacheCommand extends Command
{
    protected $signature = 'library:warm-cache';

    public function __construct()
    {
        parent::__construct();
        $this->setDescription(trans('ui.console.cache_description'));
    }

    /**
     * Executes the command to warm the library cache. It calls the "warmCache" method of the MaintenanceService,
     * which performs the actual warming of the cache. After the cache is warmed, a success message is displayed in the console output.
     */
    public function handle(MaintenanceService $maintenance): int
    {
        $maintenance->warmCache();
        $this->info(trans('ui.console.cache_result'));

        return self::SUCCESS;
    }
}
