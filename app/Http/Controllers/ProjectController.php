<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $query = Project::with(['owner', 'tasks']);

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $projects = $query->latest()->paginate(12)->withQueryString();
        $users = User::all();

        return view('pages.projects.index', compact('projects', 'search', 'users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'owner_id' => ['nullable', 'exists:users,id'],
            'status' => ['required', 'in:planning,active,on_hold,completed,cancelled'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'budget' => ['nullable', 'numeric'],
        ]);

        $data['code'] = 'PRJ-' . date('Ym') . '-' . strtoupper(substr(uniqid(), -4));

        Project::create($data);

        return redirect()->route('projects.index')->with('success', 'Project created successfully.');
    }

    public function show(Project $project): View
    {
        $project->load(['owner', 'tasks.assignedTo', 'tasks.ticket', 'tasks.asset']);
        $users = User::all();
        $tickets = Ticket::latest()->limit(50)->get();
        $assets = Asset::latest()->limit(50)->get();

        return view('pages.projects.show', compact('project', 'users', 'tickets', 'assets'));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'owner_id' => ['nullable', 'exists:users,id'],
            'status' => ['required', 'in:planning,active,on_hold,completed,cancelled'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'budget' => ['nullable', 'numeric'],
        ]);

        $project->update($data);

        return redirect()->back()->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Project deleted successfully.');
    }

    public function storeTask(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'assigned_to_id' => ['nullable', 'exists:users,id'],
            'status' => ['required', 'in:todo,in_progress,review,done'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'due_date' => ['nullable', 'date'],
            'ticket_id' => ['nullable', 'exists:tickets,id'],
            'asset_id' => ['nullable', 'exists:assets,id'],
        ]);

        $project->tasks()->create($data);

        return redirect()->back()->with('success', 'Task added to project.');
    }

    public function updateTaskStatus(Request $request, ProjectTask $task): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:todo,in_progress,review,done'],
        ]);

        $task->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Task status updated.');
    }

    public function destroyTask(ProjectTask $task): RedirectResponse
    {
        $task->delete();

        return redirect()->back()->with('success', 'Task deleted.');
    }
}
