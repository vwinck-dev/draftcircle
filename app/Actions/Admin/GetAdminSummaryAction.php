<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Enums\LoanStatus;
use App\Models\Book;
use App\Models\Fine;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Gathers key metrics and recent activity for the admin dashboard.
 */
final readonly class GetAdminSummaryAction
{
    /**
     * Gathers key metrics and recent activity for the admin dashboard.
     *
     * @return array{
     *   books: int<0, max>,
     *   users: int<0, max>,
     *   activeLoans: int<0, max>,
     *   overdueLoans: int<0, max>,
     *   pendingReservations: int<0, max>,
     *   returnedThisMonth: int<0, max>,
     *   newUsersThisMonth: int<0, max>,
     *   availableCopies: int,
     *   outstandingFines: float,
     *   popularBooks: Collection<int, Book>,
     *   recentLoans: \Illuminate\Support\Collection<int, array{id: int, book: string|null, user: string|null, status: value-of<LoanStatus>, reserved_at: \Carbon\CarbonImmutable|null}>,
     * }
     */
    public function execute(): array
    {
        return [
            'books' => Book::query()->count(),

            'users' => User::query()->count(),

            'activeLoans' => Loan::query()->whereIn('status', [LoanStatus::Active, LoanStatus::Overdue])->count(),

            'overdueLoans' => Loan::query()->where('status', LoanStatus::Overdue)->count(),

            'pendingReservations' => Loan::query()->where('status', LoanStatus::Pending)->count(),

            // Calculate loans returned in the current month for display on the dashboard.
            'returnedThisMonth' => Loan::query()
                ->where('status', LoanStatus::Returned)
                ->whereMonth('returned_at', now()->month)
                ->whereYear('returned_at', now()->year)
                ->count(),

            // Calculate new users registered in the current month for display on the dashboard.
            'newUsersThisMonth' => User::query()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),

            // Calculate total available copies across all books for display on the dashboard.
            'availableCopies' => (int) Book::query()->sum('available_copies'),

            // Calculate total outstanding fines for display on the dashboard.
            'outstandingFines' => (float) Fine::query()->whereNull('paid_at')->sum('amount'),

            // Get the 5 most popular books based on loan count for display on the dashboard.
            'popularBooks' => Book::query()
                ->select(['id', 'title', 'author', 'loan_count'])
                ->orderByDesc('loan_count')
                ->limit(5)
                ->get(),

            // Get the 6 most recent loans with related book and user information for display on the dashboard.
            'recentLoans' => Loan::query()
                ->with(['book:id,title', 'user:id,name'])
                ->latest('reserved_at')
                ->limit(6)
                ->get()
                ->map(fn (Loan $loan): array => [
                    'id' => $loan->id,
                    'book' => $loan->book?->title,
                    'user' => $loan->user?->name,
                    'status' => $loan->status->value,
                    'reserved_at' => $loan->reserved_at,
                ]),
        ];
    }
}
