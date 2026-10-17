<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Location;
use App\Models\StockCount;
use App\Models\StockCountItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class StockCountController extends Controller
{
    public function index(Request $request)
    {
        $query = StockCount::with(['location', 'createdBy'])->orderByDesc('start_date');

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $summary = [
            'total' => StockCount::count(),
            'today' => StockCount::whereDate('created_at', Carbon::today())->count(),
        ];

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100, 250, 500]) ? (int) $request->input('per_page') : 50;
        $stockCounts = $query->paginate($perPage)->withQueryString();

        return view('pages.stock-counts.index', compact('stockCounts', 'summary'));
    }

    public function create()
    {
        $locations = Location::orderBy('name')->get();

        return view('pages.stock-counts.create', compact('locations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location_id' => 'nullable|exists:locations,id',
            'start_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $stockCount = StockCount::create([
            ...$validated,
            'created_by_user_id' => auth()->id(),
        ]);

        return redirect()->route('stock-counts.show', $stockCount)->with('success', 'Stock count created. Add assets to verify.');
    }

    public function show(StockCount $stockCount)
    {
        $stockCount->load(['items.asset', 'items.verifiedBy', 'location', 'createdBy']);

        return view('pages.stock-counts.show', compact('stockCount'));
    }

    public function edit(StockCount $stockCount)
    {
        $locations = Location::orderBy('name')->get();

        return view('pages.stock-counts.edit', compact('stockCount', 'locations'));
    }

    public function update(Request $request, StockCount $stockCount)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location_id' => 'nullable|exists:locations,id',
            'start_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $stockCount->update($validated);

        return redirect()->route('stock-counts.show', $stockCount)->with('success', 'Stock count updated.');
    }

    public function destroy(StockCount $stockCount)
    {
        $stockCount->delete();

        return redirect()->route('stock-counts.index')->with('success', 'Stock count deleted.');
    }

    public function addAssets(StockCount $stockCount)
    {
        return view('pages.stock-counts.add-assets', compact('stockCount'));
    }

    public function processAddAssets(Request $request, StockCount $stockCount)
    {
        $request->validate([
            'asset_ids' => 'required|array',
            'asset_ids.*' => 'exists:assets,id',
        ]);

        $existing = $stockCount->items()->pluck('asset_id')->toArray();
        $newIds = array_diff($request->asset_ids, $existing);

        foreach ($newIds as $assetId) {
            $asset = Asset::find($assetId);
            $stockCount->items()->create([
                'asset_id' => $assetId,
                'expected_status' => $asset && $asset->isCurrentlyCheckedOut() ? 'checked_out' : 'available',
            ]);
        }

        return redirect()->route('stock-counts.show', $stockCount)->with('success', count($newIds).' assets added to stock count.');
    }

    public function verifyItem(StockCount $stockCount, StockCountItem $item)
    {
        if ($item->stock_count_id !== $stockCount->id) {
            abort(404);
        }

        $validated = request()->validate([
            'found_status' => 'required|string|max:50',
            'notes' => 'nullable|string|max:1000',
        ]);

        $item->update([
            'found_status' => $validated['found_status'],
            'verified_by_user_id' => auth()->id(),
            'verified_at' => now(),
            'notes' => $validated['notes'] ?? null,
        ]);

        // Auto-complete if all items verified
        if ($stockCount->items()->whereNull('found_status')->count() === 0) {
            $stockCount->update(['status' => 'completed', 'end_date' => now()]);
        }

        return back()->with('success', 'Asset verified.');
    }

    public function markComplete(StockCount $stockCount)
    {
        $stockCount->update(['status' => 'completed', 'end_date' => now()]);

        return back()->with('success', 'Stock count marked as completed.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:stock_counts,id',
        ]);

        StockCount::whereIn('id', $request->ids)->delete();

        return redirect()->route('stock-counts.index')->with('success', 'Selected stock count sessions deleted successfully.');
    }
}
