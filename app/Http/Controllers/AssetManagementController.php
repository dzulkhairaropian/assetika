<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCheckout;
use App\Models\AssetMaintenance;
use App\Models\Department;
use App\Models\Location;
use App\Models\Status;
use App\Models\User;
use App\Models\Vendor;
use App\Services\AssetService;
use App\Services\BastService;
use Illuminate\Http\Request;

class AssetManagementController extends Controller
{
    protected AssetService $assetService;

    public function __construct(AssetService $assetService)
    {
        $this->assetService = $assetService;
    }

    public function showCheckout(Request $request, $asset)
    {
        if (! ($asset instanceof Asset)) {
            $asset = Asset::findOrFail($asset);
        }

        if ($asset->isCurrentlyCheckedOut()) {
            return redirect()->back()->with('error', 'Asset is already checked out.');
        }

        $users = User::with(['department', 'location'])->orderBy('name')->get();
        $locations = Location::all();
        $departments = Department::all();
        $supervisors = User::with(['department', 'company'])->orderBy('name')->get();

        return view('pages.assets.management.checkout', compact('asset', 'users', 'locations', 'departments', 'supervisors'));
    }

    public function processCheckout(Request $request, $asset)
    {
        if (! ($asset instanceof Asset)) {
            $asset = Asset::findOrFail($asset);
        }

        $request->validate([
            'checked_out_to_user_id' => 'required|exists:users,id',
            'location_id' => 'required|exists:locations,id',
            'department_id' => 'required|exists:departments,id',
            'supervisor_user_id' => 'nullable|exists:users,id',
            'checked_out_at' => 'required|date',
            'checkout_notes' => 'nullable|string',
        ]);

        $toUser = User::findOrFail($request->checked_out_to_user_id);

        $this->assetService->processCheckout($asset, $toUser, [
            'location_id' => $request->location_id,
            'checkout_date' => $request->checked_out_at,
            'notes' => $request->checkout_notes,
        ]);

        return redirect()->route('assets.show', $asset)->with('success', 'Asset checked out successfully.');
    }

    public function showCheckin(Request $request, $asset)
    {
        if (! ($asset instanceof Asset)) {
            $asset = Asset::findOrFail($asset);
        }

        if (! $asset->isCurrentlyCheckedOut()) {
            return redirect()->back()->with('error', 'Asset is not checked out.');
        }

        return view('pages.assets.management.checkin', compact('asset'));
    }

    public function processCheckin(Request $request, $asset)
    {
        if (! ($asset instanceof Asset)) {
            $asset = Asset::findOrFail($asset);
        }

        $request->validate([
            'checkin_notes' => 'nullable|string',
            'condition_report' => 'required|string',
        ]);

        $this->assetService->processCheckin($asset, [
            'notes' => $request->checkin_notes,
            'condition_report' => $request->condition_report,
        ]);

        return redirect()->route('assets.show', $asset)->with('success', 'Asset checked in successfully.');
    }

    public function showBAST(Request $request, $asset, AssetCheckout $checkout)
    {
        if (! ($asset instanceof Asset)) {
            $asset = Asset::findOrFail($asset);
        }

        return BastService::generateBastPdf($asset, $checkout);
    }

    public function showMaintenance(Request $request, $asset)
    {
        if (! ($asset instanceof Asset)) {
            $asset = Asset::findOrFail($asset);
        }

        $vendors = Vendor::orderBy('name')->get();

        return view('pages.assets.management.maintenance', compact('asset', 'vendors'));
    }

    public function processMaintenance(Request $request, $asset)
    {
        if (! ($asset instanceof Asset)) {
            $asset = Asset::findOrFail($asset);
        }

        $request->validate([
            'service_provider' => 'required|string|max:255',
            'description' => 'required|string',
            'cost' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'is_components_stripped' => 'nullable|boolean',
        ]);

        $this->assetService->processMaintenance($asset, $request->all());

        return redirect()->route('assets.show', $asset)->with('success', 'Asset sent to maintenance successfully. Ticket created.');
    }

    public function showCompleteMaintenance(Request $request, $asset, AssetMaintenance $maintenance)
    {
        if (! ($asset instanceof Asset)) {
            $asset = Asset::findOrFail($asset);
        }

        return view('pages.assets.management.complete-maintenance', compact('asset', 'maintenance'));
    }

    public function processCompleteMaintenance(Request $request, $asset, AssetMaintenance $maintenance)
    {
        if (! ($asset instanceof Asset)) {
            $asset = Asset::findOrFail($asset);
        }

        $request->validate([
            'completion_date' => 'required|date',
            'cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'resolution_notes' => 'nullable|string',
        ]);

        $this->assetService->processCompleteMaintenance($asset, $maintenance, $request->all());

        return redirect()->route('assets.show', $asset)->with('success', 'Asset maintenance completed successfully.');
    }

    public function processRetire(Request $request, $asset)
    {
        if (! ($asset instanceof Asset)) {
            $asset = Asset::findOrFail($asset);
        }

        $request->validate([
            'retirement_reason' => 'required|string|max:255',
            'retirement_notes' => 'nullable|string',
        ]);

        $retiredStatus = Status::where('name', 'Retired')->first();

        $asset->update([
            'status_id' => $retiredStatus?->id ?? $asset->status_id,
            'retired_at' => now(),
            'retirement_reason' => $request->retirement_reason,
            'retirement_notes' => $request->retirement_notes,
            'assigned_to_user_id' => null,
            'user_name' => null,
            'current_checkout_id' => null,
        ]);

        return redirect()->route('assets.show', $asset)->with('success', 'Asset retired successfully.');
    }
}
