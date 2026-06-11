<?php

declare(strict_types=1);

namespace App\Actions\Loans;

use App\Models\Book;
use Illuminate\Validation\ValidationException;

/**
 * Validates the availability of a book for loaning, ensuring that the book is active, has a physical format, and has available copies.
 * If the book is not available, a validation exception is thrown with an appropriate error message.
 */
final readonly class ValidateBookAvailabilityAction
{
    /**
     * Validates the availability of a book for loaning, ensuring that the book is active, has a physical format, and has available copies.
     * If the book is not available, a validation exception is thrown with an appropriate error message.
     *
     * @throws ValidationException
     */
    public function execute(Book $book): void
    {
        if (! $book->is_active || ! $book->format->isPhysical() || $book->available_copies < 1) {
            throw ValidationException::withMessages([
                'book' => trans('ui.errors.book_unavailable'),
            ]);
        }
    }
}
