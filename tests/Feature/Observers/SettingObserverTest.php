<?php

declare(strict_types=1);

namespace Tests\Feature\Observers;

use App\Enums\SettingGroup;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class SettingObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_flushes_settings_cache_when_setting_is_created(): void
    {
        Cache::put('settings:all', ['cached' => true], 86400);

        Setting::query()->create([
            'key' => 'blocked_comment_words',
            'value' => 'spam',
            'group' => SettingGroup::General,
            'type' => 'string',
        ]);

        $this->assertNull(Cache::get('settings:all'));
    }

    public function test_flushes_settings_cache_when_setting_is_updated(): void
    {
        $setting = Setting::query()->create([
            'key' => 'some_key',
            'value' => 'old_value',
            'group' => SettingGroup::General,
            'type' => 'string',
        ]);
        Cache::put('settings:all', ['cached' => true], 86400);

        $setting->update(['value' => 'new_value']);

        $this->assertNull(Cache::get('settings:all'));
    }

    public function test_flushes_settings_cache_when_setting_is_deleted(): void
    {
        $setting = Setting::query()->create([
            'key' => 'temp_key',
            'value' => 'value',
            'group' => SettingGroup::General,
            'type' => 'string',
        ]);
        Cache::put('settings:all', ['cached' => true], 86400);

        $setting->delete();

        $this->assertNull(Cache::get('settings:all'));
    }
}
