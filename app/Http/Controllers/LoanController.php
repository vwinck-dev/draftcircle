<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ReserveBookRequest;
use App\Models\Book;
use App\Models\Loan;
use App\Services\LoanService;
use App\Support\AuthenticatedUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final readonly class LoanController
{
    public function __construct(private LoanService $loans) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Loans/Index', ['loans' => $this->loans->listFor(user: AuthenticatedUser::from($request))]);
    }

    public function store(ReserveBookRequest $request, Book $book): RedirectResponse
    {
        $this->loans->reserveBook(user: AuthenticatedUser::from($request), book: $book);

        return back()->with('success', trans('ui.flash.book_reserved'));
    }

    public function update(Request $request, Loan $loan): RedirectResponse
    {
        $this->loans->returnBook(loan: $loan);

        return back()->with('success', trans('ui.flash.book_returned'));
    }
}
