<?php

use App\Models\Asset;
use App\Models\AssetCheckout;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Company;
use App\Models\Department;
use App\Models\Location;
use App\Models\MasterCategory;
use App\Models\Product;
use App\Models\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('dashboard_loads_with_bento_fleet_metrics_and_action_center', function () {
    $company = Company::create(['name' => 'Tech Corp', 'code' => 'TC']);
    $user = User::factory()->create(['company_id' => $company->id]);
    Role::firstOrCreate(['name' => 'super_admin']);
    $user->assignRole('super_admin');
    $this->actingAs($user);

    $dept = Department::create(['company_id' => $company->id, 'name' => 'IT Ops', 'code' => 'OPS']);
    $location = Location::create(['company_id' => $company->id, 'name' => 'DC-1']);
    $masterCat = MasterCategory::firstOrCreate(['name' => 'IT Asset', 'code' => 'IT']);
    $category = Category::create(['master_category_id' => $masterCat->id, 'name' => 'Server', 'code' => 'SRV']);
    $brand = Brand::create(['name' => 'HPE']);
    $product = Product::create([
        'master_category_id' => $masterCat->id,
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'name' => 'ProLiant DL380',
        'model' => 'DL380',
    ]);
    $statusDeployed = Status::create(['master_category_id' => $masterCat->id, 'name' => 'Deployed', 'type' => 'deployable']);

    $asset = Asset::create([
        'product_id' => $product->id,
        'department_id' => $dept->id,
        'location_id' => $location->id,
        'status_id' => $statusDeployed->id,
        'serial_number' => 'SRV-DL380-001',
        'release_date' => '2026-01-01',
    ]);

    // Create an overdue checkout loan
    AssetCheckout::create([
        'asset_id' => $asset->id,
        'checked_out_to_user_id' => $user->id,
        'checked_out_by_user_id' => $user->id,
        'department_id' => $dept->id,
        'location_id' => $location->id,
        'checked_out_at' => now()->subDays(10),
        'expected_return_date' => now()->subDays(3)->toDateString(),
    ]);

    $response = $this->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('Fleet Inventory & Deployment', false);
    $response->assertSee('Action Required');
    $response->assertSee('Overdue Loan');
    $response->assertSee('Checkouts & Returns', false);
    $response->assertSee('Maintenance & Service', false);
});
