<?php

use App\Models\Asset;
use App\Models\AssetMaintenance;
use App\Models\Category;
use App\Models\Company;
use App\Models\Department;
use App\Models\Location;
use App\Models\MasterCategory;
use App\Models\Priority;
use App\Models\Product;
use App\Models\Status;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketStatus;
use App\Models\TicketType;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->company = Company::create(['name' => 'Acme Corp', 'code' => 'ACM']);
    $this->dept = Department::create(['company_id' => $this->company->id, 'name' => 'IT', 'code' => 'IT']);
    $this->location = Location::create(['company_id' => $this->company->id, 'name' => 'HQ']);

    Role::firstOrCreate(['name' => 'super_admin']);
    $this->admin = User::factory()->create(['company_id' => $this->company->id, 'department_id' => $this->dept->id, 'location_id' => $this->location->id]);
    $this->admin->assignRole('super_admin');
    $this->actingAs($this->admin);

    $this->master = MasterCategory::firstOrCreate(['name' => 'IT Asset', 'code' => 'IT']);
    $cat = Category::create(['master_category_id' => $this->master->id, 'name' => 'Notebook', 'code' => 'NB']);
    $this->product = Product::create(['master_category_id' => $this->master->id, 'category_id' => $cat->id, 'name' => 'ThinkPad T14']);

    $this->statusReady = Status::create(['master_category_id' => $this->master->id, 'name' => 'Ready to Deploy', 'type' => 'deployable']);
    $this->statusDeployed = Status::create(['master_category_id' => $this->master->id, 'name' => 'Deployed', 'type' => 'deployable']);
    $this->statusBroken = Status::create(['master_category_id' => $this->master->id, 'name' => 'Broken', 'type' => 'undeployable']);
    $this->statusService = Status::create(['master_category_id' => $this->master->id, 'name' => 'Service', 'type' => 'undeployable']);
    $this->statusRetired = Status::create(['master_category_id' => $this->master->id, 'name' => 'Retired', 'type' => 'archived']);

    $this->vendor = Vendor::create(['name' => 'Lenovo Authorized Service']);

    TicketType::firstOrCreate(['code' => 'INC'], ['name' => 'Incident']);
    TicketType::firstOrCreate(['code' => 'REQ'], ['name' => 'Request']);
    TicketCategory::firstOrCreate(['name' => 'Hardware'], ['code' => 'HARDWARE', 'is_active' => true]);
    Priority::firstOrCreate(['name' => 'High', 'code' => 'HIGH'], ['level' => 3]);
    Priority::firstOrCreate(['name' => 'Medium', 'code' => 'MED'], ['level' => 2]);
    TicketStatus::firstOrCreate(['name' => 'New', 'code' => 'NEW'], ['is_closed' => false]);
    TicketStatus::firstOrCreate(['name' => 'In Progress', 'code' => 'IN-PROG'], ['is_closed' => false]);
    TicketStatus::firstOrCreate(['name' => 'Resolved', 'code' => 'RESOLVED'], ['is_closed' => true]);
});

test('asset lifecycle flow handles ready -> broken -> maintenance -> successful completion and blocks checkout while broken or in service', function () {
    $asset = Asset::create([
        'asset_tag' => 'ACM/NB/260101/001',
        'product_id' => $this->product->id,
        'department_id' => $this->dept->id,
        'location_id' => $this->location->id,
        'status_id' => $this->statusReady->id,
    ]);

    // 1. Mark Ready asset as Broken -> creates open incident ticket & blocks checkout
    $this->post(route('assets.broken.process', $asset), [
        'description' => 'Keyboard keys unresponsive',
        'priority' => 'High',
        'save_to_notes' => '1',
    ])->assertRedirect(route('assets.show', $asset));

    $asset->refresh();
    expect($asset->status_id)->toBe($this->statusBroken->id);

    // Checkout must be blocked while Broken
    $this->get(route('assets.checkout', $asset))->assertRedirect(route('assets.show', $asset));
    $this->post(route('assets.checkout.process', $asset), [
        'checked_out_to_user_id' => $this->admin->id,
        'location_id' => $this->location->id,
        'department_id' => $this->dept->id,
        'checked_out_at' => now()->toDateString(),
    ])->assertSessionHas('error');

    // 2. Send Broken asset to Maintenance -> transitions Broken ticket to In Progress & creates Service ticket
    $this->post(route('assets.maintenance.process', $asset), [
        'service_provider' => $this->vendor->name,
        'description' => 'Replace keyboard assembly',
        'cost' => 500000,
        'start_date' => now()->toDateString(),
    ])->assertRedirect(route('assets.show', $asset));

    $asset->refresh();
    $maintenance = AssetMaintenance::where('asset_id', $asset->id)->first();
    expect($asset->status_id)->toBe($this->statusService->id);
    expect($maintenance)->not->toBeNull();
    expect($maintenance->description)->toBe('Replace keyboard assembly');

    // Complete Maintenance page loads cleanly with statuses dropdown
    $this->get(route('assets.maintenance.complete', [$asset, $maintenance]))
        ->assertOk()
        ->assertSee('Ready to Deploy')
        ->assertSee('Broken')
        ->assertSee('Replace keyboard assembly');

    // 3. Complete Maintenance successfully -> Ready to Deploy & closes open service/broken tickets
    $this->post(route('assets.maintenance.complete.process', [$asset, $maintenance]), [
        'completion_date' => now()->toDateString(),
        'cost' => 650000,
        'status_id' => $this->statusReady->id,
        'notes' => 'Keyboard replaced and tested OK',
    ])->assertRedirect(route('assets.show', $asset));

    $asset->refresh();
    $maintenance->refresh();
    expect($asset->status_id)->toBe($this->statusReady->id);
    expect((float) $maintenance->cost)->toBe(650000.0);

    $openTicketsCount = Ticket::where('asset_id', $asset->id)
        ->whereHas('status', fn ($q) => $q->where('is_closed', false))
        ->count();
    expect($openTicketsCount)->toBe(0);
});

