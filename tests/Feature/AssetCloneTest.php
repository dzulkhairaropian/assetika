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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('asset_clone_route_redirects_to_create_with_clone_from_param', function () {
    $company = Company::create(['name' => 'Clone Co', 'code' => 'CL']);
    $user = User::factory()->create(['company_id' => $company->id]);
    Role::firstOrCreate(['name' => 'super_admin']);
    $user->assignRole('super_admin');
    $this->actingAs($user);

    $department = Department::create(['company_id' => $company->id, 'name' => 'IT Dept', 'code' => 'IT']);
    $masterCat = MasterCategory::firstOrCreate(['name' => 'IT Asset', 'code' => 'IT']);
    $category = Category::create(['master_category_id' => $masterCat->id, 'name' => 'Laptop', 'code' => 'NB']);
    $brand = Brand::create(['name' => 'ThinkPad']);
    $product = Product::create([
        'master_category_id' => $masterCat->id,
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'name' => 'T14 Gen 3',
        'model' => 'T14',
    ]);
    $status = Status::create(['master_category_id' => $masterCat->id, 'name' => 'Ready to Deploy', 'type' => 'deployable']);

    $asset = Asset::create([
        'product_id' => $product->id,
        'department_id' => $department->id,
        'status_id' => $status->id,
        'serial_number' => 'SN-CLONE-TEST-001',
        'release_date' => '2026-10-20',
        'specification' => '32GB RAM, 1TB NVMe',
        'notes' => 'Cloned template machine',
    ]);

    $response = $this->get(route('assets.clone', $asset));
    $response->assertRedirect(route('assets.create', ['clone_from' => $asset->id]));

    $createResponse = $this->get(route('assets.create', ['clone_from' => $asset->id]));
    $createResponse->assertOk();
    $createResponse->assertViewHas('clonedAsset');
    $createResponse->assertSee('Cloning Asset:');
    $createResponse->assertSee($asset->asset_tag);
    $createResponse->assertSee('32GB RAM, 1TB NVMe');
});

test('submitting_new_serial_number_for_cloned_asset_generates_distinct_tag_and_device_name', function () {
    $company = Company::create(['name' => 'Clone Co', 'code' => 'CL']);
    $user = User::factory()->create(['company_id' => $company->id]);
    Role::firstOrCreate(['name' => 'super_admin']);
    $user->assignRole('super_admin');
    $this->actingAs($user);

    $department = Department::create(['company_id' => $company->id, 'name' => 'IT Dept', 'code' => 'IT']);
    $masterCat = MasterCategory::firstOrCreate(['name' => 'IT Asset', 'code' => 'IT']);
    $category = Category::create(['master_category_id' => $masterCat->id, 'name' => 'Laptop', 'code' => 'NB']);
    $brand = Brand::create(['name' => 'ThinkPad']);
    $product = Product::create([
        'master_category_id' => $masterCat->id,
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'name' => 'T14 Gen 3',
        'model' => 'T14',
    ]);
    $status = Status::create(['master_category_id' => $masterCat->id, 'name' => 'Ready to Deploy', 'type' => 'deployable']);

    $original = Asset::create([
        'product_id' => $product->id,
        'department_id' => $department->id,
        'status_id' => $status->id,
        'serial_number' => 'SN-ORIGINAL-001',
        'release_date' => '2026-10-20',
        'specification' => '16GB RAM, 512GB SSD',
    ]);

    $response = $this->post(route('assets.store'), [
        'product_id' => $original->product_id,
        'department_id' => $original->department_id,
        'status_id' => $original->status_id,
        'serial_number' => 'SN-CLONED-002',
        'release_date' => '2026-10-20',
        'specification' => $original->specification,
    ]);

    $response->assertRedirect(route('assets.index'));

    $cloned = Asset::where('serial_number', 'SN-CLONED-002')->first();
    $this->assertNotNull($cloned);
    $this->assertNotEquals($original->id, $cloned->id);
    $this->assertNotEquals($original->asset_tag, $cloned->asset_tag);
    $this->assertNotEquals($original->device_name, $cloned->device_name);
    $this->assertEquals($original->specification, $cloned->specification);
});
