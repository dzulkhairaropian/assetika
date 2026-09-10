<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
        $addedNames = [];

        // Check Storage::disk('local') (storage/app/private/{backupName})
        if ($disk->exists($backupName)) {
            $files = $disk->files($backupName);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'zip') {
                    $baseName = basename($file);
                    $backups[] = [
                        'name' => $baseName,
                        'size' => $this->formatBytes($disk->size($file)),
                        'date' => Carbon::createFromTimestamp($disk->lastModified($file))->setTimezone(config('app.timezone', 'Asia/Jakarta'))->format('Y-m-d H:i:s'),
                        'raw_date' => $disk->lastModified($file),
                    ];
                    $addedNames[$baseName] = true;
                }
            }
        }

        // Check fallback storage path (storage/app/{backupName})
        $rootPath = storage_path("app/{$backupName}");
        if (file_exists($rootPath)) {
            $globFiles = glob($rootPath.'/*.zip');
            foreach ($globFiles as $file) {
                $baseName = basename($file);
                if (! isset($addedNames[$baseName])) {
                    $backups[] = [
                        'name' => $baseName,
                        'size' => $this->formatBytes(filesize($file)),
                        'date' => Carbon::createFromTimestamp(filemtime($file))->setTimezone(config('app.timezone', 'Asia/Jakarta'))->format('Y-m-d H:i:s'),
                        'raw_date' => filemtime($file),
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
            $params = [
                '--disable-notifications' => true,
            ];

            if ($request->type === 'db') {
                $params['--only-db'] = true;
            }

            $exitCode = Artisan::call('backup:run', $params);
            $output = Artisan::output();

            if ($exitCode !== 0) {
                Log::warning('Spatie backup failed with exit code '.$exitCode.'. Attempting PDO fallback dump. Output: '.$output);

                // Fallback to PDO database backup if type is 'db' or if Spatie fails
                try {
                    $this->fallbackDbBackup();

                    return redirect()->back()->with('success', 'Backup generated successfully (via PDO fallback).');
                } catch (\Exception $fallbackException) {
                    Log::error('PDO Fallback backup also failed: '.$fallbackException->getMessage());

                    $errorMessage = 'Backup failed.';
                    if (preg_match('/Backup failed because:?\s*(.*)/i', $output, $matches)) {
                        $errorMessage .= ' Reason: '.trim($matches[1]);
                    } elseif (! empty(trim($output))) {
                        $errorMessage .= ' Output: '.trim(strip_tags($output));
                    }

                    return redirect()->back()->with('error', $errorMessage);
                }
            }

            return redirect()->back()->with('success', 'Backup generated successfully.');
        } catch (\Exception $e) {
            Log::error('Backup failed: '.$e->getMessage());

            // Try PDO fallback for DB backups
            try {
                $this->fallbackDbBackup();

                return redirect()->back()->with('success', 'Backup generated successfully (via PDO fallback).');
            } catch (\Exception $fallbackException) {
                return redirect()->back()->with('error', 'Backup failed: '.$e->getMessage());
            }
        }
    }

    protected function fallbackDbBackup()
    {
        $connectionName = config('database.default', 'mysql');
        $dbConfig = config("database.connections.{$connectionName}", []);
        $database = $dbConfig['database'] ?? env('DB_DATABASE', 'laravel');
        $backupName = config('backup.backup.name', env('BACKUP_NAME', 'laravel-backup'));
        $tz = config('app.timezone', 'Asia/Jakarta');
        $now = Carbon::now($tz);
        $timestamp = $now->format('Y-m-d-H-i-s');
        $sqlFileName = "db-dumps/mysql-{$database}-{$timestamp}.sql";
        $zipFileName = "{$backupName}/backup-{$timestamp}.zip";

        /** @var \PDO $pdo */
        $pdo = DB::connection($connectionName)->getPdo();

        $sql = "-- Assetika Database Backup (PDO Fallback)\n";
        $sql .= '-- Host: '.($dbConfig['host'] ?? env('DB_HOST', '127.0.0.1')).' Port: '.($dbConfig['port'] ?? env('DB_PORT', '3306'))."\n";
        $sql .= "-- Database: {$database}\n";
        $sql .= '-- Date: '.$now->format('Y-m-d H:i:s')."\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        $tables = DB::connection($connectionName)->select('SHOW TABLES');
        if (empty($tables)) {
            throw new \Exception('No tables found in database.');
        }

        $tableKey = "Tables_in_{$database}";

        foreach ($tables as $tableObj) {
            $arr = (array) $tableObj;
            $tableName = $arr[$tableKey] ?? current($arr);

            $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";

            $createTable = DB::connection($connectionName)->select("SHOW CREATE TABLE `{$tableName}`");
            if (! empty($createTable)) {
                $createSql = ((array) $createTable[0])['Create Table'] ?? '';
                $sql .= $createSql.";\n\n";
            }

            $rows = DB::connection($connectionName)->table($tableName)->get();
            if ($rows->count() > 0) {
                $sql .= "INSERT INTO `{$tableName}` VALUES\n";
                $inserts = [];
                foreach ($rows as $row) {
                    $values = [];
                    foreach ((array) $row as $val) {
                        if ($val === null) {
                            $values[] = 'NULL';
                        } else {
                            $values[] = $pdo->quote($val);
                        }
                    }
                    $inserts[] = '('.implode(',', $values).')';
                }
                $sql .= implode(",\n", $inserts).";\n\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        $relativeZipPath = "{$backupName}/backup-{$timestamp}.zip";
        $zipPath = Storage::disk('local')->path($relativeZipPath);
        $zipDir = dirname($zipPath);
        if (! file_exists($zipDir)) {
            mkdir($zipDir, 0755, true);
        }

        $zip = new \ZipArchive;
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            $zip->addFromString($sqlFileName, $sql);
            $zip->close();

            return true;
        }

        throw new \Exception('Failed to create backup ZIP archive.');
    }

    public function download($fileName)
    {
        // Safe check
        $fileName = basename($fileName);
        $backupName = config('backup.backup.name', env('BACKUP_NAME', 'laravel-backup'));
        $filePath = $backupName.'/'.$fileName;

        if (Storage::disk('local')->exists($filePath)) {
            return Storage::disk('local')->download($filePath);
        }

        $directPath = storage_path("app/{$filePath}");
        if (file_exists($directPath)) {
            return response()->download($directPath);
        }

        return redirect()->back()->with('error', 'Backup file not found.');
    }

    public function destroy($fileName)
    {
        $fileName = basename($fileName);
        $backupName = config('backup.backup.name', env('BACKUP_NAME', 'laravel-backup'));
        $filePath = $backupName.'/'.$fileName;

        $deleted = false;
        if (Storage::disk('local')->exists($filePath)) {
            Storage::disk('local')->delete($filePath);
            $deleted = true;
        }

        $directPath = storage_path("app/{$filePath}");
        if (file_exists($directPath)) {
            @unlink($directPath);
            $deleted = true;
        }

        if ($deleted) {
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

        return round($bytes, $precision).' '.$units[$pow];
    }
}
