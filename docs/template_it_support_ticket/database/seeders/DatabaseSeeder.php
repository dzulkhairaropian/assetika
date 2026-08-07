<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            CompanySeeder::class,
            DepartmentSeeder::class,
            CategorySeeder::class,
            PrioritySeeder::class,
            TicketStatusSeeder::class,
            SlaStatusSeeder::class,
            LabelSeeder::class,
            SourceSeeder::class,
            TicketTypeSeeder::class,
            DefaultUserSeeder::class,
        ]);
    }
}
