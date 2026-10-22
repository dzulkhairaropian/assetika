<?php

namespace Tests\Unit;

use App\Models\Asset;
use App\Services\AssetService;
use Tests\TestCase;

class AssetScopeTest extends TestCase
{
    public function test_asset_filter_scope_applies_correctly(): void
    {
        $asset = new Asset;
        $query = $asset->newQuery()->filter(['search' => 'AST-123']);

        $this->assertStringContainsString('asset_tag', $query->toSql());
    }

    public function test_asset_filter_scope_supports_arrays_and_tabs(): void
    {
        $asset = new Asset;
        $sql = $asset->newQuery()->filter([
            'status_id' => [1, 2],
            'department_id' => [3],
            'year' => [2023, 2024],
            'aging' => ['0-1', '4+'],
            'tab' => 'ready',
        ])->toSql();

        $this->assertStringContainsString('status_id', $sql);
        $this->assertStringContainsString('department_id', $sql);
        $this->assertStringContainsString('release_date', $sql);
    }

    public function test_asset_service_instantiation(): void
    {
        $service = new AssetService;
        $this->assertInstanceOf(AssetService::class, $service);
    }
}
