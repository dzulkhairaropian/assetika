<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categories')->upsert([
            ['name' => 'Hardware', 'code' => 'HW', 'parent_id' => null, 'color' => 'gray', 'icon' => 'heroicon-o-computer-desktop', 'description' => 'Hardware related issues', 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Software', 'code' => 'SW', 'parent_id' => null, 'color' => 'gray', 'icon' => 'heroicon-o-window', 'description' => 'Software related issues', 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Network', 'code' => 'NET', 'parent_id' => null, 'color' => 'gray', 'icon' => 'heroicon-o-globe-alt', 'description' => 'Network & connectivity issues', 'sort_order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Access & Account', 'code' => 'ACC', 'parent_id' => null, 'color' => 'gray', 'icon' => 'heroicon-o-key', 'description' => 'User access & account management', 'sort_order' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Email', 'code' => 'EMAIL', 'parent_id' => null, 'color' => 'gray', 'icon' => 'heroicon-o-envelope', 'description' => 'Email system & client issues', 'sort_order' => 5, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Infrastructure', 'code' => 'INFRA', 'parent_id' => null, 'color' => 'gray', 'icon' => 'heroicon-o-server', 'description' => 'Server & infrastructure issues', 'sort_order' => 6, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Security', 'code' => 'SEC', 'parent_id' => null, 'color' => 'gray', 'icon' => 'heroicon-o-shield-check', 'description' => 'Security & antivirus issues', 'sort_order' => 7, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Meeting', 'code' => 'MTG', 'parent_id' => null, 'color' => 'gray', 'icon' => 'heroicon-o-user-group', 'description' => 'Meeting-related IT issues', 'sort_order' => 8, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Other', 'code' => 'OTH', 'parent_id' => null, 'color' => 'gray', 'icon' => 'heroicon-o-question-mark-circle', 'description' => 'Other IT issues', 'sort_order' => 9, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ], ['code']);
    }
}
