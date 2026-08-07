<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('companies')->upsert([
            ['name' => 'PT. Diastika Biotekindo Tbk', 'code' => 'DB', 'description' => 'Main Head Office', 'website' => null, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'PT. UBC Medical Indonesia Tbk', 'code' => 'UBC', 'description' => 'Main Head Office', 'website' => null, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ], ['code']);
    }
}
