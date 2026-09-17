<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Brand;
use App\Models\License;
use App\Models\User;
use App\Services\LicenseService;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    protected LicenseService $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    public function index(Request $request)
    {
        $query = License::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('product_key', 'like', "%$search%")
                    ->orWhere('manufacturer', 'like', "%$search%");
            });
        }

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100, 250, 500]) ? (int) $request->input('per_page') : 50;
        $licenses = $query->latest()->paginate($perPage)->withQueryString();

        return view('pages.licenses.index', compact('licenses'));
    }

    public function create()
    {
        $brands = Brand::orderBy('name')->get();
        $assets = Asset::with('product.brand')->orderBy('asset_tag')->get();

        $brandOpts = $brands->map(fn ($b) => ['id' => $b->name, 'text' => $b->name])->values()->all();
        $assetOpts = $assets->map(fn ($a) => ['id' => $a->id, 'text' => $a->asset_tag.' - '.($a->product?->name ?? $a->device_name)])->values()->all();

        return view('pages.licenses.create', compact('brandOpts', 'assetOpts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'product_key' => 'nullable|string|max:255',
            'seats' => 'required|integer|min:1',
            'license_email' => 'nullable|email|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'expiration_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'asset_id' => 'nullable|exists:assets,id',
        ]);

        $assetId = $validated['asset_id'] ?? null;
        unset($validated['asset_id']);

        $license = License::create($validated);

        if ($assetId) {
            $license->seats()->create([
                'assigned_to_type' => Asset::class,
                'assigned_to_id' => $assetId,
                'checked_out_at' => now(),
                'notes' => 'Assigned during license creation',
            ]);
        }

        if ($request->has('create_another')) {
            return redirect()->route('licenses.create')->with('success', 'License created successfully. Add another one.');
        }

        return redirect()->route('licenses.index')->with('success', 'License created successfully.');
    }

    public function show(License $license)
    {
        $license->load('seats.assignedTo');
        $users = User::orderBy('name')->get();
        $assets = Asset::with('product.brand')->orderBy('asset_tag')->get();

        $userOpts = $users->map(fn ($u) => ['id' => $u->id, 'text' => $u->name.' ('.$u->email.')'])->values()->all();
        $assetOpts = $assets->map(fn ($a) => ['id' => $a->id, 'text' => $a->asset_tag.' - '.($a->product?->name ?? $a->device_name)])->values()->all();

        return view('pages.licenses.show', compact('license', 'userOpts', 'assetOpts'));
    }

    public function edit(License $license)
    {
        $brands = Brand::orderBy('name')->get();
        $brandOpts = $brands->map(fn ($b) => ['id' => $b->name, 'text' => $b->name])->values()->all();

        return view('pages.licenses.edit', compact('license', 'brandOpts'));
    }

    public function update(Request $request, License $license)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'product_key' => 'nullable|string|max:255',
            'seats' => 'required|integer|min:1',
            'license_email' => 'nullable|email|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'expiration_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $license->update($validated);

        return redirect()->route('licenses.index')->with('success', 'License updated successfully.');
    }

    public function destroy(License $license)
    {
        $license->delete();

        return redirect()->route('licenses.index')->with('success', 'License deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:licenses,id',
        ]);

        License::whereIn('id', $request->ids)->delete();

        return redirect()->route('licenses.index')->with('success', 'Selected licenses deleted successfully.');
    }

    public function checkout(Request $request, License $license)
    {
        $request->validate([
            'assigned_to_type' => 'required|in:user,asset',
            'assigned_to_id' => 'required|integer',
            'notes' => 'nullable|string',
        ]);

        $available = $license->availableSeats();
        if ($available <= 0) {
            return back()->with('error', 'No available seats for this license.');
        }

        $type = $request->assigned_to_type === 'user' ? User::class : Asset::class;
        $entity = $type::findOrFail($request->assigned_to_id);

        $license->seats()->create([
            'assigned_to_type' => $type,
            'assigned_to_id' => $entity->id,
            'checked_out_at' => now(),
            'notes' => $request->notes,
        ]);

        $label = $request->assigned_to_type === 'user' ? $entity->name : $entity->asset_tag;

        return back()->with('success', "License seat assigned to {$label}.");
    }

    public function checkin(License $license, $seatId)
    {
        $seat = $license->seats()->findOrFail($seatId);
        $seat->delete();

        return back()->with('success', 'License seat checked in successfully.');
    }
}
