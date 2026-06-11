<?php

declare(strict_types=1);

namespace App\Commands;

use App\Services\ReportService;
use Illuminate\Console\Command;

/**
 * Command to generate a library report for a specified period (e.g., weekly, monthly). This command can be used to create reports that summarize various aspects of the library's operations, such as loan statistics, user activity, and inventory status. The generated report can be output to the console or saved to a file for further analysis.
 */
final class GenerateLibraryReportCommand extends Command
{
    protected $signature = 'library:generate-report {--period=weekly}';

    public function __construct()
    {
        parent::__construct();
        $this->setDescription(trans('ui.console.report_description'));
    }

    /**
     * Executes the command to generate a library report for a specified period. It calls the "generate" method of the ReportService, passing the period option from the command line. The generated report is then displayed in the console output, showing the period for which the report was generated.
     */
    public function handle(ReportService $reports): int
    {
        $report = $reports->generate(period: (string) $this->option('period'));
        $this->info(trans('ui.console.report_result', ['period' => $report->period]));

        return self::SUCCESS;
    }
}
