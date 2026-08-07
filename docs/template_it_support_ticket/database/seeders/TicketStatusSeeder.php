<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TicketStatusSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('ticket_statuses')->upsert([
            ['name' => 'New', 'code' => 'NEW', 'color' => 'info', 'icon' => 'heroicon-o-sparkles', 'description' => 'Newly created tickets', 'sort_order' => 1, 'is_active' => true, 'is_closed' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'In Progress', 'code' => 'IN-PROGRESS', 'color' => 'warning', 'icon' => 'heroicon-o-wrench', 'description' => 'Tickets being worked on', 'sort_order' => 2, 'is_active' => true, 'is_closed' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pending', 'code' => 'PENDING', 'color' => 'gray', 'icon' => 'heroicon-o-clock', 'description' => 'Waiting for information or external action', 'sort_order' => 3, 'is_active' => true, 'is_closed' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Completed', 'code' => 'COMPLETED', 'color' => 'success', 'icon' => 'heroicon-o-check-badge', 'description' => 'Successfully resolved tickets', 'sort_order' => 4, 'is_active' => true, 'is_closed' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Closed', 'code' => 'CLOSED', 'color' => 'gray', 'icon' => 'heroicon-o-lock-closed', 'description' => 'Closed and finalized tickets', 'sort_order' => 5, 'is_active' => true, 'is_closed' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cancelled', 'code' => 'CANCELLED', 'color' => 'danger', 'icon' => 'heroicon-o-x-circle', 'description' => 'Cancelled or invalid tickets', 'sort_order' => 6, 'is_active' => true, 'is_closed' => true, 'created_at' => now(), 'updated_at' => now()],
        ], ['code']);
    }
}
