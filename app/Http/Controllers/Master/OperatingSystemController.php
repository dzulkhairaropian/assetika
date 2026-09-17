<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\OperatingSystem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class OperatingSystemController extends Controller
{
    public function index(Request $request)
    {
        $query = OperatingSystem::withCount('assets');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $summary = [
            'total' => OperatingSystem::count(),
            'today' => OperatingSystem::whereDate('created_at', Carbon::today())->count(),
        ];

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100, 250, 500]) ? (int) $request->input('per_page') : 50;
        $operatingSystems = $query->latest()->paginate($perPage)->withQueryString();

        return view('pages.master.operating-systems.index', compact('operatingSystems', 'summary'));
    }

    public function create()
    {
        $brands = Brand::orderBy('name')->get();
        $brandOpts = $brands->map(fn ($b) => ['id' => $b->id, 'text' => $b->name])->values()->all();

        return view('pages.master.operating-systems.create', compact('brandOpts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand_id' => 'required|exists:brands,id',
            'description' => 'nullable|string',
        ]);

        OperatingSystem::create($validated);

        if ($request->has('create_another')) {
            return redirect()->route('operating-systems.create')->with('success', 'Operating System created. Add another one.');
        }

        return redirect()->route('operating-systems.index')->with('success', 'Operating System created successfully.');
    }

    public function edit(OperatingSystem $operatingSystem)
    {
        $brands = Brand::orderBy('name')->get();
        $brandOpts = $brands->map(fn ($b) => ['id' => $b->id, 'text' => $b->name])->values()->all();

        return view('pages.master.operating-systems.edit', compact('operatingSystem', 'brandOpts'));
    }

    public function update(Request $request, OperatingSystem $operatingSystem)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand_id' => 'required|exists:brands,id',
            'description' => 'nullable|string',
        ]);

        $operatingSystem->update($validated);

        return redirect()->route('operating-systems.index')->with('success', 'Operating System updated successfully.');
    }

    public function destroy(OperatingSystem $operatingSystem)
    {
        $operatingSystem->delete();

        return redirect()->route('operating-systems.index')->with('success', 'Operating System deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:operating_systems,id',
        ]);

        OperatingSystem::whereIn('id', $request->ids)->delete();

        return redirect()->route('operating-systems.index')->with('success', 'Selected operating systems deleted successfully.');
    }
}
