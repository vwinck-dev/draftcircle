<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Fine;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class FineGeneratedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Fine $fine) {}
}
