<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            ['code' => 'MGR', 'name' => 'Manager'],
            ['code' => 'STF', 'name' => 'Staff'],
            ['code' => 'LD', 'name' => 'Lead'],
            ['code' => 'DIR', 'name' => 'Director'],
        ];

        foreach ($positions as $pos) {
            Position::updateOrCreate(
                ['code' => $pos['code']],
                ['name' => $pos['name']]
            );
        }
    }
}
