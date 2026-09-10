<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\OperatingSystem;
use Illuminate\Http\Request;

class OperatingSystemController extends Controller
{
    public function index(Request $request)
    {
        $query = OperatingSystem::withCount('assets');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $operatingSystems = $query->latest()->paginate(50)->withQueryString();

        return view('pages.master.operating-systems.index', compact('operatingSystems'));
    }

    public function create()
    {
        $brands = Brand::orderBy('name')->get();

        return view('pages.master.operating-systems.create', compact('brands'));
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

        return view('pages.master.operating-systems.edit', compact('operatingSystem', 'brands'));
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
