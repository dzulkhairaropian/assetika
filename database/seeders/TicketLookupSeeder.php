<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TicketLookupSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('priorities')->upsert([
            ['name' => 'Critical', 'code' => 'CRITICAL', 'color' => 'danger', 'sort_order' => 1, 'is_active' => 1, 'response_time_minutes' => 15, 'resolution_time_hours' => 4],
            ['name' => 'High', 'code' => 'HIGH', 'color' => 'warning', 'sort_order' => 2, 'is_active' => 1, 'response_time_minutes' => 60, 'resolution_time_hours' => 8],
            ['name' => 'Medium', 'code' => 'MEDIUM', 'color' => 'info', 'sort_order' => 3, 'is_active' => 1, 'response_time_minutes' => 240, 'resolution_time_hours' => 24],
            ['name' => 'Low', 'code' => 'LOW', 'color' => 'success', 'sort_order' => 4, 'is_active' => 1, 'response_time_minutes' => 480, 'resolution_time_hours' => 72],
        ], ['code'], ['name', 'color', 'sort_order', 'is_active', 'response_time_minutes', 'resolution_time_hours']);

        DB::table('ticket_statuses')->upsert([
            ['name' => 'New', 'code' => 'NEW', 'color' => 'info', 'sort_order' => 1, 'is_active' => 1, 'is_closed' => 0],
            ['name' => 'In Progress', 'code' => 'IN-PROGRESS', 'color' => 'warning', 'sort_order' => 2, 'is_active' => 1, 'is_closed' => 0],
            ['name' => 'Pending', 'code' => 'PENDING', 'color' => 'gray', 'sort_order' => 3, 'is_active' => 1, 'is_closed' => 0],
            ['name' => 'Resolved', 'code' => 'RESOLVED', 'color' => 'success', 'sort_order' => 4, 'is_active' => 1, 'is_closed' => 1],
            ['name' => 'Closed', 'code' => 'CLOSED', 'color' => 'success', 'sort_order' => 5, 'is_active' => 1, 'is_closed' => 1],
        ], ['code'], ['name', 'color', 'sort_order', 'is_active', 'is_closed']);

        DB::table('status_slas')->upsert([
            ['name' => 'Fulfilled', 'code' => 'FULFILLED', 'color' => 'success', 'sort_order' => 1, 'is_active' => 1],
            ['name' => 'Warning', 'code' => 'WARNING', 'color' => 'warning', 'sort_order' => 2, 'is_active' => 1],
            ['name' => 'Breached', 'code' => 'BREACHED', 'color' => 'danger', 'sort_order' => 3, 'is_active' => 1],
            ['name' => 'Paused', 'code' => 'PAUSED', 'color' => 'gray', 'sort_order' => 4, 'is_active' => 1],
        ], ['code'], ['name', 'color', 'sort_order', 'is_active']);

        DB::table('ticket_types')->upsert([
            ['name' => 'Incident', 'code' => 'INCIDENT', 'color' => 'danger', 'sort_order' => 1, 'is_active' => 1],
            ['name' => 'Service Request', 'code' => 'SERVICE_REQUEST', 'color' => 'info', 'sort_order' => 2, 'is_active' => 1],
            ['name' => 'Change Request', 'code' => 'CHANGE', 'color' => 'warning', 'sort_order' => 3, 'is_active' => 1],
            ['name' => 'Problem', 'code' => 'PROBLEM', 'color' => 'gray', 'sort_order' => 4, 'is_active' => 1],
            ['name' => 'Maintenance', 'code' => 'MTN', 'color' => 'warning', 'sort_order' => 5, 'is_active' => 1],
        ], ['code'], ['name', 'color', 'sort_order', 'is_active']);

        DB::table('sources')->upsert([
            ['name' => 'Email', 'code' => 'EMAIL', 'color' => '#6B7280', 'sort_order' => 1, 'is_active' => 1],
            ['name' => 'Phone', 'code' => 'PHONE', 'color' => '#6B7280', 'sort_order' => 2, 'is_active' => 1],
            ['name' => 'Walk-in', 'code' => 'WALKIN', 'color' => '#6B7280', 'sort_order' => 3, 'is_active' => 1],
            ['name' => 'Portal', 'code' => 'PORTAL', 'color' => '#6B7280', 'sort_order' => 4, 'is_active' => 1],
        ], ['code'], ['name', 'color', 'sort_order', 'is_active']);

        DB::table('labels')->upsert([
            ['name' => 'Hardware', 'slug' => 'hardware', 'color' => '#EF4444', 'sort_order' => 1, 'is_active' => 1],
            ['name' => 'Software', 'slug' => 'software', 'color' => '#3B82F6', 'sort_order' => 2, 'is_active' => 1],
            ['name' => 'Network', 'slug' => 'network', 'color' => '#F59E0B', 'sort_order' => 3, 'is_active' => 1],
            ['name' => 'Access', 'slug' => 'access', 'color' => '#10B981', 'sort_order' => 4, 'is_active' => 1],
        ], ['slug'], ['name', 'color', 'sort_order', 'is_active']);

        // Seed Ticket Categories & Subcategories
        $categoriesData = [
            'Hardware' => ['Laptop / Notebook', 'Desktop PC', 'Printer / Scanner', 'Monitor / Display', 'Peripherals (Mouse/Keyboard/Dock)'],
            'Software' => ['Operating System', 'Office / Productivity', 'ERP / CRM Software', 'Specialized Design/Dev Software', 'Antivirus / Security'],
            'Network & Internet' => ['Wi-Fi / Wireless Connection', 'LAN / Ethernet Cable', 'VPN Access', 'Internet Slow/Down', 'Firewall / Port Request'],
            'Account & Access' => ['Active Directory / Domain', 'Email / Mailbox', 'Password Reset', 'Access Right / Permission', '2FA / Multi-Factor Auth'],
        ];

        foreach ($categoriesData as $catName => $subs) {
            $catId = DB::table('ticket_categories')->insertGetId([
                'name' => $catName,
                'code' => strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $catName), 0, 8)),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($subs as $subName) {
                DB::table('ticket_subcategories')->insert([
                    'ticket_category_id' => $catId,
                    'name' => $subName,
                    'code' => strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $subName), 0, 8)),
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
