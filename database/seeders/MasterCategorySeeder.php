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
            ['name' => 'Consumable'],
            ['name' => 'GA Asset'],
        ];

        foreach ($masters as $master) {
            MasterCategory::updateOrCreate(['name' => $master['name']], []);
        }
    }
}
