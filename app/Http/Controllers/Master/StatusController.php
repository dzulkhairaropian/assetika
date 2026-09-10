<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MasterCategory;
use App\Models\Status;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    public function index(Request $request)
    {
        $query = Status::with('masterCategory')->withCount(['assets', 'consumables']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%$search%");
        }

        if ($request->filled('master_category_id')) {
            $query->where('master_category_id', $request->master_category_id);
        }

        $statuses = $query->orderBy('master_category_id')->orderBy('name')->paginate(50)->withQueryString();
        $masterCategories = MasterCategory::withCount('statuses')->orderBy('name')->get();

        return view('pages.master.statuses.index', compact('statuses', 'masterCategories'));
    }

    public function create()
    {
        $masterCategories = MasterCategory::orderBy('name')->get();

        return view('pages.master.statuses.create', compact('masterCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:50',
            'master_category_id' => 'required|exists:master_categories,id',
            'description' => 'nullable|string',
        ]);

        Status::create($validated);

        if ($request->has('create_another')) {
            return redirect()->route('statuses.create')->with('success', 'Status created. Add another one.');
        }

        return redirect()->route('statuses.index')->with('success', 'Status created successfully.');
    }

    public function edit(Status $status)
    {
        $masterCategories = MasterCategory::orderBy('name')->get();

        return view('pages.master.statuses.edit', compact('status', 'masterCategories'));
    }

    public function update(Request $request, Status $status)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:50',
            'master_category_id' => 'required|exists:master_categories,id',
            'description' => 'nullable|string',
        ]);

        $status->update($validated);

        return redirect()->route('statuses.index')->with('success', 'Status updated successfully.');
    }

    public function destroy(Status $status)
    {
        $status->delete();

        return redirect()->route('statuses.index')->with('success', 'Status deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:statuses,id',
        ]);

        Status::whereIn('id', $request->ids)->delete();

        return redirect()->route('statuses.index')->with('success', 'Selected statuses deleted successfully.');
    }

    public function bulkEdit(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:statuses,id',
            'master_category_id' => 'nullable|exists:master_categories,id',
            'color' => 'nullable|string|max:50',
        ]);

        $data = array_filter($request->only(['master_category_id', 'color']));

        if (empty($data)) {
            return back()->with('error', 'No changes specified for bulk update.');
        }

        Status::whereIn('id', $request->ids)->update($data);

        return redirect()->route('statuses.index')->with('success', count($request->ids).' statuses updated successfully.');
    }
}
