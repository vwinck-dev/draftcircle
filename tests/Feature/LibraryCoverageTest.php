<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Auth\FindOrCreateGoogleUserAction;
use App\Actions\Notifications\MarkAllNotificationsReadAction;
use App\Actions\Notifications\SendOverdueNotificationAction;
use App\Actions\Notifications\SendReservationAvailableAction;
use App\DataTransferObjects\BookData;
use App\DataTransferObjects\LoanData;
use App\DataTransferObjects\UserData;
use App\Enums\BookFormat;
use App\Enums\LoanStatus;
use App\Enums\LoanType;
use App\Enums\SettingGroup;
use App\Enums\UserRole;
use App\Events\NewBookAddedEvent;
use App\Listeners\NotifyUsersAboutNewBookListener;
use App\Models\Book;
use App\Models\BookPage;
use App\Models\BookRating;
use App\Models\Category;
use App\Models\Fine;
use App\Models\Loan;
use App\Models\ReadingProgress;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\FineGeneratedNotification;
use App\Notifications\NewBookNotification;
use App\Notifications\OverdueLoanNotification;
use App\Notifications\ReservationAvailableNotification;
use App\Policies\AdminPolicy;
use App\Policies\BookPolicy;
use App\Policies\LoanPolicy;
use App\Policies\SettingPolicy;
use App\Services\BookService;
use App\Services\LoanService;
use App\Services\NotificationService;
use App\Services\SettingService;
use App\Services\UserNotificationService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Socialite\Two\User as SocialiteUser;
use stdClass;
use Tests\TestCase;

final class LibraryCoverageTest extends TestCase
{
    use RefreshDatabase;

    public function test_should_manage_catalog_when_staff_and_students_use_book_flows(): void
    {
        Notification::fake();
        $category = Category::query()->create(['name' => 'Engineering', 'slug' => 'engineering']);
        $staff = User::factory()->create(['role' => UserRole::Librarian]);
        $student = User::factory()->create();
        $data = BookData::fromArray([
            'title' => 'Clean Architecture',
            'author' => 'Robert Martin',
            'publisher' => 'Prentice Hall',
            'year' => 2017,
            'language' => 'en',
            'isbn' => '9780134494166',
            'description' => 'Architecture principles.',
            'format' => BookFormat::Both->value,
            'total_copies' => 4,
            'is_active' => true,
            'category_ids' => [$category->id, 'invalid'],
        ]);

        $book = app(BookService::class)->create(data: $data);
        BookPage::query()->create(['book_id' => $book->id, 'page_number' => 1, 'content' => 'Page one']);

        $this->actingAs($student)->get(route('home'))->assertOk();
        $this->get(route('books.index', ['search' => 'Clean', 'category' => $category->id, 'format' => 'both', 'available' => true, 'sort' => 'title']))->assertOk();
        $this->get(route('books.show', $book))->assertOk();
        $this->get(route('books.reader', $book))->assertOk();
        $this->put(route('books.progress', $book), ['page' => 1])->assertRedirect();
        $this->post(route('books.ratings.store', $book), ['rating' => 5])->assertRedirect();

        $this->actingAs($staff)->get(route('admin.books.index'))->assertOk();
        $this->get(route('admin.books.create'))->assertOk();
        $this->get(route('admin.books.edit', $book))->assertOk();
        $this->patch(route('admin.books.update', $book), $this->bookPayload(title: 'Clean Architecture Updated', categoryId: $category->id))->assertRedirect(route('admin.books.index'));
        $this->post(route('admin.books.store'), $this->bookPayload(title: 'Refactoring', categoryId: $category->id, isbn: '9780134757599'))->assertRedirect(route('admin.books.index'));

        $book->refresh();
        $this->assertSame('Clean Architecture Updated', $book->title);
        $this->assertSame(1, ReadingProgress::query()->whereBelongsTo($student)->whereBelongsTo($book)->firstOrFail()->current_page);
        $this->assertSame(5, BookRating::query()->whereBelongsTo($student)->whereBelongsTo($book)->firstOrFail()->rating);
        $this->assertCount(1, $book->categories);
        $this->assertCount(1, $book->pages);
        $this->assertCount(1, $book->ratings);
        $this->assertCount(0, $book->loans);
        $this->assertCount(2, $category->books);
        $this->assertSame($book->id, BookPage::query()->firstOrFail()->book()->firstOrFail()->id);
        $this->assertSame($student->id, BookRating::query()->firstOrFail()->user()->firstOrFail()->id);
        $this->assertTrue(BookFormat::Both->isDigital());
        $this->assertTrue(BookFormat::Both->isPhysical());
        $this->assertFalse(BookFormat::Physical->isDigital());
        $this->assertFalse(BookFormat::Digital->isPhysical());

        $this->patch(route('admin.books.update', $book), array_merge(
            $this->bookPayload(title: 'Clean Architecture Updated', categoryId: $category->id),
            [
                'pages_provided' => true,
                'pages' => [
                    ['content' => 'Updated page one'],
                    ['content' => 'A newly created second page'],
                ],
            ],
        ))->assertRedirect(route('admin.books.index'));

        $this->assertSame(
            ['Updated page one', 'A newly created second page'],
            $book->pages()->pluck('content')->all(),
        );

        $created = Book::query()->where('title', 'Refactoring')->firstOrFail();
        $this->delete(route('admin.books.destroy', $created))->assertRedirect();
        $this->assertSoftDeleted($created);
    }

