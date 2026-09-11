<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\MasterCategory;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\Status;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $query = Project::with(['owner.department', 'statusRel', 'tasks.statusRel', 'tasks.assignedTo']);

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where(function ($q) use ($status) {
                $q->where('status', $status)
                    ->orWhere('status_id', $status);
            });
        }

        $projects = $query->latest()->paginate(12)->withQueryString();
        $users = User::all();

        $projectMC = MasterCategory::where('name', 'Project')->first();
        $projectStatuses = $projectMC ? Status::where('master_category_id', $projectMC->id)->get() : collect();

        return view('pages.projects.index', compact('projects', 'search', 'users', 'projectStatuses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'owner_id' => ['nullable', 'exists:users,id'],
            'status_id' => ['nullable', 'exists:statuses,id'],
            'status' => ['nullable', 'string'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'budget' => ['nullable', 'numeric'],
        ]);

        if (empty($data['status_id']) && ! empty($data['status'])) {
            $st = Status::whereHas('masterCategory', fn ($q) => $q->where('name', 'Project'))
                ->where('name', 'like', $data['status'])
                ->first();
            if ($st) {
                $data['status_id'] = $st->id;
            }
        }

        $data['code'] = 'PRJ-'.date('Ym').'-'.strtoupper(substr(uniqid(), -4));

        Project::create($data);

        return redirect()->route('projects.index')->with('success', 'Project created successfully.');
    }

    public function show(Project $project): View
    {
        $project->load(['owner', 'statusRel', 'tasks.assignedTo', 'tasks.ticket', 'tasks.asset', 'tasks.statusRel']);
        $users = User::all();
        $tickets = Ticket::latest()->limit(50)->get();
        $assets = Asset::latest()->limit(50)->get();

        $taskMC = MasterCategory::where('name', 'Project Task')->first();
        $taskStatuses = $taskMC ? Status::where('master_category_id', $taskMC->id)->get() : collect();

        return view('pages.projects.show', compact('project', 'users', 'tickets', 'assets', 'taskStatuses'));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'owner_id' => ['nullable', 'exists:users,id'],
            'status_id' => ['nullable', 'exists:statuses,id'],
            'status' => ['nullable', 'string'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'budget' => ['nullable', 'numeric'],
        ]);

        if (! empty($data['status_id'])) {
            $st = Status::find($data['status_id']);
            if ($st) {
                $data['status'] = strtolower(str_replace(' ', '_', $st->name));
            }
        }

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
            'status_id' => ['nullable', 'exists:statuses,id'],
            'status' => ['nullable', 'string'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'due_date' => ['nullable', 'date'],
            'ticket_id' => ['nullable', 'exists:tickets,id'],
            'asset_id' => ['nullable', 'exists:assets,id'],
        ]);

        if (! empty($data['status_id'])) {
            $st = Status::find($data['status_id']);
            if ($st) {
                $data['status'] = match (strtolower($st->name)) {
                    'to do' => 'todo',
                    'in progress' => 'in_progress',
                    'review' => 'review',
                    'done' => 'done',
                    default => strtolower(str_replace(' ', '_', $st->name))
                };
            }
        } elseif (empty($data['status_id']) && ! empty($data['status'])) {
            $st = Status::whereHas('masterCategory', fn ($q) => $q->where('name', 'Project Task'))
                ->where('name', 'like', $data['status'])
                ->first();
            if ($st) {
                $data['status_id'] = $st->id;
            }
        }

        $project->tasks()->create($data);

        return redirect()->back()->with('success', 'Task added to project.');
    }

    public function updateTaskStatus(Request $request, ProjectTask $task)
    {
        $validated = $request->validate([
            'status_id' => ['nullable', 'exists:statuses,id'],
            'status' => ['nullable', 'string'],
        ]);

        if (! empty($validated['status_id'])) {
            $st = Status::find($validated['status_id']);
            $task->status_id = $st->id;
            $task->status = match (strtolower($st->name)) {
                'to do' => 'todo',
                'in progress' => 'in_progress',
                'review' => 'review',
                'done' => 'done',
                default => strtolower(str_replace(' ', '_', $st->name))
            };
        } elseif (! empty($validated['status'])) {
            $task->status = $validated['status'];
            $st = Status::whereHas('masterCategory', fn ($q) => $q->where('name', 'Project Task'))
                ->where('name', 'like', str_replace('_', ' ', $validated['status']))
                ->first();
            if ($st) {
                $task->status_id = $st->id;
            }
        }

        $task->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'progress' => $task->project->progress_percentage,
            ]);
        }

        return redirect()->back()->with('success', 'Task status updated.');
    }

    public function destroyTask(ProjectTask $task): RedirectResponse
    {
        $task->delete();

        return redirect()->back()->with('success', 'Task deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);
        if (! empty($ids)) {
            Project::whereIn('id', $ids)->delete();

            return redirect()->back()->with('success', count($ids).' projects deleted successfully.');
        }

        return redirect()->back()->with('error', 'No projects selected for deletion.');
    }
}
