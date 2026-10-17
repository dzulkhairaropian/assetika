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
use App\Services\AssetTicketService;
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

        $statuses = Status::all();
        $supervisors = User::with('department')->orderBy('name')->get();

        return view('pages.assets.management.checkin', compact('asset', 'statuses', 'supervisors'));
    }

    public function processCheckin(Request $request, $asset)
    {
        if (! ($asset instanceof Asset)) {
            $asset = Asset::findOrFail($asset);
        }

        $request->validate([
            'checkin_notes' => 'nullable|string',
            'condition_report' => 'required|string',
            'supervisor_user_id' => 'nullable|exists:users,id',
            'status_id' => 'required|exists:statuses,id',
            'checked_in_at' => 'required|date',
        ]);

        $this->assetService->processCheckin($asset, [
            'notes' => $request->checkin_notes,
            'condition_report' => $request->condition_report,
            'supervisor_user_id' => $request->supervisor_user_id,
            'status_id' => $request->status_id,
            'checkin_date' => $request->checked_in_at,
            'is_components_stripped' => $request->boolean('is_components_stripped'),
        ]);

        return redirect()->route('assets.show', $asset)->with('success', 'Asset checked in successfully.');
    }

    public function showBAST(Request $request, $asset, AssetCheckout $checkout)
    {
        if (! ($asset instanceof Asset)) {
            $asset = Asset::findOrFail($asset);
        }

        if ($checkout->asset_id !== $asset->id) {
            abort(404);
        }

        return BastService::generateBastPdf($asset, $checkout);
    }

    public function editBAST(Request $request, $asset, AssetCheckout $checkout)
    {
        if (! ($asset instanceof Asset)) {
            $asset = Asset::findOrFail($asset);
        }

        if ($checkout->asset_id !== $asset->id) {
            abort(404);
        }

        $users = User::orderBy('name')->get();
        $userOpts = $users->map(fn ($u) => ['id' => $u->id, 'text' => $u->name.($u->email ? ' ('.$u->email.')' : '')])->values()->all();

        return view('pages.assets.management.edit-bast', compact('asset', 'checkout', 'userOpts'));
    }

    public function updateBAST(Request $request, $asset, AssetCheckout $checkout)
    {
        if (! ($asset instanceof Asset)) {
            $asset = Asset::findOrFail($asset);
        }

        if ($checkout->asset_id !== $asset->id) {
            abort(404);
        }

        $validated = $request->validate([
            'checked_out_by_user_id' => 'required|exists:users,id',
            'checked_out_to_user_id' => 'required|exists:users,id',
            'supervisor_user_id' => 'nullable|exists:users,id',
            'checked_out_at' => 'required|date',
            'checkout_notes' => 'nullable|string',
        ]);

        $checkout->update($validated);

        if ($asset->current_checkout_id === $checkout->id) {
            $toUser = User::find($validated['checked_out_to_user_id']);
            $asset->update([
                'assigned_to_user_id' => $toUser?->id,
                'user_name' => $toUser?->name,
                'department_id' => $toUser?->department_id ?? $asset->department_id,
            ]);
        }

        return redirect()->route('assets.show', $asset)->with('success', 'BAST document updated successfully.');
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

        if ($maintenance->asset_id !== $asset->id) {
            abort(404);
        }

        return view('pages.assets.management.complete-maintenance', compact('asset', 'maintenance'));
    }

    public function processCompleteMaintenance(Request $request, $asset, AssetMaintenance $maintenance)
    {
        if (! ($asset instanceof Asset)) {
            $asset = Asset::findOrFail($asset);
        }

        if ($maintenance->asset_id !== $asset->id) {
            abort(404);
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

        // Close any open checkout so utilization history does not run indefinitely
        if ($asset->currentCheckout) {
            $asset->currentCheckout->update([
                'checked_in_at' => now(),
                'checked_in_by_user_id' => auth()->id(),
                'checkin_notes' => trim(($asset->currentCheckout->checkin_notes ? $asset->currentCheckout->checkin_notes.' ' : '').'Auto closed on asset retirement.'),
            ]);
        }

        $asset->update([
            'status_id' => $retiredStatus?->id ?? $asset->status_id,
            'retired_at' => now(),
            'retirement_reason' => $request->retirement_reason,
            'retirement_notes' => $request->retirement_notes,
            'assigned_to_user_id' => null,
            'user_name' => null,
            'current_checkout_id' => null,
        ]);

        AssetTicketService::completeOpenTicketsForRetiredAsset($asset, $request->retirement_reason, $request->retirement_notes);

        return redirect()->route('assets.show', $asset)->with('success', 'Asset retired successfully. All open tickets closed.');
    }
}
