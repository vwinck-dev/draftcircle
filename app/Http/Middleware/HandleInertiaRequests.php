<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\BookService;
use App\Support\RecommendationCookie;
use Illuminate\Http\Request;
use Inertia\Middleware;

final class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'i18n' => [
                'locale' => app()->getLocale(),
                'supported' => config('app.supported_locales'),
                'translations' => trans('ui'),
            ],
            'notifications' => [
                'unread' => fn (): int => $this->unreadCount(request: $request),
            ],
            'recommendations' => fn (): array => $this->shouldShareRecommendations($request)
                ? $this->recommendations(request: $request)
                : ['personalized' => false, 'books' => new \Illuminate\Database\Eloquent\Collection],
            'flash' => [
                'success' => fn (): ?string => $this->sessionString(key: 'success'),
                'error' => fn (): ?string => $this->sessionString(key: 'error'),
            ],
        ];
    }

    private function unreadCount(Request $request): int
    {
        $user = $request->user();

        return $user instanceof User ? $user->unreadNotifications()->count() : 0;
    }

    private function shouldShareRecommendations(Request $request): bool
    {
        return in_array($request->route()?->getName(), ['home', 'books.index', 'books.show'], true);
    }

    /** @return array{personalized: bool, books: \Illuminate\Database\Eloquent\Collection<int, \App\Models\Book>} */
    private function recommendations(Request $request): array
    {
        if (! $request->user() instanceof User) {
            return ['personalized' => false, 'books' => new \Illuminate\Database\Eloquent\Collection];
        }

        $interests = RecommendationCookie::fromRequest(request: $request);

        return [
            'personalized' => $interests['categoryWeights'] !== [],
            'books' => app(BookService::class)->recommendations(
                categoryWeights: $interests['categoryWeights'],
                seenBookIds: $interests['bookIds'],
            ),
        ];
    }

    private function sessionString(string $key): ?string
    {
        $value = session($key);

        return is_string($value) ? $value : null;
    }
}
