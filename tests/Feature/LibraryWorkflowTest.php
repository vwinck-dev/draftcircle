<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Books\RateBookAction;
use App\Actions\Loans\GenerateFineAction;
use App\Actions\Loans\ReturnLoanAction;
use App\Actions\Loans\ValidateBookAvailabilityAction;
use App\Enums\BookFormat;
use App\Enums\LoanStatus;
use App\Enums\LoanType;
use App\Enums\UserRole;
use App\Models\Book;
use App\Models\BookRating;
use App\Models\Category;
use App\Models\Loan;
use App\Models\User;
use App\Services\BookService;
use App\Services\LoanService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\ValidationException;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

final class LibraryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_should_create_reservation_when_book_is_available(): void
    {
        $user = User::factory()->create();
        $book = $this->book();

        $loan = app(LoanService::class)->reserveBook(user: $user, book: $book);

        $this->assertSame(LoanStatus::Pending, $loan->status);
        $this->assertSame(2, $book->refresh()->available_copies);
    }

    public function test_should_allow_staff_to_reserve_available_book(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $book = $this->book();

        $this->actingAs($admin)->post(route('loans.store', $book))->assertRedirect();

        $this->assertDatabaseHas('loans', [
            'user_id' => $admin->id,
            'book_id' => $book->id,
            'status' => LoanStatus::Pending->value,
        ]);
    }

    public function test_should_invalidate_home_catalog_when_books_change(): void
    {
        $this->assertCount(0, app(BookService::class)->homeCatalog()['featured']);

        $this->book();

        $this->assertCount(1, app(BookService::class)->homeCatalog()['featured']);
    }

    public function test_should_load_and_refresh_ratings_on_home_catalog(): void
    {
        $book = $this->book();
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();
        BookRating::query()->create(['book_id' => $book->id, 'user_id' => $firstUser->id, 'rating' => 2]);

        $initial = app(BookService::class)->homeCatalog()['newArrivals']->firstOrFail();
        $this->assertSame(2.0, (float) $initial->ratings_avg_rating);
        $this->assertSame(1, $initial->ratings_count);

        app(RateBookAction::class)->execute(user: $secondUser, book: $book, rating: 4);

        $updated = app(BookService::class)->homeCatalog()['newArrivals']->firstOrFail();
        $this->assertSame(3.0, (float) $updated->ratings_avg_rating);
        $this->assertSame(2, $updated->ratings_count);
    }

    public function test_should_personalize_recommendations_from_consented_click_cookie(): void
    {
        $user = User::factory()->create();
        $interest = Category::query()->create(['name' => 'Engineering', 'slug' => 'engineering']);
        $other = Category::query()->create(['name' => 'History', 'slug' => 'history']);
        $seenBook = $this->book();
        $seenBook->categories()->attach($interest);
        $recommendedBook = $this->book();
        $recommendedBook->update(['title' => 'Recommended Engineering', 'loan_count' => 2]);
        $recommendedBook->categories()->attach($interest);
        $popularBook = $this->book();
        $popularBook->update(['title' => 'Popular History', 'loan_count' => 100]);
        $popularBook->categories()->attach($other);
        $interests = rawurlencode(json_encode([
            'categories' => [(string) $interest->id => 4],
            'books' => [$seenBook->id],
        ], JSON_THROW_ON_ERROR));

        $this->withUnencryptedCookies([
            'library_cookie_consent' => 'accepted',
            'library_recommendation_interests' => $interests,
        ])->actingAs($user)->get(route('home'))->assertInertia(
            fn (AssertableInertia $page) => $page
                ->where('recommendations.personalized', true)
                ->where('recommendations.books.0.id', $recommendedBook->id),
        );
    }

    public function test_should_reject_reservation_when_book_is_unavailable(): void
    {
        $book = $this->book(availableCopies: 0);

        $this->expectException(ValidationException::class);

        app(ValidateBookAvailabilityAction::class)->execute(book: $book);
    }

    public function test_should_return_book_when_loan_is_active(): void
    {
        $book = $this->book(availableCopies: 2);
        $loan = Loan::query()->create([
            'user_id' => User::factory()->create()->id,
            'book_id' => $book->id,
            'type' => LoanType::Loan,
            'status' => LoanStatus::Active,
            'due_at' => CarbonImmutable::now()->addDay(),
        ]);

        $returned = app(ReturnLoanAction::class)->execute(loan: $loan);

        $this->assertSame(LoanStatus::Returned, $returned->status);
        $this->assertSame(3, $book->refresh()->available_copies);
    }

    public function test_should_generate_fine_when_loan_is_overdue(): void
    {
        $book = $this->book();
        $loan = Loan::query()->create([
            'user_id' => User::factory()->create()->id,
            'book_id' => $book->id,
            'type' => LoanType::Loan,
            'status' => LoanStatus::Overdue,
            'due_at' => CarbonImmutable::now()->subDays(3),
        ]);

        $fine = app(GenerateFineAction::class)->execute(loan: $loan, dailyAmount: 2.0);

        $this->assertGreaterThanOrEqual(6, (float) $fine->amount);
    }

    public function test_should_replace_rating_when_user_rates_same_book_again(): void
    {
        $user = User::factory()->create();
        $book = $this->book();
        $action = app(RateBookAction::class);

        $action->execute(user: $user, book: $book, rating: 3);
        $action->execute(user: $user, book: $book, rating: 5);

        $this->assertDatabaseCount('book_ratings', 1);
        $this->assertDatabaseHas('book_ratings', ['book_id' => $book->id, 'user_id' => $user->id, 'rating' => 5]);
    }

    public function test_should_deny_admin_panel_when_user_is_student(): void
    {
        $response = $this->actingAs(User::factory()->create())->get(route('admin.dashboard'));

        $response->assertForbidden();
    }

    public function test_should_allow_admin_panel_when_user_is_librarian(): void
    {
        $response = $this->actingAs(User::factory()->create(['role' => UserRole::Librarian]))->get(route('admin.dashboard'));

        $response->assertOk();
    }

    public function test_should_cancel_expired_reservation_when_command_runs(): void
    {
        $book = $this->book(availableCopies: 2);
        Loan::query()->create([
            'user_id' => User::factory()->create()->id,
            'book_id' => $book->id,
            'type' => LoanType::Reservation,
            'status' => LoanStatus::Pending,
            'reserved_at' => CarbonImmutable::now()->subHours(49),
        ]);

        $this->assertSame(0, Artisan::call('library:clean-expired-reservations'));

        $this->assertDatabaseHas('loans', ['book_id' => $book->id, 'status' => LoanStatus::Cancelled->value]);
        $this->assertSame(3, $book->refresh()->available_copies);
    }

    private function book(int $availableCopies = 3): Book
    {
        return Book::query()->create([
            'title' => 'Domain-Driven Design',
            'author' => 'Eric Evans',
            'format' => BookFormat::Both,
            'total_copies' => 3,
            'available_copies' => $availableCopies,
            'is_active' => true,
        ]);
    }
}
