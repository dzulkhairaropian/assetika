<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Location;
use App\Models\StockCount;
use App\Models\StockCountItem;
use Illuminate\Http\Request;

class StockCountController extends Controller
{
    public function index(Request $request)
    {
        $query = StockCount::with(['location', 'createdBy'])->orderByDesc('start_date');

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $stockCounts = $query->paginate(50)->withQueryString();

        return view('pages.stock-counts.index', compact('stockCounts'));
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
        $item->update([
            'found_status' => request('found_status'),
            'verified_by_user_id' => auth()->id(),
            'verified_at' => now(),
            'notes' => request('notes'),
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
}