test('failed maintenance honors chosen broken or retired status instead of forcing ready to deploy', function () {
    $asset = Asset::create([
        'asset_tag' => 'ACM/NB/260101/002',
        'product_id' => $this->product->id,
        'department_id' => $this->dept->id,
        'location_id' => $this->location->id,
        'status_id' => $this->statusReady->id,
    ]);

    // Send to Maintenance
    $this->post(route('assets.maintenance.process', $asset), [
        'service_provider' => $this->vendor->name,
        'description' => 'No power after liquid spill',
        'cost' => 0,
        'start_date' => now()->toDateString(),
    ])->assertRedirect(route('assets.show', $asset));

    $maintenance = AssetMaintenance::where('asset_id', $asset->id)->first();

    // Complete Maintenance with Broken outcome (unrepairable, components stripped)
    $this->post(route('assets.maintenance.complete.process', [$asset, $maintenance]), [
        'completion_date' => now()->toDateString(),
        'cost' => 150000,
        'status_id' => $this->statusBroken->id,
        'notes' => 'Mainboard corroded beyond repair',
        'is_components_stripped' => '1',
    ])->assertRedirect(route('assets.show', $asset));

    $asset->refresh();
    expect($asset->status_id)->toBe($this->statusBroken->id);
    expect($asset->is_components_stripped)->toBeTrue();

    // An open Asset Broken ticket should exist for follow-up / disposal
    $openBrokenTicket = Ticket::where('asset_id', $asset->id)
        ->where('subject', 'like', 'Asset Broken:%')
        ->whereHas('status', fn ($q) => $q->where('is_closed', false))
        ->first();
    expect($openBrokenTicket)->not->toBeNull();

    // Now test completing maintenance directly as Retired on a second asset
    $asset2 = Asset::create([
        'asset_tag' => 'ACM/NB/260101/003',
        'product_id' => $this->product->id,
        'department_id' => $this->dept->id,
        'location_id' => $this->location->id,
        'status_id' => $this->statusReady->id,
    ]);

    $this->post(route('assets.maintenance.process', $asset2), [
        'service_provider' => $this->vendor->name,
        'description' => 'Chassis cracked',
        'start_date' => now()->toDateString(),
    ]);
    $maint2 = AssetMaintenance::where('asset_id', $asset2->id)->first();

    $this->post(route('assets.maintenance.complete.process', [$asset2, $maint2]), [
        'completion_date' => now()->toDateString(),
        'cost' => 0,
        'status_id' => $this->statusRetired->id,
        'notes' => 'Decommissioned directly after vendor inspection',
    ])->assertRedirect(route('assets.show', $asset2));

    $asset2->refresh();
    expect($asset2->status_id)->toBe($this->statusRetired->id);
    expect($asset2->retired_at)->not->toBeNull();
    expect(Ticket::where('asset_id', $asset2->id)->whereHas('status', fn ($q) => $q->where('is_closed', false))->count())->toBe(0);
});

