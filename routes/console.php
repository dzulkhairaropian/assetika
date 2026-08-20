<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

// Read backup settings and dynamically apply console schedules
$settingsPath = storage_path('app/backup_settings.json');
if (file_exists($settingsPath)) {
    $settings = json_decode(file_get_contents($settingsPath), true);
    
    // Database Backup Schedule
    if (!empty($settings['db_backup_enabled'])) {
        $dbEvent = Schedule::command('backup:run', ['--only-db' => true, '--disable-notifications' => true]);
        $frequency = $settings['db_backup_frequency'] ?? 'daily';
        $time = $settings['db_backup_time'] ?? '02:00';
        
        switch ($frequency) {
            case 'daily':
                $dbEvent->dailyAt($time);
                break;
            case 'weekly':
                $dbEvent->weeklyOn(1, $time);
                break;
            case 'monthly':
                $dbEvent->monthlyOn(1, $time);
                break;
        }
    }

    // Full Backup (Files + DB) Schedule
    if (!empty($settings['full_backup_enabled'])) {
        $fullEvent = Schedule::command('backup:run', ['--disable-notifications' => true]);
        $frequency = $settings['full_backup_frequency'] ?? 'weekly';
        $time = $settings['full_backup_time'] ?? '03:00';
        
        switch ($frequency) {
            case 'daily':
                $fullEvent->dailyAt($time);
                break;
            case 'weekly':
                $fullEvent->weeklyOn(1, $time);
                break;
            case 'monthly':
                $fullEvent->monthlyOn(1, $time);
                break;
        }
    }
    
    // Auto Clean Old Backups (Runs daily)
    Schedule::command('backup:clean', ['--disable-notifications' => true])->dailyAt('01:00');
} else {
    // Default fallback schedule if settings file does not exist yet
    Schedule::command('backup:run', ['--only-db' => true, '--disable-notifications' => true])->dailyAt('02:00');
    Schedule::command('backup:clean', ['--disable-notifications' => true])->dailyAt('01:00');
}
