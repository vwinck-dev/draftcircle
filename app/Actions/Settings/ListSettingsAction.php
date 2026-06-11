<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Models\Setting;

/**
 * Retrieves all settings from the database, organized by their respective groups, and returns them as a nested associative array where the first level keys are the setting groups and the second level keys are the setting keys with their corresponding values.
 */
final readonly class ListSettingsAction
{
    /**
     * Retrieves all settings from the database, organized by their respective groups, and
     * returns them as a nested associative array where the first level keys are the setting
     * groups and the second level keys are the setting keys with their corresponding values.
     *
     * @return array<string, array<string, string|null>>
     */
    public function execute(): array
    {
        $result = [];

        foreach (Setting::query()->orderBy('group')->get() as $setting) {
            $result[$setting->group->value][$setting->key] = $setting->value;
        }

        return $result;
    }
}
