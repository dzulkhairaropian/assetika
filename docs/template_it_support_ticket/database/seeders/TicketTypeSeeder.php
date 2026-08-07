<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TicketTypeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('ticket_types')->upsert([
            ['name' => 'Incident', 'code' => 'INC', 'color' => 'danger', 'icon' => 'heroicon-o-exclamation-triangle', 'description' => 'Unplanned interruption or reduction in quality of IT service', 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Service Request', 'code' => 'SR', 'color' => 'info', 'icon' => 'heroicon-o-inbox-arrow-down', 'description' => 'Formal request for something to be provided', 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Task', 'code' => 'TASK', 'color' => 'warning', 'icon' => 'heroicon-o-clipboard-document-check', 'description' => 'Internal IT task or scheduled maintenance', 'sort_order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Change Request', 'code' => 'CR', 'color' => 'primary', 'icon' => 'heroicon-o-arrow-path', 'description' => 'Request for change to IT infrastructure or systems', 'sort_order' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ], ['code']);
    }
}
