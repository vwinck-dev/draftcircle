<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Reports\GenerateReportAction;
use App\DataTransferObjects\ReportData;
use Illuminate\Support\Facades\Storage;

final readonly class ReportService
{
    public function __construct(private GenerateReportAction $generateReport) {}

    public function generate(string $period): ReportData
    {
        $report = $this->generateReport->execute(period: $period);
        Storage::disk('local')->put(
            path: "reports/library-{$report->period}-{$report->generatedAt}.json",
            contents: (string) json_encode($report, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR),
        );

        return $report;
    }
}
