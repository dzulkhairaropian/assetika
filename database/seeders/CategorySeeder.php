<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MasterCategory;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $itAsset = MasterCategory::where('name', 'IT Asset')->first();
        $consumable = MasterCategory::where('name', 'IT Consumable')->first();

        $categories = [
            ['code' => 'NB', 'name' => 'Notebook/Laptop', 'master_id' => $itAsset?->id, 'device_type' => 'laptop', 'useful_life_years' => 4],
            ['code' => 'PC', 'name' => 'Personal Computer', 'master_id' => $itAsset?->id, 'device_type' => 'desktop', 'useful_life_years' => 5],
            ['code' => 'MN', 'name' => 'Monitor', 'master_id' => $itAsset?->id, 'device_type' => 'display', 'useful_life_years' => 5],
            ['code' => 'PR', 'name' => 'Printer', 'master_id' => $itAsset?->id, 'device_type' => 'peripheral', 'useful_life_years' => 4],
            ['code' => 'MSE', 'name' => 'Mouse', 'master_id' => $consumable?->id, 'device_type' => 'peripheral', 'useful_life_years' => 2],
            ['code' => 'KBD', 'name' => 'Keyboard', 'master_id' => $consumable?->id, 'device_type' => 'peripheral', 'useful_life_years' => 2],
            ['code' => 'TON', 'name' => 'Toner', 'master_id' => $consumable?->id, 'device_type' => 'peripheral', 'useful_life_years' => 1],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['code' => $cat['code']],
                [
                    'name' => $cat['name'],
                    'master_category_id' => $cat['master_id'],
                    'device_type' => $cat['device_type'] ?? 'other',
                    'useful_life_years' => $cat['useful_life_years'] ?? 4,
                ]
            );
        }
    }
}
