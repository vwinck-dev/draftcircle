<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Services\CacheService;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            BookSeeder::class,
            LoanSeeder::class,
            ActivitySeeder::class,
            SettingSeeder::class,
        ]);

        app(CacheService::class)->flushBooks();
        app(CacheService::class)->flushLoans();
        app(CacheService::class)->flushSettings();
    }
}
