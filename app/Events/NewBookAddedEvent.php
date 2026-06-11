<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Book;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class NewBookAddedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Book $book) {}
}
