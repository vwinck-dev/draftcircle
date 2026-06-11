<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SettingGroup;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $key
 * @property string|null $value
 * @property SettingGroup $group
 */
final class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['group' => SettingGroup::class];
    }
}
