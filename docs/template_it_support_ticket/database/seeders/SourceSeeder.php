<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SourceSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('sources')->upsert([
            ['name' => 'Email', 'code' => 'EMAIL', 'color' => '#6B7280', 'icon' => 'heroicon-o-envelope', 'description' => 'Tickets received via email', 'is_active' => true, 'sort_order' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Phone', 'code' => 'PHONE', 'color' => '#6B7280', 'icon' => 'heroicon-o-phone', 'description' => 'Tickets received via phone call', 'is_active' => true, 'sort_order' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Walk-in', 'code' => 'WALKIN', 'color' => '#6B7280', 'icon' => 'heroicon-o-user', 'description' => 'Tickets from direct walk-in', 'is_active' => true, 'sort_order' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'System/Automated', 'code' => 'SYSTEM', 'color' => '#6B7280', 'icon' => 'heroicon-o-cpu-chip', 'description' => 'Automatically generated system tickets', 'is_active' => true, 'sort_order' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Internal IT', 'code' => 'INTERNAL', 'color' => '#6B7280', 'icon' => 'fas fa-server', 'description' => 'Task internal IT team', 'is_active' => true, 'sort_order' => 0, 'created_at' => now(), 'updated_at' => now()],
        ], ['code']);
    }
}
