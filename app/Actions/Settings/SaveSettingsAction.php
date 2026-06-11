<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Enums\SettingGroup;
use App\Models\Setting;

/**
 * Handles the saving of settings for a specific setting group, allowing for the creation or updating of settings in the database based on the provided key-value pairs.
 */
final readonly class SaveSettingsAction
{
    /**
     * Saves settings for a specific setting group, updating existing settings or creating
     * new ones based on the provided key-value pairs. The method iterates through the settings
     * array and uses the "updateOrCreate" method to ensure that each setting is properly saved in
     * the database with the correct group and type.
     *
     * @param array<string, string|null> $settings
     */
    public function execute(SettingGroup $group, array $settings): void
    {
        foreach ($settings as $key => $value) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => $group, 'type' => 'string'],
            );
        }
    }
}
