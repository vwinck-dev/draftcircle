<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\NotificationType;
use App\Models\Book;
use App\Models\Fine;
use App\Models\Loan;
use App\Models\User;
use App\Notifications\FineGeneratedNotification;
use App\Notifications\NewBookNotification;
use App\Notifications\OverdueLoanNotification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class ActivitySeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('notifications')->exists()) {
            return;
        }

        $user = User::query()->where('role', 'student')->first();
        $book = Book::query()->first();
        $loan = Loan::query()->with('book')->where('status', 'overdue')->first();
        $fine = Fine::query()->first();

        if (! $user instanceof User || ! $book instanceof Book) {
            return;
        }

        $notifications = [
            [
                'type' => NewBookNotification::class,
                'data' => [
                    'type' => NotificationType::NewBook->value,
                    'book_id' => $book->id,
                    'message_key' => 'mail.new_book_message',
                    'message_params' => ['title' => $book->title],
                ],
            ],
        ];

        if ($loan instanceof Loan) {
            $loanBook = $loan->book;

            if ($loanBook instanceof Book) {
                $notifications[] = [
                    'type' => OverdueLoanNotification::class,
                    'data' => [
                        'type' => NotificationType::OverdueReminder->value,
                        'loan_id' => $loan->id,
                        'message_key' => 'mail.overdue_line',
                        'message_params' => ['title' => $loanBook->title],
                    ],
                ];
            }
        }

        if ($fine instanceof Fine) {
            $notifications[] = [
                'type' => FineGeneratedNotification::class,
                'data' => [
                    'type' => NotificationType::FineGenerated->value,
                    'fine_id' => $fine->id,
                    'message_key' => 'mail.fine_message',
                    'message_params' => ['amount' => $fine->amount],
                ],
            ];
        }

        foreach ($notifications as $index => $notification) {
            DB::table('notifications')->insert([
                'id' => Str::uuid()->toString(),
                'type' => $notification['type'],
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'data' => json_encode($notification['data'], JSON_THROW_ON_ERROR),
                'read_at' => $index === 0 ? now() : null,
                'created_at' => now()->subHours($index + 1),
                'updated_at' => now()->subHours($index + 1),
            ]);
        }
    }
}
