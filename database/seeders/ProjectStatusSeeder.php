<?php

namespace Database\Seeders;

use App\Models\MasterCategory;
use App\Models\Status;
use Illuminate\Database\Seeder;

class ProjectStatusSeeder extends Seeder
{
    public function run(): void
    {
        $projectMC = MasterCategory::firstOrCreate(['name' => 'Project']);
        $taskMC = MasterCategory::firstOrCreate(['name' => 'Project Task']);

        $projectStatuses = [
            ['name' => 'Planning',  'color' => 'info',    'icon' => 'clock'],
            ['name' => 'Active',    'color' => 'success', 'icon' => 'play-circle'],
            ['name' => 'On Hold',   'color' => 'warning', 'icon' => 'pause-circle'],
            ['name' => 'Completed','color' => 'primary', 'icon' => 'check-circle'],
            ['name' => 'Cancelled','color' => 'danger',  'icon' => 'x-circle'],
        ];

        foreach ($projectStatuses as $st) {
            Status::updateOrCreate(
                ['name' => $st['name'], 'master_category_id' => $projectMC->id],
                ['color' => $st['color'], 'icon' => $st['icon']]
            );
        }

        $taskStatuses = [
            ['name' => 'To Do',       'color' => 'light',   'icon' => 'circle'],
            ['name' => 'In Progress', 'color' => 'primary', 'icon' => 'loader'],
            ['name' => 'Review',      'color' => 'warning', 'icon' => 'eye'],
            ['name' => 'Done',        'color' => 'success', 'icon' => 'check-circle'],
        ];

        foreach ($taskStatuses as $st) {
            Status::updateOrCreate(
                ['name' => $st['name'], 'master_category_id' => $taskMC->id],
                ['color' => $st['color'], 'icon' => $st['icon']]
            );
        }
    }
}
