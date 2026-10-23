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
