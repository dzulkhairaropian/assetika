<?php

namespace App\Http\Controllers;

use App\Models\GaAsset;
use App\Models\GaAssetCategory;
use App\Models\GaAssetSubcategory;
use App\Models\GaAssetAssignment;
use App\Models\GaMaintenanceLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class GaAssetController extends Controller
{
    public function index(Request $request)
    {
        $query = GaAsset::with(['category', 'subcategory', 'currentHolder']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('asset_code', 'like', "%$search%")
                  ->orWhere('serial_number', 'like', "%$search%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->get('filter') === 'tax_due') {
            $query->whereHas('vehicle', function($v) {
                $v->whereNotNull('tax_due_date')->where('tax_due_date', '<=', now()->addDays(30));
            });
        } elseif ($request->get('filter') === 'warranty_expiring') {
            $query->whereNotNull('warranty_expiry_date')->where('warranty_expiry_date', '<=', now()->addDays(30));
        }

        $assets = $query->latest()->paginate(25)->withQueryString();
        $categories = GaAssetCategory::all();

        $taxDueVehicles = \App\Models\GaAssetVehicle::with('asset')
            ->whereNotNull('tax_due_date')
            ->where('tax_due_date', '<=', now()->addDays(30))
            ->get();
        $taxDueVehiclesCount = $taxDueVehicles->count();

        $warrantyExpiringAssets = GaAsset::whereNotNull('warranty_expiry_date')
            ->where('warranty_expiry_date', '<=', now()->addDays(30))
            ->get();
        $warrantyExpiringCount = $warrantyExpiringAssets->count();

        return view('pages.ga-assets.index', compact('assets', 'categories', 'taxDueVehiclesCount', 'warrantyExpiringCount', 'taxDueVehicles', 'warrantyExpiringAssets'));
    }

    public function create()
    {
        $categories = GaAssetCategory::all();
        $subcategories = GaAssetSubcategory::all();
        $statuses = ['Available', 'Deployed', 'In Repair', 'Disposed'];
        
        return view('pages.ga-assets.create', compact('categories', 'subcategories', 'statuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'qty' => 'required|integer|min:1',
            'asset_code' => 'required|string|max:50|unique:ga_assets,asset_code',
            'inventory_number' => 'nullable|string|max:100',
            'category_id' => 'required|exists:ga_asset_categories,id',
            'subcategory_id' => 'nullable|exists:ga_asset_subcategories,id',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'status' => 'required|in:Available,Deployed,In Repair,Disposed',
            'acquisition_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 5),
            'acquisition_source' => 'required|in:Pembelian,Hibah,Sewa,Lainnya',
            'purchase_date' => 'required|date',
            'vendor_name' => 'nullable|string|max:150',
            'invoice_number' => 'nullable|string|max:100',
            'purchase_price' => 'required|numeric|min:0',
            'additional_cost' => 'nullable|numeric|min:0',
            'warranty_period_month' => 'nullable|integer|min:0',
            'warranty_expiry_date' => 'nullable|date',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('ga-assets', 'public');
        }

        // Calculate total_asset_value for DB
        $validated['total_asset_value'] = $validated['purchase_price'] + ($validated['additional_cost'] ?? 0);

        $asset = GaAsset::create($validated);

        // Vehicle details handler
        if ($request->filled('vehicle.license_plate') || $request->filled('vehicle.chassis_number')) {
            $vehicleData = $request->validate([
                'vehicle.license_plate' => 'nullable|string|max:50',
                'vehicle.chassis_number' => 'nullable|string|max:100',
                'vehicle.engine_number' => 'nullable|string|max:100',
                'vehicle.bpkb_number' => 'nullable|string|max:100',
                'vehicle.stnk_number' => 'nullable|string|max:100',
                'vehicle.tax_due_date' => 'nullable|date',
                'vehicle.plate_expiry_date' => 'nullable|date',
                'vehicle.last_service_date' => 'nullable|date',
                'vehicle.last_odometer' => 'nullable|integer',
                'vehicle.insurance_policy_number' => 'nullable|string|max:100',
                'vehicle.insurance_expiry_date' => 'nullable|date',
                'vehicle.fuel_consumption' => 'nullable|numeric',
            ]);

            $asset->vehicle()->create($vehicleData['vehicle']);
        }

        return redirect()->route('ga-assets.index')->with('success', 'GA Asset created successfully.');
    }

    public function show(GaAsset $ga_asset)
    {
        $ga_asset->load(['category', 'subcategory', 'vehicle', 'assignments.user', 'maintenanceLogs']);
        $users = User::all();
        return view('pages.ga-assets.show', ['asset' => $ga_asset, 'users' => $users]);
    }

    public function edit(GaAsset $ga_asset)
    {
        $ga_asset->load('vehicle');
        $categories = GaAssetCategory::all();
        $subcategories = GaAssetSubcategory::where('category_id', $ga_asset->category_id)->get();
        $statuses = ['Available', 'Deployed', 'In Repair', 'Disposed'];

        return view('pages.ga-assets.edit', [
            'asset' => $ga_asset,
            'ga_asset' => $ga_asset,
            'categories' => $categories,
            'subcategories' => $subcategories,
            'statuses' => $statuses
        ]);
    }

    public function update(Request $request, GaAsset $ga_asset)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'qty' => 'required|integer|min:1',
            'asset_code' => 'required|string|max:50|unique:ga_assets,asset_code,' . $ga_asset->id,
            'inventory_number' => 'nullable|string|max:100',
            'category_id' => 'required|exists:ga_asset_categories,id',
            'subcategory_id' => 'nullable|exists:ga_asset_subcategories,id',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'status' => 'required|in:Available,Deployed,In Repair,Disposed',
            'acquisition_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 5),
            'acquisition_source' => 'required|in:Pembelian,Hibah,Sewa,Lainnya',
            'purchase_date' => 'required|date',
            'vendor_name' => 'nullable|string|max:150',
            'invoice_number' => 'nullable|string|max:100',
            'purchase_price' => 'required|numeric|min:0',
            'additional_cost' => 'nullable|numeric|min:0',
            'warranty_period_month' => 'nullable|integer|min:0',
            'warranty_expiry_date' => 'nullable|date',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($ga_asset->image) {
                Storage::disk('public')->delete($ga_asset->image);
            }
            $validated['image'] = $request->file('image')->store('ga-assets', 'public');
        }

        // Calculate total_asset_value for DB
        $validated['total_asset_value'] = $validated['purchase_price'] + ($validated['additional_cost'] ?? 0);

        $ga_asset->update($validated);

        // Vehicle details handler
        $vehicleData = $request->validate([
            'vehicle.license_plate' => 'nullable|string|max:50',
            'vehicle.chassis_number' => 'nullable|string|max:100',
            'vehicle.engine_number' => 'nullable|string|max:100',
            'vehicle.bpkb_number' => 'nullable|string|max:100',
            'vehicle.stnk_number' => 'nullable|string|max:100',
            'vehicle.tax_due_date' => 'nullable|date',
            'vehicle.plate_expiry_date' => 'nullable|date',
            'vehicle.last_service_date' => 'nullable|date',
            'vehicle.last_odometer' => 'nullable|integer',
            'vehicle.insurance_policy_number' => 'nullable|string|max:100',
            'vehicle.insurance_expiry_date' => 'nullable|date',
            'vehicle.fuel_consumption' => 'nullable|numeric',
        ]);

        if ($request->filled('vehicle.license_plate') || $request->filled('vehicle.chassis_number')) {
            $ga_asset->vehicle()->updateOrCreate([], $vehicleData['vehicle']);
        } else {
            $ga_asset->vehicle()->delete();
        }

        return redirect()->route('ga-assets.index')->with('success', 'GA Asset updated successfully.');
    }

    public function destroy(GaAsset $ga_asset)
    {
        if ($ga_asset->image) {
            Storage::disk('public')->delete($ga_asset->image);
        }
        $ga_asset->delete();

        return redirect()->route('ga-assets.index')->with('success', 'GA Asset deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:ga_assets,id',
        ]);

        $assets = GaAsset::whereIn('id', $request->ids)->get();
        foreach ($assets as $asset) {
            if ($asset->image) {
                Storage::disk('public')->delete($asset->image);
            }
            $asset->delete();
        }

        return redirect()->route('ga-assets.index')->with('success', 'Selected GA Assets deleted successfully.');
    }

    public function checkout(Request $request, GaAsset $asset)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'assigned_at' => 'required|date',
            'notes' => 'nullable|string',
            'checkout_evidence' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('checkout_evidence')) {
            $validated['checkout_evidence'] = $request->file('checkout_evidence')->store('ga-evidence', 'public');
        }

        $validated['checked_out_by_user_id'] = auth()->id();

        $asset->assignments()->create($validated);
        $asset->update([
            'status' => 'Deployed',
            'current_holder_id' => $validated['user_id']
        ]);

        return redirect()->route('ga-assets.show', $asset)->with('success', 'Asset checked out successfully.');
    }

    public function checkin(Request $request, GaAsset $asset)
    {
        $validated = $request->validate([
            'returned_at' => 'required|date',
            'status' => 'required|string|in:Available,Broken,Maintenance',
            'condition_on_return' => 'required|string|max:50',
            'notes' => 'nullable|string',
            'checkin_evidence' => 'nullable|image|max:2048',
        ]);

        $status = $validated['status'];
        unset($validated['status']);

        $assignment = $asset->assignments()->whereNull('returned_at')->latest()->first();

        if ($assignment) {
            if ($request->hasFile('checkin_evidence')) {
                $validated['checkin_evidence'] = $request->file('checkin_evidence')->store('ga-evidence', 'public');
            }
            $validated['checked_in_by_user_id'] = auth()->id();
            $assignment->update($validated);
        }

        $asset->update([
            'status' => $status,
            'current_holder_id' => null
        ]);

        return redirect()->route('ga-assets.show', $asset)->with('success', 'Asset checked in successfully.');
    }

    public function maintenance(Request $request, GaAsset $asset)
    {
        $validated = $request->validate([
            'type' => 'required|in:Repair,Routine',
            'cost' => 'required|numeric|min:0',
            'vendor' => 'nullable|string|max:255',
            'scheduled_date' => 'required|date',
            'completed_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $asset->maintenanceLogs()->create($validated);

        if (empty($validated['completed_date'])) {
            $asset->update(['status' => 'In Repair']);
        } else {
            $asset->update(['status' => 'Available']);
        }

        return redirect()->route('ga-assets.show', $asset)->with('success', 'Maintenance logged successfully.');
    }

    public function renewVehicleTax(Request $request, GaAsset $asset)
    {
        $validated = $request->validate([
            'renewal_type' => 'required|in:tax,plate,both',
            'tax_due_date' => 'nullable|required_if:renewal_type,tax,both|date',
            'plate_expiry_date' => 'nullable|required_if:renewal_type,plate,both|date',
            'stnk_number' => 'nullable|string|max:100',
            'cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($asset->vehicle) {
            $data = [];
            if (!empty($validated['tax_due_date'])) {
                $data['tax_due_date'] = $validated['tax_due_date'];
            }
            if (!empty($validated['plate_expiry_date'])) {
                $data['plate_expiry_date'] = $validated['plate_expiry_date'];
            }
            if (!empty($validated['stnk_number'])) {
                $data['stnk_number'] = $validated['stnk_number'];
            }
            $asset->vehicle->update($data);
        }

        if (!empty($validated['cost']) && $validated['cost'] > 0) {
            $typeLabel = match($validated['renewal_type']) {
                'tax' => 'Perpanjangan Pajak STNK (1 Thn)',
                'plate' => 'Perpanjangan Plat/Kaleng (5 Thn)',
                'both' => 'Perpanjangan Pajak & Plat Kaleng (5 Thn)',
            };
            $asset->maintenanceLogs()->create([
                'type' => 'Routine',
                'cost' => $validated['cost'],
                'scheduled_date' => now(),
                'completed_date' => now(),
                'notes' => $typeLabel . ($validated['notes'] ? ': ' . $validated['notes'] : ''),
            ]);
        }

        return redirect()->route('ga-assets.show', $asset)->with('success', 'Perpanjangan STNK / Pajak / Plat Kaleng berhasil dicatat.');
    }

    public function getSubcategoriesAjax(Request $request)
    {
        $categoryId = $request->get('category_id');
        $subcategories = GaAssetSubcategory::where('category_id', $categoryId)->get(['id', 'name']);
        return response()->json($subcategories);
    }

    public function showBAST(GaAsset $asset, GaAssetAssignment $assignment)
    {
        $assignment->load(['user.department', 'user.company', 'checkedOutByUser']);
        $asset->load(['category', 'subcategory', 'vehicle']);

        $pdf = Pdf::loadView('pdf.ga_bast', compact('asset', 'assignment'));
        return $pdf->stream("BAST-GA-{$asset->asset_code}-{$assignment->id}.pdf");
    }
}
