<?php

use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\User;
use Spatie\Permission\Models\Role;

test('it_staff_can_take_ticket_and_transition_to_in_progress', function () {
    Role::firstOrCreate(['name' => 'it_admin']);
    $staff = User::factory()->create(['is_active' => true]);
    $staff->assignRole('it_admin');

    $requester = User::factory()->create(['is_active' => true]);

    $newStatus = TicketStatus::firstOrCreate(['name' => 'New', 'code' => 'NEW'], ['is_closed' => false]);
    $inProgStatus = TicketStatus::firstOrCreate(['name' => 'In Progress', 'code' => 'IN-PROGRESS'], ['is_closed' => false]);

    $ticket = Ticket::create([
        'request_id' => 'REQ-TEST-001',
        'subject' => 'Monitor flickers constantly',
        'problem_description' => 'HDMI cable or monitor port issue',
        'requester_user_id' => $requester->id,
        'requester_name' => $requester->name,
        'status_id' => $newStatus->id,
    ]);

    $this->actingAs($staff);
    $response = $this->post(route('tickets.take', $ticket));

    $response->assertRedirect();
    $this->assertEquals($staff->id, $ticket->fresh()->assigned_to_id);
    $this->assertEquals($inProgStatus->id, $ticket->fresh()->status_id);
});

test('non_staff_cannot_take_ticket_or_update_status', function () {
    $requester = User::factory()->create(['is_active' => true]);
    $otherUser = User::factory()->create(['is_active' => true]);

    $newStatus = TicketStatus::firstOrCreate(['name' => 'New', 'code' => 'NEW'], ['is_closed' => false]);
    $closedStatus = TicketStatus::firstOrCreate(['name' => 'Closed', 'code' => 'CLOSED'], ['is_closed' => true]);

    $ticket = Ticket::create([
        'request_id' => 'REQ-TEST-002',
        'subject' => 'Printer offline',
        'problem_description' => 'Cannot print document',
        'requester_user_id' => $requester->id,
        'status_id' => $newStatus->id,
    ]);

    $this->actingAs($otherUser);

    // Non-staff take ticket should fail
    $takeResponse = $this->post(route('tickets.take', $ticket));
    $takeResponse->assertSessionHas('error');
    $this->assertNull($ticket->fresh()->assigned_to_id);

    // Non-staff update status should fail
    $statusResponse = $this->post(route('tickets.update-status', $ticket), [
        'status_id' => $closedStatus->id,
    ]);
    $statusResponse->assertSessionHas('error');
    $this->assertEquals($newStatus->id, $ticket->fresh()->status_id);
});

test('ticket_search_matches_subject', function () {
    Role::firstOrCreate(['name' => 'super_admin']);
    $admin = User::factory()->create(['is_active' => true]);
    $admin->assignRole('super_admin');
    $this->actingAs($admin);

    $ticket = Ticket::create([
        'request_id' => 'REQ-TEST-003',
        'subject' => 'MacBook Kernel Panic Crash',
        'problem_description' => 'Unrelated text body without title keywords',
        'requester_name' => 'Alice Test',
        'requester_user_id' => $admin->id,
    ]);

    $response = $this->get(route('tickets.index', ['search' => 'Kernel Panic']));
    $response->assertOk();
    $response->assertSee('MacBook Kernel Panic Crash');
});

test('it_staff_can_create_and_resolve_ticket_directly', function () {
    Role::firstOrCreate(['name' => 'it_admin']);
    $staff = User::factory()->create(['is_active' => true]);
    $staff->assignRole('it_admin');

    $requester = User::factory()->create(['is_active' => true]);
    $resolvedStatus = TicketStatus::firstOrCreate(['name' => 'Resolved', 'code' => 'RESOLVED'], ['is_closed' => true]);

    $this->actingAs($staff);
    $response = $this->post(route('tickets.store'), [
        'subject' => 'Fixed Wi-Fi connection in Meeting Room',
        'problem_description' => 'User reported no internet, restarted access point',
        'requester_user_id' => $requester->id,
        'status_id' => $resolvedStatus->id,
        'resolution_notes' => 'Power cycled Ubiquiti AP',
        'actual_hours' => 0.5,
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('tickets.index'));

    $ticket = Ticket::where('subject', 'Fixed Wi-Fi connection in Meeting Room')->first();
    $this->assertNotNull($ticket);
    $this->assertEquals($resolvedStatus->id, $ticket->status_id);
    $this->assertNotNull($ticket->completed_date);
    $this->assertEquals('Power cycled Ubiquiti AP', $ticket->resolution_notes);
});

test('standard_user_can_submit_self_service_ticket_and_only_view_own_tickets', function () {
    $userA = User::factory()->create(['is_active' => true]);
    $userB = User::factory()->create(['is_active' => true]);

    $newStatus = TicketStatus::firstOrCreate(['name' => 'New', 'code' => 'NEW'], ['is_closed' => false]);

    $this->actingAs($userA);
    $response = $this->post(route('tickets.store'), [
        'subject' => 'Need second monitor for accounting spreadsheet',
        'problem_description' => 'Dual monitor setup request',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('tickets.index'));

    $ticketA = Ticket::where('subject', 'Need second monitor for accounting spreadsheet')->first();
    $this->assertNotNull($ticketA);
    $this->assertEquals($userA->id, $ticketA->requester_user_id);
    $this->assertEquals($newStatus->id, $ticketA->status_id);
    $this->assertNull($ticketA->assigned_to_id);

    // User A can view own ticket
    $this->get(route('tickets.show', $ticketA))->assertOk();

    // User B cannot view User A's ticket
    $this->actingAs($userB);
    $this->get(route('tickets.show', $ticketA))->assertForbidden();
});
