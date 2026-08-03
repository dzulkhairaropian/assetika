---
name: laravel-backup-troubleshoot
description: Troubleshoots and fixes silent failures in Laravel/Spatie backup package, ensuring proper error reporting.
source: auto-skill
extracted_at: '2026-07-20T04:55:29.167Z'
---

When the Laravel/Spatie backup package reports successful backups but no files are found or generated, follow these steps to diagnose and fix the issue:

## Procedure

1.  **Examine `BackupController`**:
    *   Inspect `app/Http/Controllers/BackupController.php`.
    *   Note how the `index()` method retrieves backup files (e.g., `Storage::disk('local')->files($backupName)`).
    *   Examine the `create()` method to see how `Artisan::call('backup:run ...')` is invoked and if its exit code is checked.

2.  **Verify Backup Configuration**:
    *   Check `config/backup.php` for the configured `backup.name` and `destination.disks`.
    *   The `backup.name` (e.g., `env('APP_NAME', 'laravel-backup')`) determines the subdirectory where backups are stored. Ensure this matches the `APP_NAME` in your `.env` file.
    *   The `destination.disks` specifies the storage disk (e.g., `'local'`).

3.  **Manually Run Backup and Capture Output**:
    *   The most critical step is to bypass the web interface and run the `backup:run` Artisan command directly in the terminal to capture its exact error output.
    *   Use `php artisan backup:run --only-db --disable-notifications 2>&1` (for database-only backup) or `php artisan backup:run --disable-notifications 2>&1` (for full backup).
    *   Analyze the output for any errors, especially `mysqldump` connection issues or filesystem write failures.

4.  **Debug Underlying Issues**:
    *   **MySQL Connection Errors** (`Can't connect to MySQL server`):
        *   Verify your database server (e.g., MySQL, MariaDB) is running. For Laragon, ensure all services are started.
        *   Check `DB_HOST`, `DB_PORT`, `DB_USERNAME`, `DB_PASSWORD` in your `.env` file match your running database instance.
        *   If `mysqldump` is trying to connect to a different host/port than your database, adjust the database configuration.
    *   **Filesystem Write Errors**: Check directory permissions for `storage/app` and the configured backup directory.

5.  **Enhance Error Reporting in `BackupController`**:
    *   The `Artisan::call()` method returns an exit code (`0` for success, non-zero for failure) but does not throw an exception directly.
    *   Modify the `create()` method in `BackupController.php` to check this exit code. If non-zero, capture `Artisan::output()` to get the error details and return a user-friendly error message to the frontend.

**Example Code Change (from this conversation):**

```php
// In app/Http/Controllers/BackupController.php

// ...
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
// ...
```
