<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

Schedule::command('library:send-overdue-notifications')->dailyAt(time: '08:00');

Schedule::command('library:clean-expired-reservations')->hourly();

Schedule::command('library:generate-report')->weeklyOn(dayOfWeek: 1, time: '07:00');

Schedule::command('library:process-pending-fines')->dailyAt(time: '00:00');

Schedule::command('library:warm-cache')->everySixHours();

Schedule::command('horizon:snapshot')->everyFiveMinutes();
