<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\Book;
use App\Models\Loan;
use App\Models\Setting;
use App\Observers\BookObserver;
use App\Observers\LoanObserver;
use App\Observers\SettingObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        Book::observe(BookObserver::class);
        Loan::observe(LoanObserver::class);
        Setting::observe(SettingObserver::class);

        Gate::define('access-admin', fn ($user): bool => $user->isStaff());
        Gate::define('manage-settings', fn ($user): bool => $user->role === UserRole::Admin);
    }
}
