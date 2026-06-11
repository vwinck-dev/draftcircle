<?php

declare(strict_types=1);

namespace App\Enums;

enum LoanType: string
{
    case Reservation = 'reservation';
    case Loan = 'loan';
}
