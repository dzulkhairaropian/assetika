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
