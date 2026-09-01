<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Depreciation;
use Illuminate\Http\Request;

class DepreciationController extends Controller
{
    public function index(Request $request)
    {
        $query = Depreciation::withCount('assets');

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $depreciations = $query->latest()->paginate(50)->withQueryString();

        return view('pages.master.depreciations.index', compact('depreciations'));
    }

    public function create()
    {
        return view('pages.master.depreciations.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'months' => 'required|integer|min:1|max:600',
        ]);

        Depreciation::create($validated);

        if ($request->has('create_another')) {
            return redirect()->route('depreciations.create')->with('success', 'Depreciation created. Add another one.');
        }

        return redirect()->route('depreciations.index')->with('success', 'Depreciation created successfully.');
    }

    public function edit(Depreciation $depreciation)
    {
        return view('pages.master.depreciations.edit', compact('depreciation'));
    }

    public function update(Request $request, Depreciation $depreciation)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'months' => 'required|integer|min:1|max:600',
        ]);

        $depreciation->update($validated);

        return redirect()->route('depreciations.index')->with('success', 'Depreciation updated successfully.');
    }

    public function destroy(Depreciation $depreciation)
    {
        $depreciation->delete();

        return redirect()->route('depreciations.index')->with('success', 'Depreciation deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:depreciations,id',
        ]);

        Depreciation::whereIn('id', $request->ids)->delete();

        return redirect()->route('depreciations.index')->with('success', 'Selected depreciations deleted successfully.');
    }
}
