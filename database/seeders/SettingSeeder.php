<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\SettingGroup;
use App\Models\Setting;
use Illuminate\Database\Seeder;

final class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['library_name', 'DraftCircle', SettingGroup::General],
            ['organization_id', '', SettingGroup::General],
            ['blocked_comment_words', 'xgh', SettingGroup::General],
            ['loan_days', '14', SettingGroup::Loans],
            ['maximum_reservations', '3', SettingGroup::Loans],
            ['daily_fine_amount', '1.00', SettingGroup::Fines],
            ['fine_grace_days', '0', SettingGroup::Fines],
            ['email_notifications', '1', SettingGroup::Notifications],
            ['maintenance_mode', '0', SettingGroup::Maintenance],
        ];

        foreach ($settings as [$key, $value, $group]) {
            Setting::query()->updateOrCreate(
                attributes: ['key' => $key],
                values: ['value' => $value, 'group' => $group, 'type' => 'string'],
            );
        }
    }
}
