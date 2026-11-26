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
use Illuminate\Support\Carbon;

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

        $blockedStatusNames = ['broken', 'service', 'in repair', 'service & repair', 'maintenance', 'retired', 'disposed', 'disposal', 'sold'];
        if (in_array(strtolower((string) ($asset->status?->name ?? '')), $blockedStatusNames, true)) {
            return redirect()->route('assets.show', $asset)->with('error', "Cannot check out asset while its status is {$asset->status->name}.");
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
            'expected_return_date' => 'nullable|date',
            'checkout_notes' => 'nullable|string',
            'checkout_evidence' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $toUser = User::findOrFail($request->checked_out_to_user_id);

        try {
            $this->assetService->processCheckout($asset, $toUser, [
                'location_id' => $request->location_id,
                'department_id' => $request->department_id,
                'supervisor_user_id' => $request->supervisor_user_id,
                'checkout_date' => $request->checked_out_at,
                'expected_return_date' => $request->expected_return_date,
                'notes' => $request->checkout_notes,
            ], $request->file('checkout_evidence'));
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

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

        $statuses = Status::whereHas('masterCategory', function ($q) {
            $q->where('name', 'IT Asset');
        })->get();
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
            'checkin_evidence' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $this->assetService->processCheckin($asset, [
            'notes' => $request->checkin_notes,
            'condition_report' => $request->condition_report,
            'supervisor_user_id' => $request->supervisor_user_id,
            'status_id' => $request->status_id,
            'checkin_date' => $request->checked_in_at,
            'is_components_stripped' => $request->boolean('is_components_stripped'),
        ], $request->file('checkin_evidence'));

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

    public function downloadLoanForm(Request $request)
    {
        $assetIds = $request->input('asset_ids', []);
        if (is_string($assetIds)) {
            $assetIds = explode(',', $assetIds);
        }

        return BastService::generateLoanFormPdf(null, [
            'asset_ids' => $assetIds,
            'borrower_name' => $request->input('borrower_name'),
            'borrower_contact' => $request->input('borrower_contact'),
            'department' => $request->input('department'),
            'location' => $request->input('location'),
            'loan_date' => $request->input('loan_date'),
            'return_date' => $request->input('return_date'),
            'purpose' => $request->input('purpose'),
        ]);
    }

    public function downloadCheckoutLoanForm(Request $request, $asset, AssetCheckout $checkout)
    {
        if (! ($asset instanceof Asset)) {
            $asset = Asset::findOrFail($asset);
        }

        if ($checkout->asset_id !== $asset->id) {
            abort(404);
        }

        return BastService::generateLoanFormPdf($checkout);
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
            'service_provider' => 'required|string|max:255|exists:vendors,name',
            'description' => 'required|string',
            'cost' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'is_components_stripped' => 'nullable|boolean',
            'evidence' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $this->assetService->processMaintenance($asset, $request->all(), $request->file('evidence'));

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

        $statuses = Status::whereHas('masterCategory', fn ($q) => $q->where('name', 'IT Asset'))
            ->orderBy('name')
            ->get();
        if ($statuses->isEmpty()) {
            $statuses = Status::orderBy('name')->get();
        }

        return view('pages.assets.management.complete-maintenance', compact('asset', 'maintenance', 'statuses'));
    }

    public function processCompleteMaintenance(Request $request, $asset, AssetMaintenance $maintenance)
    {
        if (! ($asset instanceof Asset)) {
            $asset = Asset::findOrFail($asset);
        }

        if ($maintenance->asset_id !== $asset->id) {
            abort(404);
        }

        $validated = $request->validate([
            'completion_date' => 'required|date',
            'cost' => 'nullable|numeric|min:0',
            'status_id' => 'required|exists:statuses,id',
            'notes' => 'nullable|string',
            'resolution_notes' => 'nullable|string',
            'is_components_stripped' => 'nullable|boolean',
            'evidence' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $this->assetService->processCompleteMaintenance($asset, $maintenance, [
            'completion_date' => $validated['completion_date'],
            'cost' => $validated['cost'] ?? null,
            'status_id' => $validated['status_id'],
            'notes' => $validated['notes'] ?? null,
            'resolution_notes' => $validated['resolution_notes'] ?? null,
            'is_components_stripped' => $request->boolean('is_components_stripped'),
        ], $request->file('evidence'));

        return redirect()->route('assets.show', $asset)->with('success', 'Asset maintenance completed successfully.');
    }

    public function processRetire(Request $request, $asset)
    {
        if (! ($asset instanceof Asset)) {
            $asset = Asset::findOrFail($asset);
        }

        $validated = $request->validate([
            'retired_at' => 'nullable|date',
            'retirement_reason' => 'required|string|max:255',
            'retirement_notes' => 'nullable|string',
        ]);

        $retiredStatus = Status::where('name', 'Retired')->first();
        $retiredDate = ! empty($validated['retired_at']) ? Carbon::parse($validated['retired_at']) : now();

        // Close any open checkout so utilization history does not run indefinitely
        if ($asset->currentCheckout) {
            $asset->currentCheckout->update([
                'checked_in_at' => $retiredDate,
                'checked_in_by_user_id' => auth()->id(),
                'checkin_notes' => trim(($asset->currentCheckout->checkin_notes ? $asset->currentCheckout->checkin_notes.' ' : '').'Auto closed on asset retirement.'),
            ]);
        }

        $asset->update([
            'status_id' => $retiredStatus?->id ?? $asset->status_id,
            'retired_at' => $retiredDate,
            'retirement_reason' => $validated['retirement_reason'],
            'retirement_notes' => $validated['retirement_notes'] ?? null,
            'assigned_to_user_id' => null,
            'user_name' => null,
            'current_checkout_id' => null,
        ]);

        AssetTicketService::completeOpenTicketsForRetiredAsset($asset, $validated['retirement_reason'], $validated['retirement_notes'] ?? null);

        return redirect()->route('assets.show', $asset)->with('success', 'Asset retired successfully. All open tickets closed.');
    }

    public function processReplacement(Request $request, $asset)
    {
        if (! ($asset instanceof Asset)) {
            $asset = Asset::findOrFail($asset);
        }

        $validated = $request->validate([
            'replacement_asset_id' => 'required|exists:assets,id',
            'checkin_status_id' => 'required|exists:statuses,id',
            'replacement_mode' => 'required|in:permanent,loaner',
            'replacement_date' => 'required|date',
            'expected_return_date' => 'nullable|required_if:replacement_mode,loaner|date',
            'condition_report' => 'required|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        $replacementAsset = Asset::findOrFail($validated['replacement_asset_id']);

        try {
            $result = $this->assetService->replaceAsset($asset, $replacementAsset, $validated);
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with(
            'success',
            "Asset {$asset->asset_tag} replaced with {$replacementAsset->asset_tag} for {$result['user']->name}."
        );
    }

    public function processBroken(Request $request, $asset)
    {
        if (! ($asset instanceof Asset)) {
            $asset = Asset::findOrFail($asset);
        }

        $validated = $request->validate([
            'broken_date' => 'nullable|date',
            'priority' => 'nullable|in:Low,Medium,High,Critical',
            'description' => 'required|string|max:1000',
            'checkin_from_holder' => 'nullable|boolean',
            'is_components_stripped' => 'nullable|boolean',
            'save_to_notes' => 'nullable|boolean',
        ]);

        $ticket = $this->assetService->markAsBroken($asset, [
            'broken_date' => $validated['broken_date'] ?? null,
            'priority' => $validated['priority'] ?? 'High',
            'description' => $validated['description'],
            'checkin_from_holder' => $request->boolean('checkin_from_holder'),
            'is_components_stripped' => $request->boolean('is_components_stripped'),
            'save_to_notes' => $request->boolean('save_to_notes'),
        ]);

        return redirect()->route('assets.show', $asset)->with(
            'success',
            "Asset {$asset->asset_tag} marked as Broken and Ticket #{$ticket->request_id} created automatically."
        );
    }
}
