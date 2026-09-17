<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $query = Location::with('company')->withCount('assets');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $summary = [
            'total' => Location::count(),
            'today' => Location::whereDate('created_at', Carbon::today())->count(),
        ];

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100, 250, 500]) ? (int) $request->input('per_page') : 50;
        $locations = $query->latest()->paginate($perPage)->withQueryString();
        $companies = Company::all();

        return view('pages.master.locations.index', compact('locations', 'companies', 'summary'));
    }

    public function create()
    {
        $companies = Company::orderBy('name')->get();
        $companyOpts = $companies->map(fn ($c) => ['id' => $c->id, 'text' => $c->name])->values()->all();

        return view('pages.master.locations.create', compact('companyOpts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_id' => 'required|exists:companies,id',
            'description' => 'nullable|string',
        ]);

        Location::create($validated);

        if ($request->has('create_another')) {
            return redirect()->route('locations.create')->with('success', 'Location created successfully. Add another one.');
        }

        return redirect()->route('locations.index')->with('success', 'Location created successfully.');
    }

    public function edit(Location $location)
    {
        $companies = Company::orderBy('name')->get();
        $companyOpts = $companies->map(fn ($c) => ['id' => $c->id, 'text' => $c->name])->values()->all();

        return view('pages.master.locations.edit', compact('location', 'companyOpts'));
    }

    public function update(Request $request, Location $location)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_id' => 'required|exists:companies,id',
            'description' => 'nullable|string',
        ]);

        $location->update($validated);

        return redirect()->route('locations.index')->with('success', 'Location updated successfully.');
    }

    public function destroy(Location $location)
    {
        $location->delete();

        return redirect()->route('locations.index')->with('success', 'Location deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:locations,id',
        ]);

        Location::whereIn('id', $request->ids)->delete();

        return redirect()->route('locations.index')->with('success', 'Selected locations deleted successfully.');
    }
}
