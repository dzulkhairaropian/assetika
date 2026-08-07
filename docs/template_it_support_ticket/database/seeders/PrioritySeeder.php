<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PrioritySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('priorities')->upsert([
            ['name' => 'Critical', 'code' => 'P1', 'label' => null, 'color' => 'danger', 'icon' => 'heroicon-o-exclamation-circle', 'response_time_minutes' => 30, 'resolution_time_hours' => 4, 'description' => 'Critical priority - Immediate attention required', 'example_cases' => null, 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'High', 'code' => 'P2', 'label' => null, 'color' => 'warning', 'icon' => 'heroicon-o-exclamation-triangle', 'response_time_minutes' => 60, 'resolution_time_hours' => 8, 'description' => 'High priority - Urgent attention required', 'example_cases' => null, 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Medium', 'code' => 'P3', 'label' => null, 'color' => 'warning', 'icon' => 'heroicon-o-minus-circle', 'response_time_minutes' => 120, 'resolution_time_hours' => 24, 'description' => 'Medium priority - Standard response', 'example_cases' => null, 'sort_order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Low', 'code' => 'P4', 'label' => null, 'color' => 'success', 'icon' => 'heroicon-o-information-circle', 'response_time_minutes' => 240, 'resolution_time_hours' => 48, 'description' => 'Low priority - Routine request', 'example_cases' => null, 'sort_order' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ], ['code']);
    }
}
