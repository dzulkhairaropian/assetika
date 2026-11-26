<?php

use App\Models\Asset;
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
use App\Services\AssetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('send to broken button renders on asset detail and creates incident ticket automatically', function () {
    $company = Company::create(['name' => 'Acme Corp', 'code' => 'ACM']);
    $dept = Department::create(['company_id' => $company->id, 'name' => 'IT', 'code' => 'IT']);
    $location = Location::create(['company_id' => $company->id, 'name' => 'HQ']);

    Role::firstOrCreate(['name' => 'super_admin']);
    $admin = User::factory()->create(['company_id' => $company->id, 'department_id' => $dept->id, 'location_id' => $location->id]);
    $holder = User::factory()->create(['name' => 'Holder User', 'company_id' => $company->id, 'department_id' => $dept->id, 'location_id' => $location->id]);
    $admin->assignRole('super_admin');
    $this->actingAs($admin);

    $master = MasterCategory::firstOrCreate(['name' => 'IT Asset', 'code' => 'IT']);
    $cat = Category::create(['master_category_id' => $master->id, 'name' => 'Notebook', 'code' => 'NB']);
    $product = Product::create(['master_category_id' => $master->id, 'category_id' => $cat->id, 'name' => 'Dell Vostro 3400']);
    $statusReady = Status::create(['master_category_id' => $master->id, 'name' => 'Ready to Deploy', 'type' => 'deployable']);
    Status::create(['master_category_id' => $master->id, 'name' => 'Deployed', 'type' => 'deployable']);
    $statusBroken = Status::create(['master_category_id' => $master->id, 'name' => 'Broken', 'type' => 'undeployable']);

    TicketType::firstOrCreate(['code' => 'INC'], ['name' => 'Incident']);
    TicketCategory::firstOrCreate(['name' => 'Hardware'], ['code' => 'HARDWARE', 'is_active' => true]);
    Priority::firstOrCreate(['name' => 'High', 'code' => 'HIGH'], ['level' => 3]);
    TicketStatus::firstOrCreate(['name' => 'New', 'code' => 'NEW'], ['is_closed' => false]);

    $asset = Asset::create([
        'asset_tag' => 'ACM/NB/260101/001',
        'product_id' => $product->id,
        'department_id' => $dept->id,
        'location_id' => $location->id,
        'status_id' => $statusReady->id,
    ]);

    app(AssetService::class)->processCheckout($asset, $holder, [
        'location_id' => $location->id,
        'department_id' => $dept->id,
        'checkout_date' => now()->subDays(10),
    ]);
    $asset->refresh();

    $showRes = $this->get(route('assets.show', $asset));
    $showRes->assertOk();
    $showRes->assertSee('Send to Broken');

    $brokenRes = $this->post(route('assets.broken.process', $asset), [
        'broken_date' => '2026-11-26',
        'priority' => 'High',
        'description' => 'Motherboard shorted and screen flickering',
        'save_to_notes' => '1',
        'checkin_from_holder' => '0',
    ]);

    $brokenRes->assertRedirect(route('assets.show', $asset));
    $brokenRes->assertSessionHas('success');

    $asset->refresh();
    expect($asset->status_id)->toBe($statusBroken->id);
    expect($asset->notes)->toContain('Motherboard shorted and screen flickering');
    expect($asset->isCurrentlyCheckedOut())->toBeTrue();

    $ticket = Ticket::where('asset_id', $asset->id)
        ->where('subject', 'like', 'Asset Broken:%')
        ->first();

    expect($ticket)->not->toBeNull();
    expect($ticket->requester_user_id)->toBe($holder->id);
    expect($ticket->problem_description)->toContain('Motherboard shorted and screen flickering');

    // Button should disappear once status is Broken
    $afterRes = $this->get(route('assets.show', $asset));
    $afterRes->assertOk();
    $afterRes->assertDontSee('Send Asset to Broken');
});
