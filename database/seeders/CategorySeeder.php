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
        $consumable = MasterCategory::where('name', 'Consumable')->first();

        $categories = [
            ['code' => 'NB', 'name' => 'Notebook/Laptop', 'master_id' => $itAsset?->id],
            ['code' => 'PC', 'name' => 'Personal Computer', 'master_id' => $itAsset?->id],
            ['code' => 'MN', 'name' => 'Monitor', 'master_id' => $itAsset?->id],
            ['code' => 'PR', 'name' => 'Printer', 'master_id' => $itAsset?->id],
            ['code' => 'MSE', 'name' => 'Mouse', 'master_id' => $consumable?->id],
            ['code' => 'KBD', 'name' => 'Keyboard', 'master_id' => $consumable?->id],
            ['code' => 'TON', 'name' => 'Toner', 'master_id' => $consumable?->id],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['code' => $cat['code']],
                [
                    'name' => $cat['name'],
                    'master_category_id' => $cat['master_id']
                ]
            );
        }
    }
}
