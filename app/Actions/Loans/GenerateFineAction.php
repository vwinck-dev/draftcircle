<?php

declare(strict_types=1);

namespace App\Actions\Loans;

use App\Events\FineGeneratedEvent;
use App\Models\Fine;
use App\Models\Loan;
use Carbon\CarbonImmutable;

/**
 * Handles the generation of fines for overdue loans, calculating the fine amount based on the number of days overdue and a specified daily amount, while also dispatching an event when a fine is generated.
 */
final readonly class GenerateFineAction
{
    /**
     * Generates a fine for an overdue loan, calculating the fine amount based on the number of days overdue and a specified daily amount.
     */
    public function execute(Loan $loan, float $dailyAmount = 1.0): Fine
    {
        $days = max(1, (int) ceil($loan->due_at?->diffInDays(CarbonImmutable::now()) ?? 1));
        $fine = Fine::query()->updateOrCreate(
            ['loan_id' => $loan->id],
            ['user_id' => $loan->user_id, 'amount' => $days * $dailyAmount],
        );

        FineGeneratedEvent::dispatch($fine);

        return $fine;
    }
}
