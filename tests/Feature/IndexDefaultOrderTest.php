<?php

use App\Models\Asset;
use App\Models\Category;
use App\Models\Company;
use App\Models\Department;
use App\Models\MasterCategory;
use App\Models\Product;
use App\Models\Status;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('index pages display newest added records first even with identical timestamps', function () {
    $company = Company::create(['name' => 'Acme Corp', 'code' => 'ACM']);
    $dept = Department::create(['company_id' => $company->id, 'name' => 'IT', 'code' => 'IT']);

    Role::firstOrCreate(['name' => 'super_admin']);
    $admin = User::factory()->create(['company_id' => $company->id, 'department_id' => $dept->id]);
    $admin->assignRole('super_admin');
    $this->actingAs($admin);

    $master = MasterCategory::firstOrCreate(['name' => 'IT Asset', 'code' => 'IT']);
    $cat = Category::create(['master_category_id' => $master->id, 'name' => 'Laptop', 'code' => 'LP']);
    $product = Product::create(['master_category_id' => $master->id, 'category_id' => $cat->id, 'name' => 'ThinkPad']);
    $statusFirst = Status::create(['master_category_id' => $master->id, 'name' => 'AAA First Status']);
    $statusLatest = Status::create(['master_category_id' => $master->id, 'name' => 'ZZZ Latest Status']);

    $firstAsset = Asset::create([
        'asset_tag' => 'ACM/LP/260101/001',
        'product_id' => $product->id,
        'department_id' => $dept->id,
        'status_id' => $statusFirst->id,
    ]);
    $latestAsset = Asset::create([
        'asset_tag' => 'ACM/LP/260101/002',
        'product_id' => $product->id,
        'department_id' => $dept->id,
        'status_id' => $statusLatest->id,
    ]);

    $firstTicket = Ticket::create([
        'request_id' => 'TKT-0001',
        'subject' => 'First Ticket',
        'problem_description' => 'First problem',
        'requester_user_id' => $admin->id,
        'requester_name' => $admin->name,
    ]);
    $latestTicket = Ticket::create([
        'request_id' => 'TKT-0002',
        'subject' => 'Latest Ticket',
        'problem_description' => 'Latest problem',
        'requester_user_id' => $admin->id,
        'requester_name' => $admin->name,
    ]);

    $assetsResponse = $this->get(route('assets.index'));
    $assetsResponse->assertOk();
    $assetsResponse->assertSeeInOrder([$latestAsset->asset_tag, $firstAsset->asset_tag]);

    $ticketsResponse = $this->get(route('tickets.index'));
    $ticketsResponse->assertOk();
    $ticketsResponse->assertSeeInOrder([$latestTicket->request_id, $firstTicket->request_id]);

    $statusesResponse = $this->get(route('statuses.index'));
    $statusesResponse->assertOk();
    $statusesResponse->assertSeeInOrder([$statusLatest->name, $statusFirst->name]);
});
