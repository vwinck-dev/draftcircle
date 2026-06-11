<?php

declare(strict_types=1);

namespace App\Enums;

enum LoanStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Returned = 'returned';
    case Cancelled = 'cancelled';
    case Overdue = 'overdue';
}
