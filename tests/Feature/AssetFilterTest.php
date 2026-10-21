<?php

use App\Models\Asset;
use App\Models\Category;
use App\Models\Company;
use App\Models\Department;
use App\Models\MasterCategory;
use App\Models\Product;
use App\Models\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('asset_filter_supports_array_of_status_and_company_ids', function () {
    $companyA = Company::create(['name' => 'Company Alpha', 'code' => 'ALP']);
    $companyB = Company::create(['name' => 'Company Beta', 'code' => 'BET']);

    $deptA = Department::create(['company_id' => $companyA->id, 'name' => 'IT Alpha', 'code' => 'ITA']);
    $deptB = Department::create(['company_id' => $companyB->id, 'name' => 'IT Beta', 'code' => 'ITB']);

    $statusReady = Status::create(['name' => 'Ready to Deploy', 'color' => 'success']);
    $statusDeployed = Status::create(['name' => 'Deployed', 'color' => 'primary']);
    $statusBroken = Status::create(['name' => 'Broken', 'color' => 'danger']);

    $masterCat = MasterCategory::firstOrCreate(['name' => 'IT Asset', 'code' => 'IT']);
    $cat = Category::firstOrCreate(['name' => 'Laptop', 'code' => 'LP', 'master_category_id' => $masterCat->id]);
    $prod = Product::create(['name' => 'ThinkPad T14', 'category_id' => $cat->id, 'master_category_id' => $masterCat->id]);

    $asset1 = Asset::create([
        'asset_tag' => 'ALP/LP/260101/001',
        'product_id' => $prod->id,
        'department_id' => $deptA->id,
        'status_id' => $statusReady->id,
        'release_date' => '2026-01-01',
    ]);

    $asset2 = Asset::create([
        'asset_tag' => 'ALP/LP/260101/002',
        'product_id' => $prod->id,
        'department_id' => $deptA->id,
        'status_id' => $statusDeployed->id,
        'release_date' => '2025-01-01',
    ]);

    $asset3 = Asset::create([
        'asset_tag' => 'BET/LP/260101/003',
        'product_id' => $prod->id,
        'department_id' => $deptB->id,
        'status_id' => $statusBroken->id,
        'release_date' => '2020-01-01',
    ]);

    // Test multi-status array filter
    $query = Asset::query()->filter([
        'status_id' => [$statusReady->id, $statusDeployed->id],
    ]);
    $results = $query->get();
    $this->assertTrue($results->contains($asset1));
    $this->assertTrue($results->contains($asset2));
    $this->assertFalse($results->contains($asset3));

    // Test multi-company array filter
    $companyQuery = Asset::query()->filter([
        'company_id' => [$companyA->id],
    ]);
    $compResults = $companyQuery->get();
    $this->assertTrue($compResults->contains($asset1));
    $this->assertTrue($compResults->contains($asset2));
    $this->assertFalse($compResults->contains($asset3));
});

test('asset_index_supports_multi_year_and_preserves_tab', function () {
    $company = Company::create(['name' => 'Test Corp', 'code' => 'TC']);
    $user = User::factory()->create(['company_id' => $company->id]);
    $this->actingAs($user);

    $dept = Department::create(['company_id' => $company->id, 'name' => 'IT Dept', 'code' => 'IT']);
    $status = Status::create(['name' => 'Ready to Deploy', 'color' => 'success']);
    $masterCat = MasterCategory::firstOrCreate(['name' => 'IT Asset', 'code' => 'IT']);
    $cat = Category::firstOrCreate(['name' => 'Laptop', 'code' => 'LP', 'master_category_id' => $masterCat->id]);
    $prod = Product::create(['name' => 'Latitude 5420', 'category_id' => $cat->id, 'master_category_id' => $masterCat->id]);

    $asset2025 = Asset::create([
        'asset_tag' => 'TC/LP/250101/001',
        'product_id' => $prod->id,
        'department_id' => $dept->id,
        'status_id' => $status->id,
        'release_date' => '2025-05-01',
    ]);

    $asset2026 = Asset::create([
        'asset_tag' => 'TC/LP/260101/002',
        'product_id' => $prod->id,
        'department_id' => $dept->id,
        'status_id' => $status->id,
        'release_date' => '2026-02-01',
    ]);

    $response = $this->get(route('assets.index', [
        'year' => ['2025', '2026'],
        'tab' => 'ready',
    ]));

    $response->assertOk();
    $response->assertSee('TC/LP/250101/001');
    $response->assertSee('TC/LP/260101/002');
    $response->assertSee('name="tab" value="ready"', false);
});
