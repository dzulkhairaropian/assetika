<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MasterCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MasterCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = MasterCategory::withCount('categories');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $summary = [
            'total' => MasterCategory::count(),
            'today' => MasterCategory::whereDate('created_at', Carbon::today())->count(),
        ];

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100, 250, 500]) ? (int) $request->input('per_page') : 50;
        $masterCategories = $query->latest()->paginate($perPage)->withQueryString();

        return view('pages.master.master-categories.index', compact('masterCategories', 'summary'));
    }

    public function create()
    {
        return view('pages.master.master-categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        MasterCategory::create($validated);

        if ($request->has('create_another')) {
            return redirect()->route('master-categories.create')->with('success', 'Master Category created. Add another one.');
        }

        return redirect()->route('master-categories.index')->with('success', 'Master Category created successfully.');
    }

    public function edit(MasterCategory $masterCategory)
    {
        return view('pages.master.master-categories.edit', compact('masterCategory'));
    }

    public function update(Request $request, MasterCategory $masterCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $masterCategory->update($validated);

        return redirect()->route('master-categories.index')->with('success', 'Master Category updated successfully.');
    }

    public function destroy(MasterCategory $masterCategory)
    {
        $masterCategory->delete();

        return redirect()->route('master-categories.index')->with('success', 'Master Category deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:master_categories,id',
        ]);

        MasterCategory::whereIn('id', $request->ids)->delete();

        return redirect()->route('master-categories.index')->with('success', 'Selected master categories deleted successfully.');
    }
}
