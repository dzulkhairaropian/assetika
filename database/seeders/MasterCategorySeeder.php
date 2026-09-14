<?php

namespace Database\Seeders;

use App\Models\MasterCategory;
use Illuminate\Database\Seeder;

class MasterCategorySeeder extends Seeder
{
    public function run(): void
    {
        $masters = [
            ['name' => 'IT Asset'],
            ['name' => 'IT Component'],
            ['name' => 'IT Consumable'],
            ['name' => 'IT Request'],
            ['name' => 'GA Asset'],
            ['name' => 'GA Consumable'],
            ['name' => 'GA Request'],
        ];

        foreach ($masters as $master) {
            MasterCategory::updateOrCreate(['name' => $master['name']], []);
        }
    }
}
