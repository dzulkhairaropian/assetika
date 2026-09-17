<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PositionController extends Controller
{
    public function index(Request $request)
    {
        $query = Position::withCount('users');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $summary = [
            'total' => Position::count(),
            'today' => Position::whereDate('created_at', Carbon::today())->count(),
        ];

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100, 250, 500]) ? (int) $request->input('per_page') : 50;
        $positions = $query->latest()->paginate($perPage)->withQueryString();

        return view('pages.master.positions.index', compact('positions', 'summary'));
    }

    public function create()
    {
        return view('pages.master.positions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:positions,code',
            'description' => 'nullable|string',
        ]);

        Position::create($validated);

        if ($request->has('create_another')) {
            return redirect()->route('positions.create')->with('success', 'Position created successfully. Add another one.');
        }

        return redirect()->route('positions.index')->with('success', 'Position created successfully.');
    }

    public function edit(Position $position)
    {
        return view('pages.master.positions.edit', compact('position'));
    }

    public function update(Request $request, Position $position)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:positions,code,'.$position->id,
            'description' => 'nullable|string',
        ]);

        $position->update($validated);

        return redirect()->route('positions.index')->with('success', 'Position updated successfully.');
    }

    public function destroy(Position $position)
    {
        $position->delete();

        return redirect()->route('positions.index')->with('success', 'Position deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:positions,id',
        ]);

        Position::whereIn('id', $request->ids)->delete();

        return redirect()->route('positions.index')->with('success', 'Selected positions deleted successfully.');
    }
}
