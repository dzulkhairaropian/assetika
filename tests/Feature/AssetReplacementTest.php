<?php

use App\Models\Asset;
use App\Models\AssetMaintenance;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Company;
use App\Models\Department;
use App\Models\Location;
use App\Models\MasterCategory;
use App\Models\Product;
use App\Models\Status;
use App\Models\User;
use App\Services\AssetService;
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

test('young_broken_or_frequently_repaired_user_assets_are_eligible_and_can_be_swapped', function () {
    $company = Company::create(['name' => 'Acme Corp', 'code' => 'ACM']);
    $department = Department::create(['company_id' => $company->id, 'name' => 'Engineering', 'code' => 'ENG']);
    $location = Location::create(['company_id' => $company->id, 'name' => 'HQ Floor 2']);

    $admin = User::factory()->create(['company_id' => $company->id, 'department_id' => $department->id, 'location_id' => $location->id]);
    $userA = User::factory()->create(['name' => 'User A', 'company_id' => $company->id, 'department_id' => $department->id, 'location_id' => $location->id]);
    Role::firstOrCreate(['name' => 'super_admin']);
    $admin->assignRole('super_admin');
    $this->actingAs($admin);

    $masterCat = MasterCategory::firstOrCreate(['name' => 'IT Asset', 'code' => 'IT']);
    $laptopCategory = Category::create([
        'master_category_id' => $masterCat->id,
        'name' => 'Notebook/Laptop',
        'code' => 'NB',
        'device_type' => 'laptop',
        'useful_life_years' => 4,
    ]);
    $brand = Brand::create(['name' => 'Dell']);
    $laptopProduct = Product::create([
        'master_category_id' => $masterCat->id,
        'category_id' => $laptopCategory->id,
        'brand_id' => $brand->id,
        'name' => 'Vostro 3400',
        'model' => '3400',
    ]);

    $statusReady = Status::create(['master_category_id' => $masterCat->id, 'name' => 'Ready to Deploy', 'type' => 'deployable']);
    $statusDeployed = Status::create(['master_category_id' => $masterCat->id, 'name' => 'Deployed', 'type' => 'deployable']);
    $statusBroken = Status::create(['master_category_id' => $masterCat->id, 'name' => 'Broken', 'type' => 'undeployable']);

    // User A's 3-year-old laptop that is Broken (under 4y threshold!)
    $userABrokenLaptop = Asset::create([
        'product_id' => $laptopProduct->id,
        'department_id' => $department->id,
        'location_id' => $location->id,
        'status_id' => $statusReady->id,
        'asset_tag' => 'ACM/NB/230101/001',
        'device_name' => 'USERA-LAPTOP',
        'release_date' => Carbon::now()->subYears(3),
        'purchase_cost' => 12000000,
    ]);

    app(AssetService::class)->processCheckout($userABrokenLaptop, $userA, [
        'location_id' => $location->id,
        'department_id' => $department->id,
        'checkout_date' => now()->subYears(2),
    ]);
    $userABrokenLaptop->update(['status_id' => $statusBroken->id]);
    $userABrokenLaptop->refresh();

    // Frequent repairs asset (2.5y old, Deployed, 2 maintenances)
    $frequentRepairLaptop = Asset::create([
        'product_id' => $laptopProduct->id,
        'department_id' => $department->id,
        'location_id' => $location->id,
        'status_id' => $statusDeployed->id,
        'asset_tag' => 'ACM/NB/240101/002',
        'device_name' => 'FREQUENT-REPAIR',
        'release_date' => Carbon::now()->subYears(2)->subMonths(6),
        'purchase_cost' => 10000000,
    ]);
    AssetMaintenance::create([
        'asset_id' => $frequentRepairLaptop->id,
        'service_provider' => 'Dell Center',
        'start_date' => now()->subMonths(4),
        'completion_date' => now()->subMonths(3),
        'cost' => 3000000,
    ]);
    AssetMaintenance::create([
        'asset_id' => $frequentRepairLaptop->id,
        'service_provider' => 'Dell Center',
        'start_date' => now()->subMonths(1),
        'completion_date' => now()->subDays(20),
        'cost' => 2500000,
    ]);

    // Spare Ready to Deploy unit (1y old, with condition note)
    $spareLaptop = Asset::create([
        'product_id' => $laptopProduct->id,
        'department_id' => $department->id,
        'location_id' => $location->id,
        'status_id' => $statusReady->id,
        'asset_tag' => 'ACM/NB/250101/003',
        'device_name' => 'SPARE-LAPTOP',
        'release_date' => Carbon::now()->subYear(),
        'purchase_cost' => 15000000,
        'notes' => 'case sedikit mengangkat',
    ]);

    // Both young broken and frequent-repair units should be eligible, while healthy 1y spare is not
    $eligibleIds = Asset::eligibleForReplacement()->pluck('id')->all();
    expect($eligibleIds)->toContain($userABrokenLaptop->id);
    expect($eligibleIds)->toContain($frequentRepairLaptop->id);
    expect($eligibleIds)->not->toContain($spareLaptop->id);

    // Evaluation score & reasons
    $evalA = $userABrokenLaptop->replacement_evaluation;
    expect($evalA['tier'])->toBe('Critical');
    expect($evalA['reasons'])->toContain('Broken');
    expect($evalA['needs_user_swap'])->toBeTrue();

    $evalB = $frequentRepairLaptop->replacement_evaluation;
    expect($evalB['reasons'])->toContain('2x Repairs');
    expect($evalB['reasons'])->toContain('Repair Cost 55%');

    // Planner page shows User A in user_needs and troubled tabs
    $plannerResponse = $this->get(route('assets.replacements', ['tab' => 'user_needs']));
    $plannerResponse->assertOk();
    $plannerResponse->assertSee('ACM/NB/230101/001');
    $plannerResponse->assertSee('User A');
    $plannerResponse->assertSee('case sedikit mengangkat');

    // Execute atomic hardware swap for User A
    $swapResponse = $this->post(route('assets.replace', $userABrokenLaptop), [
        'replacement_asset_id' => $spareLaptop->id,
        'checkin_status_id' => $statusBroken->id,
        'replacement_mode' => 'permanent',
        'replacement_date' => now()->format('Y-m-d'),
        'condition_report' => 'Motherboard damaged',
        'notes' => 'Swapped with spare unit',
    ]);

    $swapResponse->assertRedirect();
    $swapResponse->assertSessionHas('success');

    $userABrokenLaptop->refresh();
    $spareLaptop->refresh();

    expect($userABrokenLaptop->isCurrentlyCheckedOut())->toBeFalse();
    expect($userABrokenLaptop->status_id)->toBe($statusBroken->id);
    expect($spareLaptop->isCurrentlyCheckedOut())->toBeTrue();
    expect($spareLaptop->assigned_to_user_id)->toBe($userA->id);
    expect($spareLaptop->status_id)->toBe($statusDeployed->id);
});