    public function test_should_process_loans_notifications_reports_and_commands(): void
    {
        Notification::fake();
        Storage::fake('local');
        $student = User::factory()->create();
        $staff = User::factory()->create(['role' => UserRole::Librarian]);
        $other = User::factory()->create();
        $book = $this->book();

        $this->actingAs($student)->post(route('loans.store', $book))->assertRedirect();
        $reservation = Loan::query()->firstOrFail();
        $this->get(route('loans.index'))->assertOk();
        $this->actingAs($staff)->get(route('admin.loans.index'))->assertOk();
        $this->actingAs($other)->put(route('loans.return', $reservation))->assertForbidden();

        $activeLoan = Loan::query()->create([
            'user_id' => $student->id,
            'book_id' => $book->id,
            'type' => LoanType::Loan,
            'status' => LoanStatus::Active,
            'due_at' => CarbonImmutable::now()->subDays(3),
        ]);
        $this->actingAs($student)->put(route('loans.return', $activeLoan))->assertRedirect();
        $this->assertSame(LoanStatus::Returned, $activeLoan->refresh()->status);

        $overdueLoan = Loan::query()->create([
            'user_id' => $student->id,
            'book_id' => $book->id,
            'type' => LoanType::Loan,
            'status' => LoanStatus::Active,
            'due_at' => CarbonImmutable::now()->subDays(2),
        ]);
        $this->assertSame(0, Artisan::call('library:send-overdue-notifications'));
        $this->assertSame(LoanStatus::Overdue, $overdueLoan->refresh()->status);
        $this->assertSame(0, Artisan::call('library:process-pending-fines'));
        $fine = Fine::query()->whereBelongsTo($overdueLoan)->firstOrFail();
        $this->assertSame($student->id, $fine->user()->firstOrFail()->id);
        $this->assertSame($overdueLoan->id, $fine->loan()->firstOrFail()->id);

        $this->assertSame(0, Artisan::call('library:generate-report', ['--period' => 'daily']));
        $this->assertSame(0, Artisan::call('library:warm-cache'));
        $this->assertSame(0, Artisan::call('library:clean-expired-reservations'));
        Storage::disk('local')->assertExists(collect(Storage::disk('local')->allFiles('reports'))->firstOrFail());

        app(NotificationService::class)->sendOverdue(loan: $overdueLoan);
        app(NotificationService::class)->sendReservationAvailable(loan: $reservation);
        app(SendOverdueNotificationAction::class)->execute(loan: $overdueLoan);
        app(SendReservationAvailableAction::class)->execute(loan: $reservation);
        app(NotifyUsersAboutNewBookListener::class)->handle(new NewBookAddedEvent(book: $book));
        Notification::assertSentTo($student, OverdueLoanNotification::class);
        Notification::assertSentTo($student, ReservationAvailableNotification::class);

        $this->assertNotificationPayloads(book: $book, loan: $overdueLoan, fine: $fine, student: $student);
        $this->assertSame($student->id, $overdueLoan->user()->firstOrFail()->id);
        $this->assertSame($book->id, $overdueLoan->book()->firstOrFail()->id);
        $this->assertSame($fine->id, $overdueLoan->fine()->firstOrFail()->id);
        $this->assertCount(3, app(LoanService::class)->listFor(user: $staff, all: true));
    }

