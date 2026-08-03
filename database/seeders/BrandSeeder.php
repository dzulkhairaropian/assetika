<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            ['code' => 'APL', 'name' => 'Apple'],
            ['code' => 'DEL', 'name' => 'Dell'],
            ['code' => 'HP', 'name' => 'HP'],
            ['code' => 'LEN', 'name' => 'Lenovo'],
            ['code' => 'ASU', 'name' => 'Asus'],
            ['code' => 'LOG', 'name' => 'Logitech'],
        ];

        foreach ($brands as $brand) {
            Brand::updateOrCreate(
                ['code' => $brand['code']],
                ['name' => $brand['name']]
            );
        }
    }
}
