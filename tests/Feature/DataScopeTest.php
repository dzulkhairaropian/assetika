<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use App\Models\Asset;
use App\Models\Status;
use App\Models\Product;
use App\Models\Category;
use App\Models\MasterCategory;
use App\Models\Brand;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_see_assets_from_other_company(): void
    {
        $companyA = Company::create(['name' => 'Company A', 'code' => 'COA']);
        $companyB = Company::create(['name' => 'Company B', 'code' => 'COB']);

        $deptA = Department::create(['company_id' => $companyA->id, 'name' => 'IT A', 'code' => 'ITA']);
        $deptB = Department::create(['company_id' => $companyB->id, 'name' => 'IT B', 'code' => 'ITB']);

        $userA = User::factory()->create(['company_id' => $companyA->id]);

        $status = Status::create(['name' => 'Available', 'type' => 'deployable']);
        $masterCat = MasterCategory::create(['name' => 'IT Assets', 'code' => 'IT']);
        $category = Category::create(['master_category_id' => $masterCat->id, 'name' => 'Laptop', 'code' => 'LP']);
        $brand = Brand::create(['name' => 'Dell']);
        $product = Product::create(['category_id' => $category->id, 'brand_id' => $brand->id, 'name' => 'Latitude 5420']);
        $location = Location::create(['company_id' => $companyA->id, 'name' => 'Head Office']);

        $assetA = Asset::create([
            'asset_tag' => 'COA/LP/260902/001',
            'product_id' => $product->id,
            'department_id' => $deptA->id,
            'location_id' => $location->id,
            'status_id' => $status->id,
        ]);

        $assetB = Asset::create([
            'asset_tag' => 'COB/LP/260902/001',
            'product_id' => $product->id,
            'department_id' => $deptB->id,
            'location_id' => $location->id,
            'status_id' => $status->id,
        ]);

        $this->actingAs($userA);

        $assets = Asset::all();

        $this->assertTrue($assets->contains($assetA));
        $this->assertFalse($assets->contains($assetB));
    }
}
