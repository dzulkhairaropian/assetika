<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('code', 'DB')->first();

        $locations = [
            ['name' => 'Head Office - Jakarta'],
            ['name' => 'Branch Office - Surabaya'],
            ['name' => 'Warehouse - Bekasi'],
        ];

        foreach ($locations as $loc) {
            Location::updateOrCreate(
                ['name' => $loc['name']],
                ['company_id' => $company?->id]
            );
        }
    }
}