    public function test_should_manage_settings_users_policies_and_google_identity(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $librarian = User::factory()->create(['role' => UserRole::Librarian]);
        $student = User::factory()->create();
        $book = $this->book();
        $loan = Loan::query()->create([
            'user_id' => $student->id,
            'book_id' => $book->id,
            'type' => LoanType::Loan,
            'status' => LoanStatus::Active,
        ]);
        $setting = Setting::query()->create(['key' => 'library_name', 'value' => 'Library', 'group' => SettingGroup::General, 'type' => 'string']);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->get(route('admin.reports.index'))->assertOk();
        $this->get(route('admin.users.index'))->assertOk();
        $this->get(route('admin.users.show', $student))->assertOk();
        $this->get(route('admin.settings.index'))->assertOk();
        $this->put(route('admin.settings.update'), ['group' => 'general', 'settings' => ['library_name' => 'DraftCircle']])->assertRedirect();
        $this->assertSame('DraftCircle', app(SettingService::class)->all()['general']['library_name']);

        $this->assertPolicies(admin: $admin, librarian: $librarian, student: $student, book: $book, loan: $loan, setting: $setting);
        $this->assertTrue($admin->isStaff());
        $this->assertFalse($student->isStaff());
        $this->assertCount(1, $student->loans);
        $this->assertCount(0, $student->ratings);
        $this->assertSame($student->id, ReadingProgress::query()->create(['user_id' => $student->id, 'book_id' => $book->id, 'current_page' => 2])->user()->firstOrFail()->id);
        $this->assertSame($book->id, ReadingProgress::query()->firstOrFail()->book()->firstOrFail()->id);

        $socialiteUser = new SocialiteUser;
        $socialiteUser->email = 'google@example.com';
        $socialiteUser->name = 'Google Student';
        $socialiteUser->id = 'google-id';
        $socialiteUser->avatar = 'https://example.com/avatar.jpg';
        $googleUser = app(FindOrCreateGoogleUserAction::class)->execute(googleUser: $socialiteUser);
        $this->assertSame('google-id', $googleUser->google_id);

        $loanData = new LoanData(
            userId: $student->id,
            bookId: $book->id,
            type: LoanType::Loan,
            status: LoanStatus::Active,
            reservedAt: CarbonImmutable::now(),
            dueAt: CarbonImmutable::now()->addDay(),
        );
        $userData = new UserData(name: $student->name, email: $student->email, registrationNumber: null, role: UserRole::Student);
        $this->assertSame($student->id, $loanData->userId);
        $this->assertSame($student->email, $userData->email);
    }

    public function test_should_render_custom_http_error_views(): void
    {
        foreach ([401, 403, 404, 419, 429, 500, 503] as $status) {
            $html = view("errors.{$status}")->render();

            $this->assertStringContainsString((string) $status, $html);
            $this->assertStringContainsString(trans('ui.brand.name'), $html);
            $this->assertStringContainsString(trans("ui.http_errors.{$status}.title"), $html);
        }
    }

