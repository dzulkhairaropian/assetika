<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Component;
use Illuminate\Http\Request;

class ComponentController extends Controller
{
    public function index(Request $request)
    {
        $query = Component::query()->with('category');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('serial', 'like', "%$search%")
                    ->orWhere('model_number', 'like', "%$search%")
                    ->orWhereHas('category', fn($c) => $c->where('name', 'like', "%$search%"));
            });
        }

        $components = $query->latest()->paginate(50)->withQueryString();

        return view('pages.components.index', compact('components'));
    }

    public function create()
    {
        $categories = \App\Models\Category::whereHas('masterCategory', function ($q) {
            $q->where('name', 'IT Component');
        })->orderBy('name')->get();
        $brands = \App\Models\Brand::orderBy('name')->get();
        $locations = \App\Models\Location::orderBy('name')->get();

        return view('pages.components.create', compact('categories', 'brands', 'locations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'manufacturer' => 'nullable|string|max:255',
            'model_number' => 'nullable|string|max:255',
            'serial' => 'nullable|string|max:255',
            'qty' => 'required|integer|min:0',
            'min_qty' => 'nullable|integer|min:0',
            'purchase_cost' => 'nullable|numeric|min:0',
            'location_id' => 'nullable|exists:locations,id',
            'notes' => 'nullable|string',
        ]);

        Component::create($validated);

        if ($request->has('create_another')) {
            return redirect()->route('components.create')->with('success', 'Component created successfully. Add another one.');
        }

        return redirect()->route('components.index')->with('success', 'Component created successfully.');
    }

    public function show(Component $component)
    {
        $component->load(['category', 'location', 'assets']);
        $available = $component->availableQty();
        $checkouts = $component->assets()
            ->withPivot(['qty', 'checked_out_at'])
            ->orderBy('component_asset.created_at', 'desc')
            ->get();

        return view('pages.components.show', compact('component', 'available', 'checkouts'));
    }

    public function edit(Component $component)
    {
        $categories = \App\Models\Category::whereHas('masterCategory', function ($q) {
            $q->where('name', 'IT Component');
        })->orderBy('name')->get();
        $brands = \App\Models\Brand::orderBy('name')->get();
        $locations = \App\Models\Location::orderBy('name')->get();

        return view('pages.components.edit', compact('component', 'categories', 'brands', 'locations'));
    }

    public function update(Request $request, Component $component)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'manufacturer' => 'nullable|string|max:255',
            'model_number' => 'nullable|string|max:255',
            'serial' => 'nullable|string|max:255',
            'qty' => 'required|integer|min:0',
            'min_qty' => 'nullable|integer|min:0',
            'purchase_cost' => 'nullable|numeric|min:0',
            'location_id' => 'nullable|exists:locations,id',
            'notes' => 'nullable|string',
        ]);

        $component->update($validated);

        return redirect()->route('components.index')->with('success', 'Component updated successfully.');
    }

    public function destroy(Component $component)
    {
        $component->delete();

        return redirect()->route('components.index')->with('success', 'Component deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:components,id',
        ]);

        Component::whereIn('id', $request->ids)->delete();

        return redirect()->route('components.index')->with('success', 'Selected components deleted successfully.');
    }

    public function checkout(Request $request, Component $component)
    {
        $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'qty' => 'required|integer|min:1',
        ]);

        $available = $component->availableQty();
        if ($request->qty > $available) {
            return back()->with('error', "Insufficient stock. Available: {$available}.");
        }

        $asset = Asset::findOrFail($request->asset_id);

        $existing = $component->assets()->where('asset_id', $asset->id)->first();
        if ($existing) {
            $component->assets()->updateExistingPivot($asset->id, [
                'qty' => $existing->pivot->qty + $request->qty,
                'checked_out_at' => now(),
            ]);
        } else {
            $component->assets()->attach($asset->id, [
                'qty' => $request->qty,
                'checked_out_at' => now(),
            ]);
        }

        return back()->with('success', "{$request->qty}x {$component->name} checked out to {$asset->name}.");
    }

    public function checkin(Request $request, Component $component, Asset $asset)
    {
        $checkout = $component->assets()->where('asset_id', $asset->id)->first();

        if (!$checkout) {
            return back()->with('error', 'Asset does not have this component checked out.');
        }

        $request->validate([
            'qty' => 'required|integer|min:1|max:' . $checkout->pivot->qty,
        ]);

        if ($request->qty < $checkout->pivot->qty) {
            $component->assets()->updateExistingPivot($asset->id, [
                'qty' => $checkout->pivot->qty - $request->qty,
            ]);
        } else {
            $component->assets()->detach($asset->id);
        }

        return back()->with('success', "{$request->qty}x {$component->name} checked in from {$asset->name}.");
    }
}
