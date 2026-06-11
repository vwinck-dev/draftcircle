<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Setting;
use App\Services\CacheService;

final readonly class SettingObserver
{
    public function __construct(private CacheService $cache) {}

    public function saved(Setting $setting): void
    {
        $this->cache->flushSettings();
    }

    public function deleted(Setting $setting): void
    {
        $this->cache->flushSettings();
    }
}
