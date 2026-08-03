<?php

namespace Database\Seeders;

use App\Models\MasterCategory;
use App\Models\Status;
use Illuminate\Database\Seeder;

class CreateDefaultTransactionStatusesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->createStatus('Restock', 'Consumable', '#198754', 'arrow-down-circle'); // Success
        $this->createStatus('Checkout', 'Consumable', '#0d6efd', 'arrow-up-circle'); // Primary
        $this->createStatus('Checked Out', 'IT Asset', '#0d6efd', 'arrow-up-circle'); // Primary
        $this->createStatus('Returned', 'IT Asset', '#198754', 'arrow-down-circle'); // Success
    }

    private function createStatus(string $name, ?string $masterCategoryName, string $color, string $icon)
    {
        $masterCategory = null;
        if ($masterCategoryName) {
            $masterCategory = MasterCategory::where('name', $masterCategoryName)->first();
        }

        Status::firstOrCreate(
            ['name' => $name],
            [
                'master_category_id' => $masterCategory->id ?? null,
                'color' => $color,
                'icon' => $icon,
            ]
        );
    }
}
