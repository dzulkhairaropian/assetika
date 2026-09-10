<?php

namespace Database\Seeders;

use App\Models\MasterCategory;
use App\Models\Status;
use Illuminate\Database\Seeder;

class CreateDefaultTransactionStatusesSeeder extends Seeder
{
    public function run()
    {
        $statuses = [
            ['name' => 'Restock',    'master_category' => 'Consumable', 'color' => 'success', 'icon' => 'download'],
            ['name' => 'Checkout',   'master_category' => 'Consumable', 'color' => 'warning', 'icon' => 'shopping-cart'],
            ['name' => 'Checked Out', 'master_category' => 'IT Asset',   'color' => 'primary', 'icon' => 'log-out'],
            ['name' => 'Returned',   'master_category' => 'IT Asset',   'color' => 'success', 'icon' => 'log-in'],
        ];

        foreach ($statuses as $status) {
            $masterCategory = MasterCategory::where('name', $status['master_category'])->first();

            Status::firstOrCreate(
                ['name' => $status['name']],
                [
                    'master_category_id' => $masterCategory->id ?? null,
                    'color' => $status['color'],
                    'icon' => $status['icon'],
                ]
            );
        }
    }
}