test('asset show page displays comprehensive last user details after checkin and supports legacy holder fallback', function () {
    $holder = User::factory()->create([
        'name' => 'Rina Accounting',
        'email' => 'rina@example.com',
        'company_id' => $this->company->id,
        'department_id' => $this->dept->id,
    ]);

    $asset = Asset::create([
        'asset_tag' => 'ACM/NB/201201/017',
        'product_id' => $this->product->id,
        'department_id' => $this->dept->id,
        'location_id' => $this->location->id,
        'status_id' => $this->statusReady->id,
    ]);

    $this->post(route('assets.checkout.process', $asset), [
        'checked_out_to_user_id' => $holder->id,
        'department_id' => $this->dept->id,
        'location_id' => $this->location->id,
        'checked_out_at' => now()->subDays(12)->toDateString(),
    ])->assertRedirect(route('assets.show', $asset));

    $this->post(route('assets.checkin.process', $asset), [
        'checked_in_at' => now()->toDateString(),
        'status_id' => $this->statusBroken->id,
        'condition_report' => 'LCD flickering and hinge cracked',
        'checkin_notes' => 'Returned by user before replacement',
    ])->assertRedirect(route('assets.show', $asset));

    $showResponse = $this->get(route('assets.show', $asset));
    $showResponse->assertOk();
    $showResponse->assertSee('Last User');
    $showResponse->assertSee('Rina Accounting');
    $showResponse->assertSee('rina@example.com');
    $showResponse->assertSee('LCD flickering and hinge cracked');
    $showResponse->assertSee('Returned by user before replacement');
    $showResponse->assertSee('Last BAST PDF');
    $showResponse->assertSee($this->admin->name);

    // Fallback when asset has legacy user_name set without checkout history
    $legacyAsset = Asset::create([
        'asset_tag' => 'ACM/NB/201201/018',
        'product_id' => $this->product->id,
        'department_id' => $this->dept->id,
        'location_id' => $this->location->id,
        'status_id' => $this->statusBroken->id,
        'user_name' => 'Legacy Finance Staff',
    ]);

    $legacyResponse = $this->get(route('assets.show', $legacyAsset));
    $legacyResponse->assertOk();
    $legacyResponse->assertSee('Recorded Holder');
    $legacyResponse->assertSee('Legacy Finance Staff');
});

test('equipment loan form pdf can be downloaded blank, with selected assets, and for an existing checkout', function () {
    $holder = User::factory()->create([
        'name' => 'Andi Borrower',
        'email' => 'andi@example.com',
        'company_id' => $this->company->id,
        'department_id' => $this->dept->id,
    ]);

    $asset = Asset::create([
        'asset_tag' => 'ACM/NB/260501/001',
        'product_id' => $this->product->id,
        'department_id' => $this->dept->id,
        'location_id' => $this->location->id,
        'status_id' => $this->statusReady->id,
        'serial_number' => 'SN-LOAN-001',
    ]);

    // 1. Blank loan form PDF download
    $blankResponse = $this->get(route('assets.loan-form.download'));
    $blankResponse->assertOk();
    $blankResponse->assertHeader('content-type', 'application/pdf');
    expect($blankResponse->headers->get('content-disposition'))->toContain('attachment;');

    // 2. Pre-filled loan form PDF download with selected asset and borrower metadata
    $selectedResponse = $this->get(route('assets.loan-form.download', [
        'asset_ids' => [$asset->id],
        'borrower_name' => 'Andi Borrower',
        'department' => 'IT',
        'loan_date' => now()->toDateString(),
        'return_date' => now()->addDays(7)->toDateString(),
        'purpose' => 'Client presentation',
    ]));
    $selectedResponse->assertOk();
    $selectedResponse->assertHeader('content-type', 'application/pdf');

    // 3. Checkout-specific loan form PDF download
    $this->post(route('assets.checkout.process', $asset), [
        'checked_out_to_user_id' => $holder->id,
        'department_id' => $this->dept->id,
        'location_id' => $this->location->id,
        'checked_out_at' => now()->toDateString(),
        'expected_return_date' => now()->addDays(5)->toDateString(),
    ])->assertRedirect(route('assets.show', $asset));

    $checkout = $asset->fresh()->currentCheckout;
    $checkoutLoanResponse = $this->get(route('assets.loan-form.checkout', [$asset, $checkout]));
    $checkoutLoanResponse->assertOk();
    $checkoutLoanResponse->assertHeader('content-type', 'application/pdf');
    expect($checkoutLoanResponse->headers->get('content-disposition'))->toContain('Andi-Borrower');

    // 4. Verify UI links on index, transactions, and show pages
    $this->get(route('assets.index'))->assertOk()->assertSee('Loan Form');
    $this->get(route('asset-transactions.index'))->assertOk()->assertSee('Download Loan Form');
    $this->get(route('assets.show', $asset))->assertOk()->assertSee('Loan Form');
});
