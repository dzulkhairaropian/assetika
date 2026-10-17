<?php

use App\Models\CalendarEvent;
use App\Models\ConsumableRequest;
use App\Models\User;
use Spatie\Permission\Models\Role;

test('consumable_request_generate_request_number_is_sequential_and_collision_safe', function () {
    $num1 = ConsumableRequest::generateRequestNumber('2026-10-18');
    $this->assertStringStartsWith('CR/261018/', $num1);

    $user = User::factory()->create();
    ConsumableRequest::create([
        'requester_id' => $user->id,
        'request_number' => $num1,
        'requested_at' => '2026-10-18 10:00:00',
    ]);

    $num2 = ConsumableRequest::generateRequestNumber('2026-10-18');
    $this->assertNotEquals($num1, $num2);
    $this->assertStringEndsWith('/002', $num2);
});

test('calendar_events_can_be_created_updated_and_deleted', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->postJson(route('api.calendar.store'), [
        'title' => 'Server Maintenance Window',
        'start' => '2026-10-20 22:00:00',
        'end' => '2026-10-21 02:00:00',
        'calendar' => 'Danger',
    ]);

    $response->assertOk();
    $response->assertJson(['success' => true]);

    $this->assertDatabaseHas('calendar_events', [
        'title' => 'Server Maintenance Window',
        'calendar' => 'Danger',
    ]);

    $event = CalendarEvent::where('title', 'Server Maintenance Window')->first();

    // Update
    $updateResponse = $this->putJson(route('api.calendar.update', $event->id), [
        'title' => 'Updated Server Maintenance',
        'start' => '2026-10-20 23:00:00',
    ]);
    $updateResponse->assertOk();
    $this->assertEquals('Updated Server Maintenance', $event->fresh()->title);

    // Events list contains it
    $listResponse = $this->getJson(route('api.calendar.events'));
    $listResponse->assertOk();
    $listResponse->assertJsonFragment(['title' => 'Updated Server Maintenance']);

    // Delete
    $deleteResponse = $this->deleteJson(route('api.calendar.destroy', $event->id));
    $deleteResponse->assertOk();
    $this->assertDatabaseMissing('calendar_events', ['id' => $event->id]);
});

test('rbac_protects_admin_backup_routes_from_regular_users', function () {
    $regularUser = User::factory()->create();
    $this->actingAs($regularUser);

    $this->get(route('backups.index'))->assertForbidden();
    $this->get(route('settings.index'))->assertForbidden();

    Role::firstOrCreate(['name' => 'super_admin']);
    $regularUser->assignRole('super_admin');

    $this->get(route('backups.index'))->assertOk();
    $this->get(route('settings.index'))->assertOk();
});
