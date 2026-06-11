<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Notifications;

use App\Actions\Notifications\ListNotificationsAction;
use App\Enums\BookFormat;
use App\Models\Book;
use App\Models\User;
use App\Notifications\NewBookNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ListNotificationsActionTest extends TestCase
{
    use RefreshDatabase;

    private ListNotificationsAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(ListNotificationsAction::class);
    }

    public function test_returns_notifications_for_user(): void
    {
        $user = User::factory()->create();
        $book = Book::query()->create([
            'title' => 'New Book',
            'author' => 'Author',
            'format' => BookFormat::Physical,
            'total_copies' => 1,
            'available_copies' => 1,
            'is_active' => true,
        ]);
        $user->notify(new NewBookNotification(book: $book));

        $notifications = $this->action->execute(user: $user);

        $this->assertCount(1, $notifications);
    }

    public function test_does_not_return_other_users_notifications(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $book = Book::query()->create([
            'title' => 'New Book',
            'author' => 'Author',
            'format' => BookFormat::Physical,
            'total_copies' => 1,
            'available_copies' => 1,
            'is_active' => true,
        ]);
        $other->notify(new NewBookNotification(book: $book));

        $notifications = $this->action->execute(user: $user);

        $this->assertCount(0, $notifications);
    }

    public function test_returns_empty_collection_when_user_has_no_notifications(): void
    {
        $user = User::factory()->create();

        $notifications = $this->action->execute(user: $user);

        $this->assertCount(0, $notifications);
    }

    public function test_limits_to_100_notifications(): void
    {
        $user = User::factory()->create();
        $book = Book::query()->create([
            'title' => 'Book',
            'author' => 'Author',
            'format' => BookFormat::Physical,
            'total_copies' => 1,
            'available_copies' => 1,
            'is_active' => true,
        ]);

        for ($i = 0; $i < 105; $i++) {
            $user->notify(new NewBookNotification(book: $book));
        }

        $notifications = $this->action->execute(user: $user);

        $this->assertCount(100, $notifications);
    }
}
