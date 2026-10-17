<?php

namespace App\Http\Controllers;

use App\Models\Component;
use App\Models\Consumable;
use App\Models\Kit;
use App\Models\License;
use App\Models\User;
use App\Services\KitService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class KitController extends Controller
{
    protected KitService $kitService;

    public function __construct(KitService $kitService)
    {
        $this->kitService = $kitService;
    }

    public function index(Request $request)
    {
        $query = Kit::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%$search%");
        }

        $summary = [
            'total' => Kit::count(),
            'today' => Kit::whereDate('created_at', Carbon::today())->count(),
        ];

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100, 250, 500]) ? (int) $request->input('per_page') : 50;
        $kits = $query->latest()->paginate($perPage)->withQueryString();

        return view('pages.kits.index', compact('kits', 'summary'));
    }

    public function create()
    {
        $licenses = License::orderBy('name')->get();
        $components = Component::orderBy('name')->get();
        $consumables = Consumable::with('product')->get();

        $licenseOpts = $licenses->map(fn ($l) => ['id' => $l->id, 'text' => $l->name.' ('.$l->seats.' seats)'])->values()->all();
        $componentOpts = $components->map(fn ($c) => ['id' => $c->id, 'text' => ($c->component_tag ? '['.$c->component_tag.'] ' : '').$c->name])->values()->all();
        $consumableOpts = $consumables->map(fn ($con) => ['id' => $con->id, 'text' => $con->product?->name ?? 'Consumable'])->values()->all();

        return view('pages.kits.create', compact('licenseOpts', 'componentOpts', 'consumableOpts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
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

        $kit = $this->kitService->createKit(
            ['name' => $validated['name'], 'description' => $request->description],
            $request->only(['licenses', 'components', 'consumables'])
        );

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
        $kit->load(['licenses', 'components', 'consumables']);
        $licenses = License::orderBy('name')->get();
        $components = Component::orderBy('name')->get();
        $consumables = Consumable::with('product')->get();

        $licenseOpts = $licenses->map(fn ($l) => ['id' => $l->id, 'text' => $l->name.' ('.$l->seats.' seats)'])->values()->all();
        $componentOpts = $components->map(fn ($c) => ['id' => $c->id, 'text' => ($c->component_tag ? '['.$c->component_tag.'] ' : '').$c->name])->values()->all();
        $consumableOpts = $consumables->map(fn ($con) => ['id' => $con->id, 'text' => $con->product?->name ?? 'Consumable'])->values()->all();

        return view('pages.kits.edit', compact('kit', 'licenseOpts', 'componentOpts', 'consumableOpts'));
    }

    public function update(Request $request, Kit $kit)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assets' => 'nullable|array',
            'assets.*.id' => 'required|integer|exists:assets,id',
            'assets.*.qty' => 'nullable|integer|min:1',
            'licenses' => 'nullable|array',
            'licenses.*.id' => 'required|integer|exists:licenses,id',
            'licenses.*.qty' => 'nullable|integer|min:1',
            'components' => 'nullable|array',
            'components.*.id' => 'required|integer|exists:components,id',
            'components.*.qty' => 'nullable|integer|min:1',
            'consumables' => 'nullable|array',
            'consumables.*.id' => 'required|integer|exists:consumables,id',
            'consumables.*.qty' => 'nullable|integer|min:1',
        ]);

        $kit->update($validated);

        $syncAssets = [];
        if ($request->has('assets')) {
            foreach ($request->assets as $item) {
                if (! empty($item['id'])) {
                    $syncAssets[$item['id']] = ['qty' => $item['qty'] ?? 1];
                }
            }
        }
        $kit->assets()->sync($syncAssets);

        $syncLicenses = [];
        if ($request->has('licenses')) {
            foreach ($request->licenses as $item) {
                if (! empty($item['id'])) {
                    $syncLicenses[$item['id']] = ['qty' => $item['qty'] ?? 1];
                }
            }
        }
        $kit->licenses()->sync($syncLicenses);

        $syncComponents = [];
        if ($request->has('components')) {
            foreach ($request->components as $item) {
                if (! empty($item['id'])) {
                    $syncComponents[$item['id']] = ['qty' => $item['qty'] ?? 1];
                }
            }
        }
        $kit->components()->sync($syncComponents);

        $syncConsumables = [];
        if ($request->has('consumables')) {
            foreach ($request->consumables as $item) {
                if (! empty($item['id'])) {
                    $syncConsumables[$item['id']] = ['qty' => $item['qty'] ?? 1];
                }
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
