<?php

declare(strict_types=1);

namespace App\Enums;

enum SettingGroup: string
{
    case General = 'general';
    case Loans = 'loans';
    case Fines = 'fines';
    case Notifications = 'notifications';
    case Maintenance = 'maintenance';
}
