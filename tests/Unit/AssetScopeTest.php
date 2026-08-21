<?php

namespace Tests\Unit;

use App\Models\Asset;
use App\Services\AssetService;
use Tests\TestCase;

class AssetScopeTest extends TestCase
{
    public function test_asset_filter_scope_applies_correctly(): void
    {
        $asset = new Asset();
        $query = $asset->newQuery()->filter(['search' => 'AST-123']);
        
        $this->assertStringContainsString('asset_tag', $query->toSql());
    }

    public function test_asset_service_instantiation(): void
    {
        $service = new AssetService();
        $this->assertInstanceOf(AssetService::class, $service);
    }
}
