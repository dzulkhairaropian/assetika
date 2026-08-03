<?php

namespace Database\Seeders;

use App\Models\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = [
            [
                'name' => 'Bhinneka Mentari Dimensi',
                'email' => 'sales@bhinneka.com',
                'contact_person' => 'Sales Team',
                'phone' => '021-29292828',
                'website' => 'https://bhinneka.com',
            ],
            [
                'name' => 'Global IT Solution',
                'email' => 'support@globalit.co.id',
                'contact_person' => 'Andi',
                'phone' => '021-55566677',
            ],
        ];

        foreach ($vendors as $vendor) {
            Vendor::updateOrCreate(
                ['name' => $vendor['name']],
                $vendor
            );
        }
    }
}
