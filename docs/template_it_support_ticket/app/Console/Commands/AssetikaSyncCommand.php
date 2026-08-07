<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AssetikaSyncCommand extends Command
{
    protected $signature = 'assetika:sync {--table= : Sync only specific table}';

    protected $description = 'Sync lookup tables, users, and assets from Assetika';

    private array $syncList = [
        'companies' => ['endpoint' => 'lookups/companies', 'key' => 'code'],
        'departments' => ['endpoint' => 'lookups/departments', 'key' => 'code'],
        'positions' => ['endpoint' => 'lookups/positions', 'key' => 'code'],
        'locations' => ['endpoint' => 'lookups/locations', 'key' => 'name'],
        'master_categories' => ['endpoint' => 'lookups/master-categories', 'key' => 'name'],
        'categories' => ['endpoint' => 'lookups/categories', 'key' => 'code'],
        'statuses' => ['endpoint' => 'lookups/statuses', 'key' => 'name'],
        'brands' => ['endpoint' => 'lookups/brands', 'key' => 'code'],
        'operating_systems' => ['endpoint' => 'lookups/operating-systems', 'key' => 'name'],
        'vendors' => ['endpoint' => 'lookups/vendors', 'key' => 'email'],
        'products' => ['endpoint' => 'lookups/products', 'key' => 'name'],
        'users' => ['endpoint' => 'sync/users', 'key' => 'email'],
        'assets' => ['endpoint' => 'sync/assets', 'key' => 'asset_tag'],
    ];

    public function handle(): int
    {
        $base = config('assetika.base_url');
        $token = config('assetika.api_token');
        $timeout = config('assetika.timeout');

        if (! $base || ! $token) {
            $this->error('ASSETIKA_BASE_URL and ASSETIKA_API_TOKEN must be set in .env');

            return self::FAILURE;
        }

        $tableFilter = $this->option('table');

        foreach ($this->syncList as $table => $config) {
            if ($tableFilter && $tableFilter !== $table) {
                continue;
            }

            $this->info("Syncing {$table}...");
            try {
                $response = Http::withToken($token)
                    ->timeout($timeout)
                    ->get("{$base}/{$config['endpoint']}");

                if ($response->failed()) {
                    $this->warn("  FAILED ({$response->status()}): {$table}");

                    continue;
                }

                $response_data = $response->json();
                $records = $response_data['data'] ?? $response_data;
            } catch (\Exception $e) {
                $this->warn("  CONNECTION FAILED: {$table} - ".$e->getMessage());

                continue;
            }
            $syncedIds = [];

            $columnsList = DB::getSchemaBuilder()->getColumnListing($table);
            $columnsList[] = 'deleted_at';
            $dateColumns = collect(DB::select("SHOW COLUMNS FROM `{$table}`"))
                ->filter(fn ($col) => $col->Type === 'date')
                ->pluck('Field')
                ->toArray();

            foreach ($records as $record) {
                $keyValue = $record[$config['key']] ?? null;
                if (! $keyValue) {
                    continue;
                }

                $data = Arr::only($record, $columnsList);
                $data['deleted_at'] = null;

                if (array_key_exists('is_active', $data) && $data['is_active'] === null) {
                    $data['is_active'] = true;
                }

                foreach (['created_at', 'updated_at', 'deleted_at'] as $timeKey) {
                    if (array_key_exists($timeKey, $data) && $data[$timeKey]) {
                        try {
                            $data[$timeKey] = Carbon::parse($data[$timeKey])->toDateTimeString();
                        } catch (\Exception $e) {
                            $data[$timeKey] = null;
                        }
                    }
                }

                foreach ($dateColumns as $dateCol) {
                    if (array_key_exists($dateCol, $data) && $data[$dateCol]) {
                        try {
                            $data[$dateCol] = Carbon::parse($data[$dateCol])->toDateString();
                        } catch (\Exception $e) {
                            $data[$dateCol] = null;
                        }
                    }
                }

                DB::table($table)->updateOrInsert(
                    [$config['key'] => $keyValue],
                    $data,
                );

                $id = DB::table($table)
                    ->where($config['key'], $keyValue)
                    ->value('id');

                $syncedIds[] = $id;
            }

            $existingIds = DB::table($table)
                ->whereNull('deleted_at')
                ->pluck('id')
                ->toArray();

            $toDelete = array_diff($existingIds, $syncedIds);

            if ($toDelete) {
                DB::table($table)
                    ->whereIn('id', $toDelete)
                    ->update(['deleted_at' => now()]);
            }

            $this->info('  Done: '.count($records).' records synced, '.count($toDelete).' soft-deleted');
        }

        cache()->put('assetika_last_sync_at', now()->toDateTimeString());
        $this->info('Sync completed.');

        return self::SUCCESS;
    }
}
