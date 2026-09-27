<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('attendance:mark-absent')->dailyAt('23:59');
Schedule::command('leave:allocate-yearly')->yearlyOn(1, 1, '00:01');
Schedule::command('tasks:send-due-notifications')->dailyAt('08:00');
Schedule::command('meetings:send-reminders')->everyFifteenMinutes();

