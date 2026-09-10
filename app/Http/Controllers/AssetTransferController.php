<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetTransfer;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssetTransferController extends Controller
{
    public function index(Request $request)
    {
        $query = AssetTransfer::with([
            'asset',
            'fromCompany',
            'fromDepartment',
            'fromLocation',
            'toCompany',
            'toDepartment',
            'toLocation',
            'requestedBy',
            'approvedBy',
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('asset', function ($q) use ($search) {
                $q->where('asset_tag', 'like', "%{$search}%")
                    ->orWhere('device_name', 'like', "%{$search}%");
            });
        }

        $transfers = $query->latest()->paginate(50)->withQueryString();

        return view('pages.assets.transfers.index', compact('transfers'));
    }

    public function create(Request $request)
    {
        $assets = Asset::with('department.company')->availableForCheckout()->get();
        $companies = Company::orderBy('name')->get();
        $selectedAsset = $request->filled('asset_id') ? Asset::find($request->asset_id) : null;

        return view('pages.assets.transfers.create', compact('assets', 'companies', 'selectedAsset'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'to_company_id' => 'required|exists:companies,id',
            'to_department_id' => 'nullable|exists:departments,id',
            'to_location_id' => 'nullable|exists:locations,id',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $asset = Asset::with('department.company')->findOrFail($validated['asset_id']);

        $fromCompanyId = $asset->department?->company_id;
        $fromDepartmentId = $asset->department_id;
        $fromLocationId = $asset->location_id;

        $transfer = AssetTransfer::create([
            'asset_id' => $asset->id,
            'from_company_id' => $fromCompanyId,
            'from_department_id' => $fromDepartmentId,
            'from_location_id' => $fromLocationId,
            'to_company_id' => $validated['to_company_id'],
            'to_department_id' => $validated['to_department_id'] ?? null,
            'to_location_id' => $validated['to_location_id'] ?? null,
            'requested_by_user_id' => Auth::id(),
            'status' => 'pending',
            'reason' => $validated['reason'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('asset-transfers.index')->with('success', 'Asset transfer request submitted successfully.');
    }

    public function approve(Request $request, AssetTransfer $transfer)
    {
        if ($transfer->status !== 'pending') {
            return redirect()->back()->with('error', 'Transfer request is not pending.');
        }

        $transfer->update([
            'status' => 'approved',
            'approved_by_user_id' => Auth::id(),
            'transferred_at' => now(),
        ]);

        // Execute asset movement
        $asset = $transfer->asset;
        $asset->update([
            'department_id' => $transfer->to_department_id,
            'location_id' => $transfer->to_location_id,
        ]);

        return redirect()->route('asset-transfers.index')->with('success', 'Asset transfer approved and executed.');
    }

    public function reject(Request $request, AssetTransfer $transfer)
    {
        if ($transfer->status !== 'pending') {
            return redirect()->back()->with('error', 'Transfer request is not pending.');
        }

        $transfer->update([
            'status' => 'rejected',
            'approved_by_user_id' => Auth::id(),
            'notes' => $request->input('notes', $transfer->notes),
        ]);

        return redirect()->route('asset-transfers.index')->with('success', 'Asset transfer request rejected.');
    }
}
