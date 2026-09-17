<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Department::with('company')->withCount(['users', 'assets']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('code', 'like', "%$search%");
            });
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $summary = [
            'total' => Department::count(),
            'today' => Department::whereDate('created_at', Carbon::today())->count(),
        ];

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100, 250, 500]) ? (int) $request->input('per_page') : 50;
        $departments = $query->latest()->paginate($perPage)->withQueryString();
        $companies = Company::all();

        return view('pages.master.departments.index', compact('departments', 'companies', 'summary'));
    }

    public function create()
    {
        $companies = Company::orderBy('name')->get();
        $companyOpts = $companies->map(fn ($c) => ['id' => $c->id, 'text' => $c->name])->values()->all();

        return view('pages.master.departments.create', compact('companyOpts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:departments,code',
            'company_id' => 'required|exists:companies,id',
            'description' => 'nullable|string',
        ]);

        Department::create($validated);

        if ($request->has('create_another')) {
            return redirect()->route('departments.create')->with('success', 'Department created successfully. Add another one.');
        }

        return redirect()->route('departments.index')->with('success', 'Department created successfully.');
    }

    public function edit(Department $department)
    {
        $companies = Company::orderBy('name')->get();
        $companyOpts = $companies->map(fn ($c) => ['id' => $c->id, 'text' => $c->name])->values()->all();

        return view('pages.master.departments.edit', compact('department', 'companyOpts'));
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:departments,code,'.$department->id,
            'company_id' => 'required|exists:companies,id',
            'description' => 'nullable|string',
        ]);

        $department->update($validated);

        return redirect()->route('departments.index')->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        $department->delete();

        return redirect()->route('departments.index')->with('success', 'Department deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:departments,id',
        ]);

        Department::whereIn('id', $request->ids)->delete();

        return redirect()->route('departments.index')->with('success', 'Selected departments deleted successfully.');
    }
}
