<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BackupController extends Controller
{
    protected function getSettingsPath()
    {
        return storage_path('app/backup_settings.json');
    }

    protected function getSettings()
    {
        $path = $this->getSettingsPath();
        if (file_exists($path)) {
            return json_decode(file_get_contents($path), true);
        }

        return [
            'db_backup_enabled' => true,
            'db_backup_frequency' => 'daily',
            'db_backup_time' => '02:00',
            'full_backup_enabled' => false,
            'full_backup_frequency' => 'weekly',
            'full_backup_time' => '03:00',
        ];
    }

    public function index()
    {
        $backupName = config('backup.backup.name', env('BACKUP_NAME', 'laravel-backup'));
        $disk = Storage::disk('local');
        
        $backups = [];
        if ($disk->exists($backupName)) {
            $files = $disk->files($backupName);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'zip') {
                    $backups[] = [
                        'name' => basename($file),
                        'size' => $this->formatBytes($disk->size($file)),
                        'date' => Carbon::createFromTimestamp($disk->lastModified($file))->toDateTimeString(),
                        'raw_date' => $disk->lastModified($file),
                    ];
                }
            }
        }

        // Sort by newest first
        usort($backups, function ($a, $b) {
            return $b['raw_date'] <=> $a['raw_date'];
        });

        $settings = $this->getSettings();

        return view('pages.backups.index', compact('backups', 'settings'));
    }

    public function create(Request $request)
    {
        $request->validate([
            'type' => 'required|in:db,full',
        ]);

        set_time_limit(300);

        try {
            if ($request->type === 'db') {
                $exitCode = Artisan::call('backup:run --only-db --disable-notifications');
            } else {
                $exitCode = Artisan::call('backup:run --disable-notifications');
            }

            if ($exitCode !== 0) {
                $output = Artisan::output();
                Log::error('Backup failed with exit code ' . $exitCode . '. Output: ' . $output);
                
                // Extract failure reason if available
                $errorMessage = 'Backup failed.';
                if (preg_match('/Backup failed because:?\s*(.*)/i', $output, $matches)) {
                    $errorMessage .= ' Reason: ' . trim($matches[1]);
                }
                
                return redirect()->back()->with('error', $errorMessage);
            }

            return redirect()->back()->with('success', 'Backup generated successfully.');
        } catch (\Exception $e) {
            Log::error('Backup failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    public function download($fileName)
    {
        // Safe check
        $fileName = basename($fileName);
        $backupName = config('backup.backup.name', env('BACKUP_NAME', 'laravel-backup'));
        $filePath = $backupName . '/' . $fileName;

        if (Storage::disk('local')->exists($filePath)) {
            return Storage::disk('local')->download($filePath);
        }

        return redirect()->back()->with('error', 'Backup file not found.');
    }

    public function destroy($fileName)
    {
        $fileName = basename($fileName);
        $backupName = config('backup.backup.name', env('BACKUP_NAME', 'laravel-backup'));
        $filePath = $backupName . '/' . $fileName;

        if (Storage::disk('local')->exists($filePath)) {
            Storage::disk('local')->delete($filePath);
            return redirect()->back()->with('success', 'Backup deleted successfully.');
        }

        return redirect()->back()->with('error', 'Backup file not found.');
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'db_backup_enabled' => 'nullable|boolean',
            'db_backup_frequency' => 'required|in:daily,weekly,monthly',
            'db_backup_time' => 'required|date_format:H:i',
            'full_backup_enabled' => 'nullable|boolean',
            'full_backup_frequency' => 'required|in:daily,weekly,monthly',
            'full_backup_time' => 'required|date_format:H:i',
        ]);

        $settings = [
            'db_backup_enabled' => $request->has('db_backup_enabled'),
            'db_backup_frequency' => $validated['db_backup_frequency'],
            'db_backup_time' => $validated['db_backup_time'],
            'full_backup_enabled' => $request->has('full_backup_enabled'),
            'full_backup_frequency' => $validated['full_backup_frequency'],
            'full_backup_time' => $validated['full_backup_time'],
        ];

        file_put_contents($this->getSettingsPath(), json_encode($settings, JSON_PRETTY_PRINT));

        return redirect()->back()->with('success', 'Backup schedule settings updated successfully.');
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
