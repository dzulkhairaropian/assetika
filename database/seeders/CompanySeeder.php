<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        Company::updateOrCreate(
            ['code' => 'DB'],
            [
                'name' => 'Duta Bangsa',
                'email' => 'info@dutabangsa.co.id',
                'phone' => '021-12345678',
                'website' => 'https://dutabangsa.co.id',
                'address' => 'Jakarta, Indonesia',
            ]
        );
    }
}
