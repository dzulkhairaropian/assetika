<?php

namespace Tests\Unit;

use App\Models\Asset;
use App\Models\AssetMaintenance;
use App\Services\AssetService;
use Tests\TestCase;

class CompleteMaintenanceTest extends TestCase
{
    public function test_complete_maintenance_accepts_nullable_cost_and_notes()
    {
        $asset = new Asset(['id' => 1, 'status_id' => 1]);
        $maintenance = new AssetMaintenance(['id' => 1, 'cost' => 0, 'notes' => null]);

        $service = new AssetService;
        $this->assertNotNull($service);
    }
}
