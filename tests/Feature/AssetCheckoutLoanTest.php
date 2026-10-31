<?php

use App\Models\Asset;
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

test('checkout_with_expected_return_date_stores_date_and_detects_overdue', function () {
    $company = Company::create(['name' => 'Loan Co', 'code' => 'LC']);
    $user = User::factory()->create(['company_id' => $company->id]);
    Role::firstOrCreate(['name' => 'super_admin']);
    $user->assignRole('super_admin');
    $this->actingAs($user);

    $department = Department::create(['company_id' => $company->id, 'name' => 'Loan Dept', 'code' => 'LD']);
    $location = Location::create(['company_id' => $company->id, 'name' => 'Headquarters']);
    $masterCat = MasterCategory::firstOrCreate(['name' => 'IT Asset', 'code' => 'IT']);
    $category = Category::create(['master_category_id' => $masterCat->id, 'name' => 'Laptop', 'code' => 'NB']);
    $brand = Brand::create(['name' => 'Dell']);
    $product = Product::create([
        'master_category_id' => $masterCat->id,
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'name' => 'Latitude 5520',
        'model' => '5520',
    ]);
    $status = Status::create(['master_category_id' => $masterCat->id, 'name' => 'Ready to Deploy', 'type' => 'deployable']);

    $asset = Asset::create([
        'product_id' => $product->id,
        'department_id' => $department->id,
        'location_id' => $location->id,
        'status_id' => $status->id,
        'serial_number' => 'SN-LOAN-001',
        'release_date' => '2026-10-20',
    ]);

    // Checkout via HTTP
    $response = $this->post(route('assets.checkout.process', $asset), [
        'checked_out_to_user_id' => $user->id,
        'location_id' => $location->id,
        'department_id' => $department->id,
        'checked_out_at' => now()->subDays(5)->toDateString(),
        'expected_return_date' => now()->subDays(2)->toDateString(),
        'checkout_notes' => 'Short 3-day loan for presentation',
    ]);

    $response->assertRedirect(route('assets.show', $asset));

    $asset->refresh();
    $this->assertTrue($asset->isCurrentlyCheckedOut());
    $this->assertTrue($asset->isOverdue());
    $this->assertEquals(2, $asset->currentCheckout->overdueDays());

    // Verify Asset Show view displays Overdue badge
    $showResponse = $this->get(route('assets.show', $asset));
    $showResponse->assertOk();
    $showResponse->assertSee('Overdue');
    $showResponse->assertSee('Short 3-day loan for presentation');

    // Verify Transaction Index displays Overdue badge and filter
    $txResponse = $this->get(route('asset-transactions.index', ['tab' => 'overdue']));
    $txResponse->assertOk();
    $txResponse->assertSee('Overdue Loans');
    $txResponse->assertSee($asset->asset_tag);
});

test('bulk checkout rejects assets that are not ready to deploy', function () {
    $company = Company::create(['name' => 'Bulk Co', 'code' => 'BC']);
    $user = User::factory()->create(['company_id' => $company->id]);
    Role::firstOrCreate(['name' => 'super_admin']);
    $user->assignRole('super_admin');
    $this->actingAs($user);

    $department = Department::create(['company_id' => $company->id, 'name' => 'Bulk Dept', 'code' => 'BD']);
    $location = Location::create(['company_id' => $company->id, 'name' => 'HQ']);
    $masterCat = MasterCategory::firstOrCreate(['name' => 'IT Asset', 'code' => 'IT']);
    $category = Category::create(['master_category_id' => $masterCat->id, 'name' => 'Laptop', 'code' => 'NB']);
    $brand = Brand::create(['name' => 'Lenovo']);
    $product = Product::create([
        'master_category_id' => $masterCat->id,
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'name' => 'ThinkPad',
        'model' => 'T14',
    ]);
    $readyStatus = Status::create(['master_category_id' => $masterCat->id, 'name' => 'Ready to Deploy', 'type' => 'deployable']);
    $brokenStatus = Status::create(['master_category_id' => $masterCat->id, 'name' => 'Broken', 'type' => 'undeployable']);

    $readyAsset = Asset::create([
        'product_id' => $product->id,
        'department_id' => $department->id,
        'location_id' => $location->id,
        'status_id' => $readyStatus->id,
        'serial_number' => 'SN-BULK-001',
        'release_date' => '2026-10-20',
    ]);

    $brokenAsset = Asset::create([
        'product_id' => $product->id,
        'department_id' => $department->id,
        'location_id' => $location->id,
        'status_id' => $brokenStatus->id,
        'serial_number' => 'SN-BULK-002',
        'release_date' => '2026-10-20',
    ]);

    // Bulk checkout form with both
    $response = $this->get(route('assets.bulk-checkout', ['ids' => "{$readyAsset->id},{$brokenAsset->id}"]));
    $response->assertOk();
    $response->assertSee($readyAsset->asset_tag);
    $response->assertDontSee($brokenAsset->asset_tag);

    // Process bulk checkout
    $postResponse = $this->post(route('assets.bulk-checkout.process'), [
        'ids' => [$readyAsset->id, $brokenAsset->id],
        'checked_out_to_user_id' => $user->id,
        'department_id' => $department->id,
        'location_id' => $location->id,
    ]);
    $postResponse->assertRedirect(route('assets.index'));

    $readyAsset->refresh();
    $brokenAsset->refresh();
    expect($readyAsset->isCurrentlyCheckedOut())->toBeTrue();
    expect($brokenAsset->isCurrentlyCheckedOut())->toBeFalse();
});

test('asset utilization summary calculates total duration and top user', function () {
    $company = Company::create(['name' => 'Util Co', 'code' => 'UC']);
    $user = User::factory()->create(['company_id' => $company->id, 'name' => 'Alice']);
    $department = Department::create(['company_id' => $company->id, 'name' => 'Util Dept', 'code' => 'UD']);
    $location = Location::create(['company_id' => $company->id, 'name' => 'HQ']);
    $masterCat = MasterCategory::firstOrCreate(['name' => 'IT Asset', 'code' => 'IT']);
    $category = Category::create(['master_category_id' => $masterCat->id, 'name' => 'Laptop', 'code' => 'NB']);
    $brand = Brand::create(['name' => 'Dell']);
    $product = Product::create([
        'master_category_id' => $masterCat->id,
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'name' => 'XPS',
        'model' => '13',
    ]);
    $status = Status::create(['master_category_id' => $masterCat->id, 'name' => 'Ready to Deploy', 'type' => 'deployable']);

    $asset = Asset::create([
        'product_id' => $product->id,
        'department_id' => $department->id,
        'location_id' => $location->id,
        'status_id' => $status->id,
        'serial_number' => 'SN-UTIL-001',
        'release_date' => '2026-10-20',
    ]);

    $summary = $asset->utilization_summary;
    expect($summary)->toBeArray();
    expect($summary['top_user'])->toBeNull();
    expect($summary['total_display'])->toBe('< 1 day');
});
