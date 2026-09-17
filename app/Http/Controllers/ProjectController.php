<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\MasterCategory;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\Status;
use App\Models\Ticket;
use App\Models\User;
use App\Services\ProjectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ProjectController extends Controller
{
    protected ProjectService $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

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

        $summary = [
            'total' => Project::count(),
            'today' => Project::whereDate('created_at', Carbon::today())->count(),
        ];

        if ($tab = $request->input('tab')) {
            if ($tab === 'today') {
                $query->whereDate('projects.created_at', Carbon::today());
            }
        }

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100, 250, 500]) ? (int) $request->input('per_page') : 50;
        $projects = $query->latest()->paginate($perPage)->withQueryString();
        $users = User::orderBy('name')->get();
        $userOpts = $users->map(fn ($u) => ['id' => $u->id, 'text' => $u->name])->values()->all();

        $projectMC = MasterCategory::where('name', 'Project')->first();
        $projectStatuses = $projectMC ? Status::where('master_category_id', $projectMC->id)->get() : collect();
        $statusOpts = $projectStatuses->map(fn ($s) => ['id' => $s->id, 'text' => $s->name])->values()->all();

        return view('pages.projects.index', compact('projects', 'search', 'users', 'projectStatuses', 'userOpts', 'statusOpts', 'summary'));
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

        $this->projectService->createProject($data);

        return redirect()->route('projects.index')->with('success', 'Project created successfully.');
    }

    public function show(Project $project): View
    {
        $project->load(['owner', 'statusRel', 'tasks.assignedTo', 'tasks.ticket', 'tasks.asset', 'tasks.statusRel']);
        $users = User::orderBy('name')->get();
        $tickets = Ticket::latest()->limit(50)->get();
        $assets = Asset::latest()->limit(50)->get();

        $userOpts = $users->map(fn ($u) => ['id' => $u->id, 'text' => $u->name])->values()->all();
        $ticketOpts = $tickets->map(fn ($t) => ['id' => $t->id, 'text' => '#'.$t->request_id.' - '.substr($t->problem_description, 0, 40)])->values()->all();
        $assetOpts = $assets->map(fn ($a) => ['id' => $a->id, 'text' => $a->asset_tag.' - '.($a->product?->name ?? $a->device_name)])->values()->all();

        $taskMC = MasterCategory::where('name', 'Project Task')->first();
        $taskStatuses = $taskMC ? Status::where('master_category_id', $taskMC->id)->get() : collect();
        $statusOpts = $taskStatuses->map(fn ($s) => ['id' => $s->id, 'text' => $s->name])->values()->all();

        return view('pages.projects.show', compact('project', 'users', 'tickets', 'assets', 'taskStatuses', 'userOpts', 'ticketOpts', 'assetOpts', 'statusOpts'));
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

        $this->projectService->updateProject($project, $data);

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

        $this->projectService->addTask($project, $data);

        return redirect()->back()->with('success', 'Task added to project.');
    }

    public function updateTaskStatus(Request $request, ProjectTask $task)
    {
        $validated = $request->validate([
            'status_id' => ['nullable', 'exists:statuses,id'],
            'status' => ['nullable', 'string'],
        ]);

        $res = $this->projectService->updateTaskStatus($task, $validated);

        if ($request->wantsJson()) {
            return response()->json($res);
        }

        return redirect()->back()->with('success', 'Task status updated.');
    }

    public function destroyTask(ProjectTask $task): RedirectResponse
    {
        $project = $task->project;
        $task->delete();
        $project->syncAutoStatus();

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
