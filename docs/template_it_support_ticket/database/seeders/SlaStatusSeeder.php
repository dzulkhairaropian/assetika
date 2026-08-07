<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SlaStatusSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('status_slas')->upsert([
            ['name' => 'Fulfilled', 'code' => 'FULFILLED', 'color' => 'success', 'icon' => 'heroicon-o-check-circle', 'description' => 'SLA requirements met', 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Warning', 'code' => 'WARNING', 'color' => 'warning', 'icon' => 'heroicon-o-exclamation-triangle', 'description' => 'Approaching SLA deadline', 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Breached', 'code' => 'BREACHED', 'color' => 'danger', 'icon' => 'heroicon-o-x-circle', 'description' => 'SLA deadline exceeded', 'sort_order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Paused', 'code' => 'PAUSED', 'color' => 'gray', 'icon' => 'heroicon-o-pause-circle', 'description' => 'SLA counting is paused', 'sort_order' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ], ['code']);
    }
}
