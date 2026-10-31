<?php

use App\Models\Asset;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Company;
use App\Models\Department;
use App\Models\MasterCategory;
use App\Models\Product;
use App\Models\Status;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('asset_replacement_helpers_and_scopes_identify_aged_laptops', function () {
    $company = Company::create(['name' => 'Acme Corp', 'code' => 'ACM']);
    $department = Department::create(['company_id' => $company->id, 'name' => 'Engineering', 'code' => 'ENG']);
    $masterCat = MasterCategory::firstOrCreate(['name' => 'IT Asset', 'code' => 'IT']);
    $laptopCategory = Category::create([
        'master_category_id' => $masterCat->id,
        'name' => 'Notebook/Laptop',
        'code' => 'NB',
        'device_type' => 'laptop',
        'useful_life_years' => 4,
    ]);
    $monitorCategory = Category::create([
        'master_category_id' => $masterCat->id,
        'name' => 'Display Monitor',
        'code' => 'MN',
        'device_type' => 'display',
        'useful_life_years' => 5,
    ]);

    $brand = Brand::create(['name' => 'Dell']);
    $laptopProduct = Product::create([
        'master_category_id' => $masterCat->id,
        'category_id' => $laptopCategory->id,
        'brand_id' => $brand->id,
        'name' => 'Latitude 7420',
        'model' => '7420',
    ]);
    $monitorProduct = Product::create([
        'master_category_id' => $masterCat->id,
        'category_id' => $monitorCategory->id,
        'brand_id' => $brand->id,
        'name' => 'UltraSharp 27',
        'model' => 'U2720Q',
    ]);

    $statusReady = Status::create(['master_category_id' => $masterCat->id, 'name' => 'Ready to Deploy', 'type' => 'deployable']);
    $statusBroken = Status::create(['master_category_id' => $masterCat->id, 'name' => 'Broken', 'type' => 'undeployable']);

    // Aged Laptop: released 4.5 years ago
    $agedLaptop = Asset::create([
        'product_id' => $laptopProduct->id,
        'department_id' => $department->id,
        'status_id' => $statusReady->id,
        'asset_tag' => 'ACM/NB/210101/001',
        'device_name' => 'ACME-LAPTOP-01',
        'release_date' => Carbon::now()->subYears(4)->subMonths(6),
        'purchase_cost' => 18000000,
    ]);

    // Critical Aged Laptop: released 5.5 years ago and broken
    $criticalLaptop = Asset::create([
        'product_id' => $laptopProduct->id,
        'department_id' => $department->id,
        'status_id' => $statusBroken->id,
        'asset_tag' => 'ACM/NB/200101/002',
        'device_name' => 'ACME-LAPTOP-02',
        'release_date' => Carbon::now()->subYears(5)->subMonths(6),
        'purchase_cost' => 22000000,
    ]);

    // Fresh Laptop: released 1 year ago
    $freshLaptop = Asset::create([
        'product_id' => $laptopProduct->id,
        'department_id' => $department->id,
        'status_id' => $statusReady->id,
        'asset_tag' => 'ACM/NB/250101/003',
        'device_name' => 'ACME-LAPTOP-03',
        'release_date' => Carbon::now()->subYear(),
        'purchase_cost' => 20000000,
    ]);

    // Aged Monitor: released 5.5 years ago (threshold is 5 years)
    $agedMonitor = Asset::create([
        'product_id' => $monitorProduct->id,
        'department_id' => $department->id,
        'status_id' => $statusReady->id,
        'asset_tag' => 'ACM/MN/210101/004',
        'device_name' => 'ACME-MONITOR-01',
        'release_date' => Carbon::now()->subYears(5)->subMonths(6),
        'purchase_cost' => 6000000,
    ]);

    // Verify model helpers
    expect($agedLaptop->isDueForReplacement())->toBeTrue();
    expect($agedLaptop->replacement_priority)->toBe('High');
    expect($criticalLaptop->isDueForReplacement())->toBeTrue();
    expect($criticalLaptop->replacement_priority)->toBe('Critical');
    expect($freshLaptop->isDueForReplacement())->toBeFalse();

    // Verify scope with laptopOnly = true
    $laptopsDue = Asset::dueForReplacement(true)->get();
    expect($laptopsDue->contains($agedLaptop))->toBeTrue();
    expect($laptopsDue->contains($criticalLaptop))->toBeTrue();
    expect($laptopsDue->contains($freshLaptop))->toBeFalse();
    expect($laptopsDue->contains($agedMonitor))->toBeFalse();

    // Verify scope with laptopOnly = false
    $allDue = Asset::dueForReplacement(false)->get();
    expect($allDue->contains($agedMonitor))->toBeTrue();
});

