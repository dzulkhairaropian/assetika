<?php

namespace Database\Seeders;

use App\Models\MasterCategory;
use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    public function run(): void
    {
        $itAsset = MasterCategory::where('name', 'IT Asset')->first();

        $statuses = [
            ['name' => 'Ready to Deploy', 'color' => 'success', 'icon' => 'lucide-check-circle'],
            ['name' => 'Deployed', 'color' => 'primary', 'icon' => 'lucide-user'],
            ['name' => 'Broken', 'color' => 'danger', 'icon' => 'lucide-x-circle'],
            ['name' => 'In Repair', 'color' => 'warning', 'icon' => 'lucide-wrench'],
            ['name' => 'Archived', 'color' => 'secondary', 'icon' => 'lucide-archive'],
        ];

        foreach ($statuses as $status) {
            Status::updateOrCreate(
                ['name' => $status['name'], 'master_category_id' => $itAsset?->id],
                [
                    'color' => $status['color'],
                    'icon' => $status['icon'],
                ]
            );
        }
    }
}
