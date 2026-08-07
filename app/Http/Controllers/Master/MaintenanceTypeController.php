<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceType;
use Illuminate\Http\Request;

class MaintenanceTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = MaintenanceType::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $maintenanceTypes = $query->latest()->paginate(50)->withQueryString();

        return view('pages.master.maintenance-types.index', compact('maintenanceTypes'));
    }

    public function create()
    {
        return view('pages.master.maintenance-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_default' => 'nullable|boolean',
        ]);

        MaintenanceType::create($validated);

        if ($request->has('create_another')) {
            return redirect()->route('maintenance-types.create')->with('success', 'Maintenance type created. Add another one.');
        }

        return redirect()->route('maintenance-types.index')->with('success', 'Maintenance type created successfully.');
    }

    public function edit(MaintenanceType $maintenanceType)
    {
        return view('pages.master.maintenance-types.edit', compact('maintenanceType'));
    }

    public function update(Request $request, MaintenanceType $maintenanceType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_default' => 'nullable|boolean',
        ]);

        $maintenanceType->update($validated);

        return redirect()->route('maintenance-types.index')->with('success', 'Maintenance type updated successfully.');
    }

    public function destroy(MaintenanceType $maintenanceType)
    {
        $maintenanceType->delete();

        return redirect()->route('maintenance-types.index')->with('success', 'Maintenance type deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:maintenance_types,id',
        ]);

        MaintenanceType::whereIn('id', $request->ids)->delete();

        return redirect()->route('maintenance-types.index')->with('success', 'Selected maintenance types deleted successfully.');
    }
}
