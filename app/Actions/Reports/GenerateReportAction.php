<?php

declare(strict_types=1);

namespace App\Actions\Reports;

use App\DataTransferObjects\ReportData;
use App\Models\Book;
use App\Models\Fine;
use App\Models\Loan;
use App\Models\User;
use Carbon\CarbonImmutable;

/**
 * Generates a report containing key metrics such as total books, users, loans, and outstanding fines for a specified period.
 */
final readonly class GenerateReportAction
{
    /**
     * Generates a report containing key metrics such as total books, users, loans, and outstanding fines for a specified period.
     */
    public function execute(string $period): ReportData
    {
        return new ReportData(
            period: $period,
            metrics: [
                'books' => Book::query()->count(),
                'users' => User::query()->count(),
                'loans' => Loan::query()->count(),
                'outstanding_fines' => (float) Fine::query()->whereNull('paid_at')->sum('amount'),
            ],
            generatedAt: CarbonImmutable::now()->toIso8601String(),
        );
    }
}
