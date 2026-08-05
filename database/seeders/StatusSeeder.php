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
        $itConsumable = MasterCategory::where('name', 'IT Consumable')->first();
        $itRequest = MasterCategory::where('name', 'IT Request')->first();
        $gaAsset = MasterCategory::where('name', 'GA Asset')->first();
        $gaConsumable = MasterCategory::where('name', 'GA Consumable')->first();
        $gaRequest = MasterCategory::where('name', 'GA Request')->first();

        $statuses = [
            // IT Asset
            ['name' => 'Draft',           'master_category_id' => $itAsset?->id,      'color' => 'light',     'icon' => 'file-text'],
            ['name' => 'Ready to Deploy', 'master_category_id' => $itAsset?->id,      'color' => 'success',   'icon' => 'check-circle'],
            ['name' => 'Deployed',        'master_category_id' => $itAsset?->id,      'color' => 'primary',   'icon' => 'monitor'],
            ['name' => 'Borrowed',        'master_category_id' => $itAsset?->id,      'color' => 'info',      'icon' => 'refresh-cw'],
            ['name' => 'Service',         'master_category_id' => $itAsset?->id,      'color' => 'warning',   'icon' => 'tool'],
            ['name' => 'Broken',          'master_category_id' => $itAsset?->id,      'color' => 'danger',    'icon' => 'alert-triangle'],
            ['name' => 'Retired',         'master_category_id' => $itAsset?->id,      'color' => 'secondary', 'icon' => 'trash-2'],

            // IT Consumable
            ['name' => 'In Stock',        'master_category_id' => $itConsumable?->id, 'color' => 'success',   'icon' => 'check-circle'],
            ['name' => 'Low Stock',       'master_category_id' => $itConsumable?->id, 'color' => 'warning',   'icon' => 'alert-circle'],
            ['name' => 'Out of Stock',    'master_category_id' => $itConsumable?->id, 'color' => 'danger',    'icon' => 'x-circle'],

            // IT Request
            ['name' => 'Pending',         'master_category_id' => $itRequest?->id,    'color' => 'warning',   'icon' => 'clock'],
            ['name' => 'Fulfilled',       'master_category_id' => $itRequest?->id,    'color' => 'success',   'icon' => 'check'],
            ['name' => 'Declined',        'master_category_id' => $itRequest?->id,    'color' => 'danger',    'icon' => 'x'],

            // GA Asset
            ['name' => 'Available',       'master_category_id' => $gaAsset?->id,      'color' => 'success',   'icon' => 'check-circle'],
            ['name' => 'Assigned',        'master_category_id' => $gaAsset?->id,      'color' => 'primary',   'icon' => 'user-check'],
            ['name' => 'Reserved',        'master_category_id' => $gaAsset?->id,      'color' => 'info',      'icon' => 'bookmark'],
            ['name' => 'Maintenance',     'master_category_id' => $gaAsset?->id,      'color' => 'warning',   'icon' => 'tool'],
            ['name' => 'Broken',          'master_category_id' => $gaAsset?->id,      'color' => 'danger',    'icon' => 'alert-triangle'],
            ['name' => 'Retired',         'master_category_id' => $gaAsset?->id,      'color' => 'secondary', 'icon' => 'trash-2'],
            ['name' => 'Lost',            'master_category_id' => $gaAsset?->id,      'color' => 'dark',      'icon' => 'help-circle'],

            // GA Consumable
            ['name' => 'In Stock',        'master_category_id' => $gaConsumable?->id, 'color' => 'success',   'icon' => 'check-circle'],
            ['name' => 'Low Stock',       'master_category_id' => $gaConsumable?->id, 'color' => 'warning',   'icon' => 'alert-circle'],
            ['name' => 'Out of Stock',    'master_category_id' => $gaConsumable?->id, 'color' => 'danger',    'icon' => 'x-circle'],

            // GA Request
            ['name' => 'Pending',         'master_category_id' => $gaRequest?->id,    'color' => 'warning',   'icon' => 'clock'],
            ['name' => 'Approved',        'master_category_id' => $gaRequest?->id,    'color' => 'info',      'icon' => 'thumbs-up'],
            ['name' => 'In Progress',     'master_category_id' => $gaRequest?->id,    'color' => 'primary',   'icon' => 'loader'],
            ['name' => 'Completed',       'master_category_id' => $gaRequest?->id,    'color' => 'success',   'icon' => 'check'],
            ['name' => 'Rejected',        'master_category_id' => $gaRequest?->id,    'color' => 'danger',    'icon' => 'x'],
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
