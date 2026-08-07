<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LabelSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('labels')->upsert([
            ['name' => 'Support', 'slug' => 'support', 'color' => 'info', 'icon' => 'heroicon-o-chat-bubble-left-right', 'description' => 'General support requests', 'is_active' => true, 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bug', 'slug' => 'bug', 'color' => 'danger', 'icon' => 'heroicon-o-bug-ant', 'description' => 'Software bugs and errors', 'is_active' => true, 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Feature Request', 'slug' => 'feature-request', 'color' => 'warning', 'icon' => 'heroicon-o-light-bulb', 'description' => 'Requests for new features', 'is_active' => true, 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Question', 'slug' => 'question', 'color' => 'gray', 'icon' => 'heroicon-o-question-mark-circle', 'description' => 'User questions and inquiries', 'is_active' => true, 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Task', 'slug' => 'task', 'color' => 'gray', 'icon' => 'heroicon-o-check-circle', 'description' => 'General IT tasks', 'is_active' => true, 'sort_order' => 5, 'created_at' => now(), 'updated_at' => now()],
        ], ['slug']);
    }
}
