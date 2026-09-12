<?php

use App\Models\Asset;
use App\Models\Category;
use App\Models\Component;
use App\Models\MasterCategory;
use App\Models\Product;
use App\Models\Ticket;
use App\Models\User;
use Spatie\Permission\Models\Role;

test('checkout component to asset automatically generates completed maintenance ticket', function () {
    Role::firstOrCreate(['name' => 'super_admin']);
    $user = User::factory()->create(['is_active' => true]);
    $user->assignRole('super_admin');
    $this->actingAs($user);

    $masterCat = MasterCategory::firstOrCreate(['name' => 'IT Component']);
    $category = Category::firstOrCreate(['name' => 'Battery', 'master_category_id' => $masterCat->id]);

    $product = Product::create([
        'name' => 'Baterai Dell Latitude 3410',
        'master_category_id' => $masterCat->id,
        'category_id' => $category->id,
    ]);

    $component = Component::create([
        'product_id' => $product->id,
        'name' => 'Baterai Dell Latitude 3410',
        'category_id' => $category->id,
        'release_date' => '2026-09-15',
        'qty' => 6,
        'min_qty' => 1,
    ]);

    $this->assertNotNull($component->component_tag);
    $this->assertStringContainsString('260915', $component->component_tag);

    $asset = Asset::create([
        'name' => 'Laptop Dell Latitude 3410',
        'asset_tag' => 'AST-TEST-3410',
        'serial' => 'SN-3410-TEST',
    ]);

    $response = $this->post(route('components.checkout', $component), [
        'asset_id' => $asset->id,
        'qty' => 1,
        'notes' => 'Pemasangan baterai pengganti',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    $this->assertDatabaseHas('component_asset', [
        'component_id' => $component->id,
        'asset_id' => $asset->id,
        'qty' => 1,
    ]);

    $this->assertEquals(5, $component->fresh()->availableQty());

    $this->assertDatabaseHas('tickets', [
        'asset_id' => $asset->id,
        'created_by_id' => $user->id,
    ]);

    $ticket = Ticket::where('asset_id', $asset->id)->first();
    $this->assertNotNull($ticket);
    $this->assertStringContainsString('[Auto-Generated] Component Installed/Attached: 1x Baterai Dell Latitude 3410', $ticket->problem_description);
});
