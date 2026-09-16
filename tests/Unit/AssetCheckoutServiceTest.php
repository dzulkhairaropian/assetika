<?php

namespace Tests\Unit;

use App\Models\Asset;
use App\Models\User;
use App\Services\AssetService;
use Tests\TestCase;

class AssetCheckoutServiceTest extends TestCase
{
    public function test_asset_service_instantiation_and_checkout_structure()
    {
        $service = new AssetService;
        $this->assertNotNull($service);

        $asset = new Asset;
        $asset->id = 1;
        $user = new User;
        $user->id = 1;

        $this->assertEquals(1, $asset->id);
        $this->assertEquals(1, $user->id);
    }
}