    private function assertNotificationPayloads(Book $book, Loan $loan, Fine $fine, User $student): void
    {
        $student->email_notifications = true;
        $notifications = [
            new NewBookNotification(book: $book),
            new OverdueLoanNotification(loan: $loan),
            new ReservationAvailableNotification(loan: $loan),
            new FineGeneratedNotification(fine: $fine),
        ];

        foreach ($notifications as $notification) {
            $this->assertSame(['database', 'broadcast', 'mail'], $notification->via($student));
            $this->assertSame(['database', 'broadcast'], $notification->via(new stdClass));
            $payload = $notification->toArray($student);
            $this->assertNotEmpty($payload);
            $this->assertArrayHasKey('message_key', $payload);
            $this->assertArrayHasKey('message_params', $payload);
            $this->assertArrayNotHasKey('message', $payload);
            $mail = $notification->toMail($student);
            $this->assertNotEmpty($mail->subject);
            $this->assertSame([
                'html' => 'emails.library-notification',
                'text' => 'emails.library-notification-text',
            ], $mail->view);
            $this->assertStringContainsString(trans('ui.brand.name'), view('emails.library-notification', $mail->viewData)->render());
            $this->assertStringContainsString(trans('ui.brand.name'), view('emails.library-notification-text', $mail->viewData)->render());
        }

        $this->assertCount(2, $notifications[2]->tags());

        DB::table('notifications')->insert([
            'id' => Str::uuid()->toString(),
            'type' => NewBookNotification::class,
            'notifiable_type' => User::class,
            'notifiable_id' => $student->id,
            'data' => json_encode($notifications[0]->toArray($student), JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = app(UserNotificationService::class);
        $this->assertCount(1, $service->list(user: $student));
        $this->assertSame(1, app(MarkAllNotificationsReadAction::class)->execute(user: $student));
        $this->assertCount(0, $student->unreadNotifications()->get());
        $this->actingAs($student)->get(route('notifications.index'))->assertOk();
        $this->put(route('notifications.read-all'))->assertRedirect()->assertSessionHas('success');
    }

    private function assertPolicies(User $admin, User $librarian, User $student, Book $book, Loan $loan, Setting $setting): void
    {
        $adminPolicy = new AdminPolicy;
        $bookPolicy = new BookPolicy;
        $loanPolicy = new LoanPolicy;
        $settingPolicy = new SettingPolicy;

        $this->assertTrue($adminPolicy->access($librarian));
        $this->assertTrue($bookPolicy->viewAny($student));
        $this->assertTrue($bookPolicy->view($student, $book));
        $this->assertTrue($bookPolicy->create($librarian));
        $this->assertTrue($bookPolicy->update($librarian, $book));
        $this->assertFalse($bookPolicy->delete($librarian, $book)); // book has active loan
        $freeBook = Book::query()->create(['title' => 'Free Book', 'author' => 'Author', 'format' => 'physical', 'total_copies' => 1, 'available_copies' => 1, 'is_active' => true]);
        $this->assertTrue($bookPolicy->delete($librarian, $freeBook)); // no loans
        $this->assertTrue($loanPolicy->viewAny($student));
        $this->assertTrue($loanPolicy->create($student, $book));
        $this->assertTrue($loanPolicy->create($admin, $book));
        $this->assertTrue($loanPolicy->view($student, $loan));
        $this->assertTrue($loanPolicy->update($librarian, $loan));
        $this->assertTrue($settingPolicy->viewAny($admin));
        $this->assertTrue($settingPolicy->update($admin, $setting));
        $this->assertFalse($settingPolicy->viewAny($student));
    }

    /** @return array<string, bool|int|string|list<int>> */
    private function bookPayload(string $title, int $categoryId, string $isbn = '9780132350884'): array
    {
        return [
            'title' => $title,
            'author' => 'Author',
            'publisher' => 'Publisher',
            'year' => 2020,
            'language' => 'en',
            'isbn' => $isbn,
            'description' => 'Description',
            'format' => BookFormat::Both->value,
            'total_copies' => 5,
            'is_active' => true,
            'category_ids' => [$categoryId],
        ];
    }

    private function book(): Book
    {
        return Book::query()->create([
            'title' => 'Domain-Driven Design',
            'author' => 'Eric Evans',
            'format' => BookFormat::Both,
            'total_copies' => 5,
            'available_copies' => 5,
            'is_active' => true,
        ]);
    }
}
