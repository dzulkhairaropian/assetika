<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\OperatingSystem;
use Illuminate\Database\Seeder;

class OperatingSystemSeeder extends Seeder
{
    public function run(): void
    {
        $microsoft = Brand::where('name', 'Microsoft')->first() ?? Brand::updateOrCreate(['code' => 'MS'], ['name' => 'Microsoft']);
        $apple = Brand::where('name', 'Apple')->first();

        $oss = [
            ['name' => 'Windows 10 Pro', 'brand_id' => $microsoft->id],
            ['name' => 'Windows 11 Pro', 'brand_id' => $microsoft->id],
            ['name' => 'macOS Sonoma', 'brand_id' => $apple?->id],
            ['name' => 'Ubuntu 22.04 LTS', 'brand_id' => null],
        ];

        foreach ($oss as $os) {
            OperatingSystem::updateOrCreate(
                ['name' => $os['name']],
                ['brand_id' => $os['brand_id']]
            );
        }
    }
}
