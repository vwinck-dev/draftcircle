<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Settings\ListSettingsAction;
use App\Actions\Settings\SaveSettingsAction;
use App\Enums\SettingGroup;

final readonly class SettingService
{
    public function __construct(
        private ListSettingsAction $listSettings,
        private SaveSettingsAction $saveSettings,
        private CacheService $cache,
    ) {}

    /** @return array<string, array<string, string|null>> */
    public function all(): array
    {
        return $this->cache->remember(
            key: 'settings:all',
            seconds: 86400,
            callback: fn (): array => $this->listSettings->execute(),
        );
    }

    /** @param array<string, string|null> $settings */
    public function save(SettingGroup $group, array $settings): void
    {
        $this->saveSettings->execute(group: $group, settings: $settings);
        $this->cache->flushSettings();
    }
}
