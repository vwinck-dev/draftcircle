<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\LoanStatus;
use App\Models\Loan;
use App\Services\LoanService;
use App\Support\AuthenticatedUser;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use LogicException;

final readonly class LoanController
{
    public function __construct(private LoanService $loans) {}

    public function index(Request $request): Response
    {
        $overdueLoans = Loan::query()
            ->with(['book', 'user', 'fine'])
            ->where('status', LoanStatus::Overdue)
            ->latest('due_at')
            ->get()
            ->map(function (Loan $loan): array {
                $book = $loan->book;

                if ($book === null) {
                    throw new LogicException("Loan {$loan->id} has no associated book.");
                }

                return [
                    'id' => $loan->id,
                    'user' => $loan->user?->only(['id', 'name', 'email']),
                    'book_title' => $book->title,
                    'due_at' => $loan->due_at,
                    'days_overdue' => $loan->due_at ? (int) now()->diffInDays($loan->due_at) : 0,
                    'fine_amount' => $loan->fine?->amount,
                ];
            });

        return Inertia::render('Admin/Loans/Index', [
            'loans' => $this->loans->listFor(user: AuthenticatedUser::from($request), all: true),
            'overdueLoans' => $overdueLoans,
        ]);
    }
}
