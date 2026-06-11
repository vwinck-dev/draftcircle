<?php

declare(strict_types=1);

namespace Tests\Feature\Listeners;

use App\Enums\BookFormat;
use App\Enums\LoanStatus;
use App\Enums\LoanType;
use App\Events\BookReturnedEvent;
use App\Events\FineGeneratedEvent;
use App\Events\NewBookAddedEvent;
use App\Models\Book;
use App\Models\Fine;
use App\Models\Loan;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

final class EventListenersTest extends TestCase
{
    use RefreshDatabase;

    public function test_book_returned_event_flushes_books_and_loans_cache(): void
    {
        Cache::put('home:catalog', ['cached' => true], 3600);
        Cache::put('admin:summary', ['cached' => true], 300);

        $loan = $this->loan();
        BookReturnedEvent::dispatch($loan);

        $this->assertNull(Cache::get('home:catalog'));
        $this->assertNull(Cache::get('admin:summary'));
    }

    public function test_fine_generated_event_sends_notification_to_user(): void
    {
        Notification::fake();

        $loan = $this->loan();
        $fine = Fine::query()->create([
            'loan_id' => $loan->id,
            'user_id' => $loan->user_id,
            'amount' => 5.00,
        ]);

        FineGeneratedEvent::dispatch($fine);

        Notification::assertSentTo(
            $loan->user,
            \App\Notifications\FineGeneratedNotification::class,
        );
    }

    public function test_new_book_added_event_notifies_all_users(): void
    {
        Notification::fake();

        User::factory()->count(3)->create();
        $book = $this->book();

        NewBookAddedEvent::dispatch($book);

        Notification::assertSentToTimes(
            User::first(),
            \App\Notifications\NewBookNotification::class,
        );
    }

    public function test_new_book_added_event_sends_to_each_existing_user(): void
    {
        Notification::fake();

        $users = User::factory()->count(2)->create();
        $book = $this->book();

        NewBookAddedEvent::dispatch($book);

        foreach ($users as $user) {
            Notification::assertSentTo($user, \App\Notifications\NewBookNotification::class);
        }
    }

    private function loan(): Loan
    {
        $book = $this->book();

        return Loan::query()->create([
            'user_id' => User::factory()->create()->id,
            'book_id' => $book->id,
            'type' => LoanType::Loan,
            'status' => LoanStatus::Active,
            'reserved_at' => CarbonImmutable::now(),
            'due_at' => CarbonImmutable::now()->addDays(14),
        ])->load(['book', 'user']);
    }

    private function book(): Book
    {
        return Book::query()->create([
            'title' => 'Book',
            'author' => 'Author',
            'format' => BookFormat::Physical,
            'total_copies' => 3,
            'available_copies' => 3,
            'is_active' => true,
        ]);
    }
}