test('asset_replacements_planner_page_loads_with_kpis_and_lists_aged_hardware', function () {
    $company = Company::create(['name' => 'Acme Corp', 'code' => 'ACM']);
    $user = User::factory()->create(['company_id' => $company->id]);
    Role::firstOrCreate(['name' => 'it_admin']);
    $user->assignRole('it_admin');
    $this->actingAs($user);

    $department = Department::create(['company_id' => $company->id, 'name' => 'Engineering', 'code' => 'ENG']);
    $masterCat = MasterCategory::firstOrCreate(['name' => 'IT Asset', 'code' => 'IT']);
    $laptopCategory = Category::create(['master_category_id' => $masterCat->id, 'name' => 'Notebook/Laptop', 'code' => 'NB']);
    $brand = Brand::create(['name' => 'Lenovo']);
    $laptopProduct = Product::create([
        'master_category_id' => $masterCat->id,
        'category_id' => $laptopCategory->id,
        'brand_id' => $brand->id,
        'name' => 'ThinkPad X1',
        'model' => 'X1 Carbon',
    ]);
    $statusReady = Status::create(['master_category_id' => $masterCat->id, 'name' => 'Ready to Deploy', 'type' => 'deployable']);

    $agedAsset = Asset::create([
        'product_id' => $laptopProduct->id,
        'department_id' => $department->id,
        'status_id' => $statusReady->id,
        'asset_tag' => 'ACM/NB/210101/099',
        'device_name' => 'THINKPAD-REPLACE-01',
        'release_date' => Carbon::now()->subYears(4)->subMonths(8),
        'purchase_cost' => 25000000,
    ]);

    $response = $this->get(route('assets.replacements'));
    $response->assertOk();
    $response->assertSee('Hardware Replacement Planner');
    $response->assertSee('ACM/NB/210101/099');
    $response->assertSee('THINKPAD-REPLACE-01');
    $response->assertSee('Due for Replacement');
    $response->assertSee('Export Proposal (Excel)');

    // Test export route
    $exportResponse = $this->get(route('assets.replacements.export', ['type' => 'laptop']));
    $exportResponse->assertOk();
});

test('asset_index_and_show_views_render_replacement_due_badges', function () {
    $company = Company::create(['name' => 'Acme Corp', 'code' => 'ACM']);
    $user = User::factory()->create(['company_id' => $company->id]);
    Role::firstOrCreate(['name' => 'super_admin']);
    $user->assignRole('super_admin');
    $this->actingAs($user);

    $department = Department::create(['company_id' => $company->id, 'name' => 'Engineering', 'code' => 'ENG']);
    $masterCat = MasterCategory::firstOrCreate(['name' => 'IT Asset', 'code' => 'IT']);
    $laptopCategory = Category::create(['master_category_id' => $masterCat->id, 'name' => 'Notebook/Laptop', 'code' => 'NB']);
    $brand = Brand::create(['name' => 'Apple']);
    $laptopProduct = Product::create([
        'master_category_id' => $masterCat->id,
        'category_id' => $laptopCategory->id,
        'brand_id' => $brand->id,
        'name' => 'MacBook Pro 14',
        'model' => 'M1 Pro',
    ]);
    $status = Status::create(['master_category_id' => $masterCat->id, 'name' => 'Deployed', 'type' => 'deployable']);

    $agedAsset = Asset::create([
        'product_id' => $laptopProduct->id,
        'department_id' => $department->id,
        'status_id' => $status->id,
        'asset_tag' => 'ACM/NB/210101/088',
        'device_name' => 'MACBOOK-AGED',
        'release_date' => Carbon::now()->subYears(4)->subMonths(3),
        'purchase_cost' => 30000000,
    ]);

    $indexResponse = $this->get(route('assets.index'));
    $indexResponse->assertOk();
    $indexResponse->assertSee('Replace');

    $showResponse = $this->get(route('assets.show', $agedAsset));
    $showResponse->assertOk();
    $showResponse->assertSee('Replacement Due');
    $showResponse->assertSee('View Planner');
});

test('category_crud_supports_useful_life_and_device_type', function () {
    $company = Company::create(['name' => 'Acme Corp', 'code' => 'ACM']);
    $user = User::factory()->create(['company_id' => $company->id]);
    Role::firstOrCreate(['name' => 'super_admin']);
    $user->assignRole('super_admin');
    $this->actingAs($user);

    $masterCat = MasterCategory::firstOrCreate(['name' => 'IT Asset', 'code' => 'IT']);

    $response = $this->post(route('categories.store'), [
        'name' => 'MacBook Fleet',
        'code' => 'MBP',
        'master_category_id' => $masterCat->id,
        'device_type' => 'laptop',
        'useful_life_years' => 5,
        'description' => 'High performance creative laptops',
    ]);

    $response->assertRedirect(route('categories.index'));
    $this->assertDatabaseHas('categories', [
        'code' => 'MBP',
        'device_type' => 'laptop',
        'useful_life_years' => 5,
    ]);
});
