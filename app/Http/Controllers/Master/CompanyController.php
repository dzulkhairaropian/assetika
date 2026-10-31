<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::with(['group'])->withCount('departments');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('code', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            });
        }

        $summary = [
            'total' => Company::count(),
            'today' => Company::whereDate('created_at', Carbon::today())->count(),
        ];

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100, 250, 500]) ? (int) $request->input('per_page') : 50;
        $companies = $query->latest()->paginate($perPage)->withQueryString();

        return view('pages.master.companies.index', compact('companies', 'summary'));
    }

    public function create()
    {
        $groups = Group::orderBy('name')->get();
        $groupOpts = $groups->map(fn ($g) => ['id' => $g->id, 'text' => $g->name])->values()->all();

        return view('pages.master.companies.create', compact('groupOpts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'group_id' => 'nullable|exists:groups,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:companies,code',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('companies/logos', 'public');
        }

        Company::create($validated);

        if ($request->has('create_another')) {
            return redirect()->route('companies.create')->with('success', 'Company created successfully. Add another one.');
        }

        return redirect()->route('companies.index')->with('success', 'Company created successfully.');
    }

    public function edit(Company $company)
    {
        $groups = Group::orderBy('name')->get();
        $groupOpts = $groups->map(fn ($g) => ['id' => $g->id, 'text' => $g->name])->values()->all();

        return view('pages.master.companies.edit', compact('company', 'groupOpts'));
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'group_id' => 'nullable|exists:groups,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:companies,code,'.$company->id,
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            if ($company->logo) {
                Storage::disk('public')->delete($company->logo);
            }
            $validated['logo'] = $request->file('logo')->store('companies/logos', 'public');
        }

        $company->update($validated);

        return redirect()->route('companies.index')->with('success', 'Company updated successfully.');
    }

    public function destroy(Company $company)
    {
        $company->delete();

        return redirect()->route('companies.index')->with('success', 'Company deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:companies,id',
        ]);

        Company::whereIn('id', $request->ids)->delete();

        return redirect()->route('companies.index')->with('success', 'Selected companies deleted successfully.');
    }
}
