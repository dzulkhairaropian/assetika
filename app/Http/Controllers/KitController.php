<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\Kit;
use App\Models\License;
use App\Models\User;
use Illuminate\Http\Request;

class KitController extends Controller
{
    public function index(Request $request)
    {
        $query = Kit::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%$search%");
        }

        $kits = $query->latest()->paginate(50)->withQueryString();

        return view('pages.kits.index', compact('kits'));
    }

    public function create()
    {
        $assets = Asset::orderBy('device_name')->get();
        $licenses = License::orderBy('name')->get();
        $components = Component::orderBy('name')->get();
        $consumables = Consumable::orderBy('name')->get();

        return view('pages.kits.create', compact('assets', 'licenses', 'components', 'consumables'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assets' => 'nullable|array',
            'assets.*.id' => 'exists:assets,id',
            'assets.*.qty' => 'nullable|integer|min:1',
            'licenses' => 'nullable|array',
            'licenses.*.id' => 'exists:licenses,id',
            'licenses.*.qty' => 'nullable|integer|min:1',
            'components' => 'nullable|array',
            'components.*.id' => 'exists:components,id',
            'components.*.qty' => 'nullable|integer|min:1',
            'consumables' => 'nullable|array',
            'consumables.*.id' => 'exists:consumables,id',
            'consumables.*.qty' => 'nullable|integer|min:1',
        ]);

        $kit = Kit::create(['name' => $validated['name'], 'description' => $request->description]);

        if ($request->has('assets')) {
            $sync = [];
            foreach ($request->assets as $item) {
                if (!empty($item['id'])) $sync[$item['id']] = ['qty' => $item['qty'] ?? 1];
            }
            $kit->assets()->sync($sync);
        }
        if ($request->has('licenses')) {
            $sync = [];
            foreach ($request->licenses as $item) {
                if (!empty($item['id'])) $sync[$item['id']] = ['qty' => $item['qty'] ?? 1];
            }
            $kit->licenses()->sync($sync);
        }
        if ($request->has('components')) {
            $sync = [];
            foreach ($request->components as $item) {
                if (!empty($item['id'])) $sync[$item['id']] = ['qty' => $item['qty'] ?? 1];
            }
            $kit->components()->sync($sync);
        }
        if ($request->has('consumables')) {
            $sync = [];
            foreach ($request->consumables as $item) {
                if (!empty($item['id'])) $sync[$item['id']] = ['qty' => $item['qty'] ?? 1];
            }
            $kit->consumables()->sync($sync);
        }

        if ($request->has('create_another')) {
            return redirect()->route('kits.create')->with('success', 'Kit created successfully. Add another one.');
        }

        return redirect()->route('kits.index')->with('success', 'Kit created successfully.');
    }

    public function show(Kit $kit)
    {
        $kit->load(['assets', 'licenses', 'components', 'consumables']);

        return view('pages.kits.show', compact('kit'));
    }

    public function edit(Kit $kit)
    {
        $kit->load(['assets', 'licenses', 'components', 'consumables']);
        $assets = Asset::orderBy('device_name')->get();
        $licenses = License::orderBy('name')->get();
        $components = Component::orderBy('name')->get();
        $consumables = Consumable::orderBy('name')->get();

        return view('pages.kits.edit', compact('kit', 'assets', 'licenses', 'components', 'consumables'));
    }

    public function update(Request $request, Kit $kit)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $kit->update($validated);

        $syncAssets = [];
        if ($request->has('assets')) {
            foreach ($request->assets as $item) {
                if (!empty($item['id'])) $syncAssets[$item['id']] = ['qty' => $item['qty'] ?? 1];
            }
        }
        $kit->assets()->sync($syncAssets);

        $syncLicenses = [];
        if ($request->has('licenses')) {
            foreach ($request->licenses as $item) {
                if (!empty($item['id'])) $syncLicenses[$item['id']] = ['qty' => $item['qty'] ?? 1];
            }
        }
        $kit->licenses()->sync($syncLicenses);

        $syncComponents = [];
        if ($request->has('components')) {
            foreach ($request->components as $item) {
                if (!empty($item['id'])) $syncComponents[$item['id']] = ['qty' => $item['qty'] ?? 1];
            }
        }
        $kit->components()->sync($syncComponents);

        $syncConsumables = [];
        if ($request->has('consumables')) {
            foreach ($request->consumables as $item) {
                if (!empty($item['id'])) $syncConsumables[$item['id']] = ['qty' => $item['qty'] ?? 1];
            }
        }
        $kit->consumables()->sync($syncConsumables);

        return redirect()->route('kits.index')->with('success', 'Kit updated successfully.');
    }

    public function destroy(Kit $kit)
    {
        $kit->delete();

        return redirect()->route('kits.index')->with('success', 'Kit deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:kits,id',
        ]);

        Kit::whereIn('id', $request->ids)->delete();

        return redirect()->route('kits.index')->with('success', 'Selected kits deleted successfully.');
    }

    public function checkoutForm(Kit $kit)
    {
        $kit->load(['assets', 'licenses', 'components', 'consumables']);
        $users = User::orderBy('name')->get();

        return view('pages.kits.checkout', compact('kit', 'users'));
    }

    public function checkout(Request $request, Kit $kit)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $errors = [];
        $success = [];

        foreach ($kit->assets as $asset) {
            if ($asset->isCurrentlyCheckedOut()) {
                $errors[] = "Asset {$asset->name} is already checked out.";
                continue;
            }

            $checkout = $asset->checkouts()->create([
                'checked_out_to_user_id' => $user->id,
                'checked_out_by_user_id' => auth()->id(),
                'checked_out_at' => now(),
                'checkout_notes' => "Checked out via Kit: {$kit->name}",
            ]);

            $asset->update([
                'assigned_to_user_id' => $user->id,
                'current_checkout_id' => $checkout->id,
            ]);

            $success[] = "Asset {$asset->name} checked out.";
        }

        foreach ($kit->licenses as $license) {
            $available = $license->availableSeats();
            if ($available <= 0) {
                $errors[] = "License {$license->name} has no available seats.";
                continue;
            }

            $license->seats()->create([
                'assigned_to_type' => User::class,
                'assigned_to_id' => $user->id,
                'checked_out_at' => now(),
                'notes' => "Checked out via Kit: {$kit->name}",
            ]);

            $success[] = "License {$license->name} assigned.";
        }

        if ($errors) {
            session()->flash('error', implode(' ', $errors));
        }

        session()->flash('success', implode(' ', $success) ?: 'Kit checkout processed.');

        return redirect()->route('kits.show', $kit);
    }
}
