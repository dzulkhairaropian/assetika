<?php

use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\User;
use Spatie\Permission\Models\Role;

test('project_task_can_be_updated_with_due_date_and_details', function () {
    Role::firstOrCreate(['name' => 'it_admin']);
    $user = User::factory()->create();
    $user->assignRole('it_admin');
    $this->actingAs($user);

    $project = Project::create([
        'code' => 'PRJ-202611-0001',
        'name' => 'IT Plan Nov 2026',
        'priority' => 'high',
        'status' => 'in_progress',
    ]);

    $task = ProjectTask::create([
        'project_id' => $project->id,
        'title' => 'Initial Task Name',
        'priority' => 'medium',
        'status' => 'todo',
    ]);

    $response = $this->put(route('projects.update-task', $task), [
        'title' => 'Service Laptop Speaker Stevani',
        'description' => 'Replace faulty speaker unit for Stevani',
        'priority' => 'high',
        'due_date' => '2026-11-15',
        'assigned_to_id' => $user->id,
    ]);

    $response->assertRedirect();
    $task->refresh();

    $this->assertEquals('Service Laptop Speaker Stevani', $task->title);
    $this->assertEquals('Replace faulty speaker unit for Stevani', $task->description);
    $this->assertEquals('high', $task->priority);
    $this->assertEquals('2026-11-15', $task->due_date->format('Y-m-d'));
    $this->assertEquals($user->id, $task->assigned_to_id);
});

test('project_tasks_with_due_date_are_included_in_calendar_events_feed', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $project = Project::create([
        'code' => 'PRJ-202611-0002',
        'name' => 'IT Infrastructure Plan',
        'priority' => 'medium',
        'status' => 'in_progress',
    ]);

    ProjectTask::create([
        'project_id' => $project->id,
        'title' => 'Upgrade Firmware 71G Ver 7.413',
        'priority' => 'high',
        'status' => 'todo',
        'due_date' => '2026-11-20',
    ]);

    $response = $this->getJson(route('api.calendar.events'));
    $response->assertOk();

    $response->assertJsonFragment([
        'title' => '[Task] Upgrade Firmware 71G Ver 7.413 (IT Infrastructure Plan)',
        'start' => '2026-11-20',
        'allDay' => true,
    ]);
});

test('creating_project_with_auto_create_ticket_generates_and_links_ticket', function () {
    Role::firstOrCreate(['name' => 'it_admin']);
    $user = User::factory()->create();
    $user->assignRole('it_admin');
    $this->actingAs($user);

    $response = $this->post(route('projects.store'), [
        'name' => 'Data Center UPS Migration',
        'description' => 'Replace battery banks and calibrate UPS load',
        'priority' => 'high',
        'auto_create_ticket' => '1',
    ]);

    $response->assertRedirect(route('projects.index'));

    $project = Project::where('name', 'Data Center UPS Migration')->first();
    $this->assertNotNull($project);
    $this->assertNotNull($project->ticket_id);
    $this->assertDatabaseHas('tickets', [
        'id' => $project->ticket_id,
        'subject' => '[Project] Data Center UPS Migration',
    ]);
});

test('creating_project_task_with_auto_create_ticket_generates_and_links_ticket', function () {
    Role::firstOrCreate(['name' => 'it_admin']);
    $user = User::factory()->create();
    $user->assignRole('it_admin');
    $this->actingAs($user);

    $project = Project::create([
        'code' => 'PRJ-202611-0003',
        'name' => 'Network Cabling Refactor',
        'priority' => 'medium',
        'status' => 'in_progress',
    ]);

    $response = $this->post(route('projects.store-task', $project), [
        'title' => 'Terminate Patch Panel Port 1-24',
        'priority' => 'urgent',
        'auto_create_ticket' => '1',
    ]);

    $response->assertRedirect();

    $task = ProjectTask::where('title', 'Terminate Patch Panel Port 1-24')->first();
    $this->assertNotNull($task);
    $this->assertNotNull($task->ticket_id);
    $this->assertDatabaseHas('tickets', [
        'id' => $task->ticket_id,
    ]);
    $this->assertStringContainsString('Terminate Patch Panel Port 1-24', $task->ticket->subject);
});

test('manual_actions_can_create_ticket_for_project_and_tasks', function () {
    Role::firstOrCreate(['name' => 'it_admin']);
    $user = User::factory()->create();
    $user->assignRole('it_admin');
    $this->actingAs($user);

    $project = Project::create([
        'code' => 'PRJ-202611-0004',
        'name' => 'Access Point Deployment',
        'priority' => 'medium',
        'status' => 'in_progress',
    ]);

    $task1 = ProjectTask::create([
        'project_id' => $project->id,
        'title' => 'Mount AP 2nd Floor',
        'priority' => 'medium',
        'status' => 'todo',
    ]);

    $task2 = ProjectTask::create([
        'project_id' => $project->id,
        'title' => 'Configure SSID VLAN',
        'priority' => 'high',
        'status' => 'todo',
    ]);

    // Create ticket for project
    $this->post(route('projects.create-ticket', $project))->assertRedirect();
    $project->refresh();
    $this->assertNotNull($project->ticket_id);

    // Bulk create tickets for tasks
    $this->post(route('projects.bulk-create-task-tickets', $project))->assertRedirect();
    $task1->refresh();
    $task2->refresh();

    $this->assertNotNull($task1->ticket_id);
    $this->assertNotNull($task2->ticket_id);
});

test('projects_index_and_show_pages_render_without_error', function () {
    Role::firstOrCreate(['name' => 'it_admin']);
    $user = User::factory()->create();
    $user->assignRole('it_admin');
    $this->actingAs($user);

    $project = Project::create([
        'code' => 'PRJ-202611-0005',
        'name' => 'Page Render Verification',
        'priority' => 'low',
        'status' => 'planning',
    ]);

    $this->get(route('projects.index'))->assertOk();
    $this->get(route('projects.show', $project))->assertOk();
});
