<?php

use App\Exports\ConsumableStockOpnameCsvExport;
use App\Exports\ConsumableStockOpnameExport;
use App\Models\Consumable;
use App\Models\MasterCategory;
use App\Models\Product;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'super_admin']);
    Role::firstOrCreate(['name' => 'it_admin']);
    Role::firstOrCreate(['name' => 'employee']);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('it_admin');

    $master = MasterCategory::firstOrCreate(['name' => 'IT Consumable'], ['code' => 'ITC']);
    $product = Product::firstOrCreate(
        ['name' => 'Test Opname Toner Item'],
        ['master_category_id' => $master->id, 'product_type' => 'Toner']
    );

    Consumable::firstOrCreate(
        ['product_id' => $product->id],
        ['quantity' => 15, 'low_threshold' => 5, 'purchase_cost' => 250000]
    );
});

test('it admin can download consumable stock opname excel template', function () {
    Excel::fake();

    $response = $this->actingAs($this->admin)->get(route('consumables.opname-template', [
        'format' => 'excel',
        'opname_date' => '2026-11-24',
        'location' => 'HQ Warehouse',
    ]));

    $response->assertSuccessful();

    Excel::assertDownloaded('consumable-stock-opname-2026-11-24.xlsx', function (ConsumableStockOpnameExport $export) {
        return true;
    });
});

test('it admin can download consumable stock opname csv template', function () {
    Excel::fake();

    $response = $this->actingAs($this->admin)->get(route('consumables.opname-template', [
        'format' => 'csv',
        'opname_date' => '2026-11-24',
    ]));

    $response->assertSuccessful();

    Excel::assertDownloaded('consumable-stock-opname-2026-11-24.csv', function (ConsumableStockOpnameCsvExport $export) {
        return true;
    });
});

test('consumable index contains the download form opname trigger button', function () {
    $response = $this->actingAs($this->admin)->get(route('consumables.index'));

    $response->assertSuccessful();
    $response->assertSee('Download Form Opname');
    $response->assertSee('opnameModalOpen');
});
