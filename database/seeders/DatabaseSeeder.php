<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CompanySeeder::class,
            DepartmentSeeder::class,
            PositionSeeder::class,
            LocationSeeder::class,
            BrandSeeder::class,
            MasterCategorySeeder::class,
            CategorySeeder::class,
            StatusSeeder::class,
            CreateDefaultTransactionStatusesSeeder::class,
            VendorSeeder::class,
            OperatingSystemSeeder::class,
            ProductSeeder::class,
            PermissionSeeder::class,
            UserSeeder::class,
        ]);
    }
}
