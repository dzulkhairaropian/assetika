<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCheckout;
use App\Models\AssetMaintenance;
use App\Models\User;
use App\Models\Location;
use App\Models\Department;
use App\Models\Status;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class AssetManagementController extends Controller
{
    public function showCheckout(Request $request, $asset)
    {
        if (!($asset instanceof Asset)) {
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
        if (!($asset instanceof Asset)) {
            $asset = Asset::findOrFail($asset);
        }

        $request->validate([
            'checked_out_to_user_id' => 'required|exists:users,id',
            'location_id' => 'required|exists:locations,id',
            'department_id' => 'required|exists:departments,id',
            'supervisor_user_id' => 'nullable|exists:users,id',
            'checked_out_at' => 'required|date',
            'checkout_notes' => 'nullable|string',
            'checkout_evidence' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
        ]);

        $evidencePath = null;
        if ($request->hasFile('checkout_evidence')) {
            $evidencePath = $request->file('checkout_evidence')->store('checkouts', 'public');
        }

        $checkout = AssetCheckout::create([
            'asset_id' => $asset->id,
            'checked_out_by_user_id' => auth()->id(),
            'checked_out_to_user_id' => $request->checked_out_to_user_id,
            'supervisor_user_id' => $request->supervisor_user_id,
            'department_id' => $request->department_id,
            'location_id' => $request->location_id,
            'checked_out_at' => $request->checked_out_at,
            'checkout_notes' => $request->checkout_notes,
            'checkout_evidence_file_path' => $evidencePath,
            'checkout_evidence_file_name' => $request->file('checkout_evidence')?->getClientOriginalName(),
        ]);

        $deployedStatus = Status::where('name', 'Deployed')->first();

        $asset->update([
            'current_checkout_id' => $checkout->id,
            'assigned_to_user_id' => $request->checked_out_to_user_id,
            'location_id' => $request->location_id,
            'department_id' => $request->department_id,
            'status_id' => $deployedStatus ? $deployedStatus->id : $asset->status_id,
        ]);

        $toUser = User::find($request->checked_out_to_user_id);
        if ($toUser) {
            $toUser->notify(new \App\Notifications\SystemNotification(
                'Asset Checked Out',
                "Asset '{$asset->tag} - " . ($asset->product->name ?? 'Item') . "' has been checked out to you.",
                route('assets.show', $asset->id),
                'arrow-up-right',
                'Asset'
            ));
        }

        return redirect()->route('assets.show', $asset)->with('success', 'Asset checked out successfully.');
    }

    public function showCheckin(Request $request, $asset)
    {
        if (!($asset instanceof Asset)) {
            $asset = Asset::findOrFail($asset);
        }

        if (!$asset->isCurrentlyCheckedOut()) {
            return redirect()->back()->with('error', 'Asset is not checked out.');
        }

        return view('pages.assets.management.checkin', compact('asset'));
    }

    public function processCheckin(Request $request, $asset)
    {
        if (!($asset instanceof Asset)) {
            $asset = Asset::findOrFail($asset);
        }

        $request->validate([
            'checked_in_at' => 'required|date',
            'condition_report' => 'required|string',
            'checkin_notes' => 'nullable|string',
            'checkin_evidence' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
        ]);

        $checkout = $asset->currentCheckout;

        $evidencePath = null;
        if ($request->hasFile('checkin_evidence')) {
            $evidencePath = $request->file('checkin_evidence')->store('checkins', 'public');
        }

        $checkout->update([
            'checked_in_at' => $request->checked_in_at,
            'checked_in_by_user_id' => auth()->id(),
            'condition_report' => $request->condition_report,
            'checkin_notes' => $request->checkin_notes,
            'checkin_evidence_file_path' => $evidencePath,
            'checkin_evidence_file_name' => $request->file('checkin_evidence')?->getClientOriginalName(),
        ]);

        $readyStatus = Status::where('name', 'Ready to Deploy')->first();

        $toUserId = $checkout->checked_out_to_user_id;

        $asset->update([
            'current_checkout_id' => null,
            'assigned_to_user_id' => null,
            'status_id' => $readyStatus ? $readyStatus->id : $asset->status_id,
        ]);

        $toUser = User::find($toUserId);
        if ($toUser) {
            $toUser->notify(new \App\Notifications\SystemNotification(
                'Asset Checked In',
                "Asset '{$asset->tag} - " . ($asset->product->name ?? 'Item') . "' has been checked in / returned.",
                route('assets.show', $asset->id),
                'arrow-down-left',
                'Asset'
            ));
        }

        return redirect()->route('assets.show', $asset)->with('success', 'Asset checked in successfully.');
    }

    public function showBAST(Request $request, $asset, AssetCheckout $checkout)
    {
        if (!($asset instanceof Asset)) {
            $asset = Asset::findOrFail($asset);
        }

        $giver = $checkout->checkedOutByUser ?? auth()->user();
        $receiver = $checkout->checkedOutToUser;
        $receiver_supervisor = $checkout->supervisor ?? $receiver;

        $giver_position = $giver->position->name ?? 'IT Administrator';
        $receiver_position = $receiver->position->name ?? '-';
        $receiver_supervisor_position = $receiver_supervisor->position->name ?? '-';

        $companyCode = $receiver->company?->code ?? ($giver->company?->code ?? "DB");
        $companyName = $receiver->company?->name ?? ($giver->company?->name ?? "PT. INVENTORY SYSTEM");

        // Determine if checkout is returned
        if ($checkout->checked_in_at !== null) {
            $returnedAt = $checkout->checked_in_at;
            $yearVal = $returnedAt->year;
            $sequence = AssetCheckout::whereYear("checked_in_at", $yearVal)
                ->where("id", "<=", $checkout->id)
                ->whereNotNull("checked_in_at")
                ->count();

            $data = [
                "sequence_number" => str_pad((string)$sequence, 3, "0", STR_PAD_LEFT),
                "company_code" => $companyCode,
                "company_name" => $companyName,
                "checkout" => $checkout,
                "day" => $returnedAt->translatedFormat("l"),
                "date" => $returnedAt->translatedFormat("d"),
                "month" => $returnedAt->translatedFormat("F"),
                "year" => $returnedAt->translatedFormat("Y"),
                "asset" => $asset,
                "user" => $receiver,
                "user_position" => $receiver_position,
                "admin" => $giver,
                "admin_position" => $giver_position,
                "receiver" => $receiver,
                "receiver_position" => $receiver_position,
                "receiver_supervisor" => $receiver_supervisor,
                "receiver_supervisor_position" => $receiver_supervisor_position,
            ];

            $pdf = Pdf::loadView("pdf.bast_checkin", $data);
            return $pdf->stream("BAST-IT-RETURN-{$receiver->name}-{$returnedAt->format("Ymd")}.pdf");
        } else {
            $checkedOutAt = $checkout->checked_out_at ?? now();
            $yearVal = $checkedOutAt->year;
            $sequence = AssetCheckout::whereYear("checked_out_at", $yearVal)
                ->where("id", "<=", $checkout->id)
                ->count();

            $data = [
                "sequence_number" => str_pad((string)$sequence, 3, "0", STR_PAD_LEFT),
                "company_code" => $companyCode,
                "company_name" => $companyName,
                "checkout" => $checkout,
                "day" => $checkedOutAt->translatedFormat("l"),
                "date" => $checkedOutAt->translatedFormat("d"),
                "month" => $checkedOutAt->translatedFormat("F"),
                "year" => $checkedOutAt->translatedFormat("Y"),
                "asset" => $asset,
                "giver" => $giver,
                "giver_position" => $giver_position,
                "receiver" => $receiver,
                "receiver_position" => $receiver_position,
                "receiver_supervisor" => $receiver_supervisor,
                "receiver_supervisor_position" => $receiver_supervisor_position,
            ];

            $pdf = Pdf::loadView("pdf.bast", $data);
            return $pdf->stream("BAST-IT-{$receiver->name}-{$checkedOutAt->format("Ymd")}.pdf");
        }
    }

    public function showMaintenance(Request $request, $asset)
    {
        if (!($asset instanceof Asset)) {
            $asset = Asset::findOrFail($asset);
        }

        $vendors = Vendor::all();

        return view('pages.assets.management.maintenance', compact('asset', 'vendors'));
    }

    public function processMaintenance(Request $request, $asset)
    {
        if (!($asset instanceof Asset)) {
            $asset = Asset::findOrFail($asset);
        }

        $request->validate([
            'service_provider' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'cost' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'evidence' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'notes' => 'nullable|string',
        ]);

        if ($asset->isCurrentlyCheckedOut()) {
            $checkout = $asset->currentCheckout;
            $checkout->update([
                'checked_in_at' => $request->start_date ?? now(),
                'checked_in_by_user_id' => auth()->id(),
                'condition_report' => 'Sent to maintenance: ' . ($request->description ?? 'No description'),
                'checkin_notes' => 'Auto-checkin from maintenance dispatch. ' . ($request->notes ?? ''),
            ]);

            $asset->update([
                'current_checkout_id' => null,
                'assigned_to_user_id' => null,
            ]);
        }

        $evidencePath = null;
        if ($request->hasFile('evidence')) {
            $evidencePath = $request->file('evidence')->store('maintenances', 'public');
        }

        AssetMaintenance::create([
            'asset_id' => $asset->id,
            'service_provider' => $request->service_provider,
            'start_date' => $request->start_date,
            'cost' => $request->cost,
            'description' => $request->description,
            'evidence_file_path' => $evidencePath,
            'notes' => $request->notes,
        ]);

        $repairStatus = Status::where('name', 'In Repair')->orWhere('name', 'Service')->first();

        $asset->update([
            'status_id' => $repairStatus ? $repairStatus->id : $asset->status_id,
        ]);

        $auth = auth()->user();
        if ($auth) {
            $auth->notify(new \App\Notifications\SystemNotification(
                'Asset Sent to Maintenance',
                "Asset '{$asset->tag} - " . ($asset->product->name ?? 'Item') . "' is now In Repair.",
                route('assets.show', $asset->id),
                'wrench',
                'Maintenance'
            ));
        }

        return redirect()->route('assets.show', $asset)->with('success', 'Asset sent to maintenance successfully.');
    }

    public function showCompleteMaintenance(Request $request, $asset, AssetMaintenance $maintenance)
    {
        if (!($asset instanceof Asset)) {
            $asset = Asset::findOrFail($asset);
        }

        return view('pages.assets.management.complete-maintenance', compact('asset', 'maintenance'));
    }

    public function processCompleteMaintenance(Request $request, $asset, AssetMaintenance $maintenance)
    {
        if (!($asset instanceof Asset)) {
            $asset = Asset::findOrFail($asset);
        }

        $request->validate([
            'completion_date' => 'required|date|after_or_equal:start_date',
            'cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $maintenance->update([
            'completion_date' => $request->completion_date,
            'cost' => $request->cost ?? $maintenance->cost,
            'notes' => $request->notes ?? $maintenance->notes,
        ]);

        $availableStatus = Status::where('name', 'Available')->first(); // Use 'Available' instead of 'Ready to Deploy'

        $asset->update([
            'status_id' => $availableStatus ? $availableStatus->id : $asset->status_id,
        ]);

        $auth = auth()->user();
        if ($auth) {
            $auth->notify(new \App\Notifications\SystemNotification(
                'Maintenance Completed',
                "Maintenance for '{$asset->tag} - " . ($asset->product->name ?? 'Item') . "' has been completed.",
                route('assets.show', $asset->id),
                'check-circle',
                'Maintenance'
            ));
        }

        return redirect()->route('assets.show', $asset)->with('success', 'Asset maintenance completed successfully.');
    }
}
