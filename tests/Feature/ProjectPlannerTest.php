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
