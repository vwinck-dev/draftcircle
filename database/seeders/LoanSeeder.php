<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\LoanStatus;
use App\Enums\LoanType;
use App\Models\Book;
use App\Models\Fine;
use App\Models\Loan;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

final class LoanSeeder extends Seeder
{
    public function run(): void
    {
        if (Loan::query()->exists()) {
            return;
        }

        /** @var list<User> $users */
        $users = User::query()->get()->values()->all();
        $books = Book::query()->where(column: 'total_copies', operator: '>', value: 0)->get();

        if ($users === [] || $books->isEmpty()) {
            return;
        }

        for ($i = 0; $i < 400; $i++) {
            $status = LoanStatus::cases()[array_rand(array: LoanStatus::cases())];
            $isReservation = $status === LoanStatus::Pending;
            $reservedAt = CarbonImmutable::now()->subDays(random_int(min: 1, max: 120));

            $dueAt = match ($status) {
                LoanStatus::Overdue => CarbonImmutable::now()->subDays(random_int(min: 1, max: 30)),
                LoanStatus::Returned => $reservedAt->addDays(random_int(min: 7, max: 21)),
                default => CarbonImmutable::now()->addDays(random_int(min: 1, max: 21)),
            };

            $returnedAt = $status === LoanStatus::Returned
                ? $reservedAt->addDays(random_int(min: 3, max: 18))
                : null;

            $loan = Loan::query()->create([
                'user_id' => $users[$i % count($users)]->id,
                'book_id' => $books->random()->id,
                'type' => $isReservation ? LoanType::Reservation : LoanType::Loan,
                'status' => $status,
                'reserved_at' => $reservedAt,
                'due_at' => $dueAt,
                'returned_at' => $returnedAt,
            ]);

            if ($status === LoanStatus::Overdue && random_int(min: 0, max: 1) === 1) {
                $overdueDays = abs(num: (int) CarbonImmutable::now()->diffInDays(date: $dueAt));
                Fine::query()->create([
                    'loan_id' => $loan->id,
                    'user_id' => $loan->user_id,
                    'amount' => number_format(
                        num: max(1, $overdueDays) * 2.50,
                        decimals: 2,
                        decimal_separator: '.',
                        thousands_separator: '',
                    ),
                    'paid_at' => random_int(min: 0, max: 1) === 1
                        ? CarbonImmutable::now()->subDays(random_int(min: 1, max: 10))
                        : null,
                ]);
            }
        }
    }
}
