<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Loan;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class BookReservedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Loan $loan) {}
}
