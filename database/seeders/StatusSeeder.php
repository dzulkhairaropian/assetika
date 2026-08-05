<?php

namespace Database\Seeders;

use App\Models\MasterCategory;
use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    public function run(): void
    {
        $itAsset = MasterCategory::where('name', 'IT Asset')->first();
        $consumable = MasterCategory::where('name', 'Consumable')->first();
        $request = MasterCategory::where('name', 'Request')->first();

        $statuses = [
            // IT Asset statuses
            ['name' => 'Ready to Deploy', 'master_category_id' => $itAsset?->id,  'color' => 'success',   'icon' => 'check-circle'],
            ['name' => 'Deployed',        'master_category_id' => $itAsset?->id,  'color' => 'primary',   'icon' => 'users'],
            ['name' => 'Borrowed',        'master_category_id' => $itAsset?->id,  'color' => 'info',      'icon' => 'refresh-cw'],
            ['name' => 'Service',         'master_category_id' => $itAsset?->id,  'color' => 'info',      'icon' => 'settings'],
            ['name' => 'In Repair',       'master_category_id' => $itAsset?->id,  'color' => 'warning',   'icon' => 'tool'],
            ['name' => 'Broken',          'master_category_id' => $itAsset?->id,  'color' => 'danger',    'icon' => 'alert-triangle'],
            ['name' => 'Retired',         'master_category_id' => $itAsset?->id,  'color' => 'secondary', 'icon' => 'trash-2'],
            ['name' => 'Archived',        'master_category_id' => $itAsset?->id,  'color' => 'dark',      'icon' => 'archive'],
            ['name' => 'Draft',           'master_category_id' => $itAsset?->id,  'color' => 'light',     'icon' => 'file-text'],
            ['name' => 'Checked Out',     'master_category_id' => $itAsset?->id,  'color' => 'primary',   'icon' => 'log-out'],
            ['name' => 'Returned',        'master_category_id' => $itAsset?->id,  'color' => 'success',   'icon' => 'log-in'],

            // Consumable statuses
            ['name' => 'In Stock',        'master_category_id' => $consumable?->id,'color' => 'success',   'icon' => 'check-circle'],
            ['name' => 'Low Stock',       'master_category_id' => $consumable?->id,'color' => 'warning',   'icon' => 'alert-circle'],
            ['name' => 'Out of Stock',    'master_category_id' => $consumable?->id,'color' => 'danger',    'icon' => 'x-circle'],
            ['name' => 'Restock',         'master_category_id' => $consumable?->id,'color' => 'success',   'icon' => 'download'],
            ['name' => 'Checkout',        'master_category_id' => $consumable?->id,'color' => 'warning',   'icon' => 'shopping-cart'],

            // Request statuses
            ['name' => 'Pending',         'master_category_id' => $request?->id,   'color' => 'warning',   'icon' => 'clock'],
            ['name' => 'Fulfilled',       'master_category_id' => $request?->id,   'color' => 'success',   'icon' => 'check'],
            ['name' => 'Declined',        'master_category_id' => $request?->id,   'color' => 'danger',    'icon' => 'x'],
        ];

        foreach ($statuses as $status) {
            Status::updateOrCreate(
                ['name' => $status['name'], 'master_category_id' => $status['master_category_id']],
                [
                    'color' => $status['color'],
                    'icon' => $status['icon'],
                ]
            );
        }
    }
}
