<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $companyId = DB::table('companies')->where('code', 'DB')->value('id');

        DB::table('departments')->upsert([
            ['name' => 'IT Department', 'code' => 'IT', 'company_id' => $companyId, 'description' => 'Information Technology Department', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Finance Accounting Tax & Purchasing', 'code' => 'FATP', 'company_id' => null, 'description' => 'Finance Accounting Tax & Purchasing Department', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Human Resources General Affairs', 'code' => 'HRGA', 'company_id' => null, 'description' => 'Human Resources General Affairs Department', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Supply Chain & Logistics', 'code' => 'SC', 'company_id' => null, 'description' => 'Supply Chain & Logistics Department', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Marketing', 'code' => 'MKT', 'company_id' => null, 'description' => 'Marketing Department', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sales', 'code' => 'SLS', 'company_id' => null, 'description' => 'Sales Department', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Legal', 'code' => 'LGL', 'company_id' => null, 'description' => 'Legal Department', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Management', 'code' => 'MGT', 'company_id' => null, 'description' => 'Management Department', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Technical Support', 'code' => 'TS', 'company_id' => null, 'description' => 'Technical Support Department', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ], ['code']);
    }
}
