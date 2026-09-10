<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCheckout;
use App\Models\AssetMaintenance;
use App\Models\Department;
use App\Models\Location;
use App\Models\Priority;
use App\Models\SlaStatus;
use App\Models\Source;
use App\Models\Status;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketStatus;
use App\Models\TicketSubcategory;
use App\Models\TicketType;
use App\Models\User;
use App\Models\Vendor;
use App\Notifications\SystemNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AssetManagementController extends Controller
{
    private function resolveTicketCategoryForAsset(?Asset $asset): array
    {
        if (! $asset) {
            $defaultCat = TicketCategory::first();

            return ['category_id' => $defaultCat?->id, 'subcategory_id' => null];
        }

        $assetCategoryName = optional(optional($asset->product)->category)->name ?? '';
        $assetCategoryCode = optional(optional($asset->product)->category)->code ?? '';

        $hardwareCat = TicketCategory::where('name', 'Hardware')->orWhere('code', 'HARDWARE')->first();
        $softwareCat = TicketCategory::where('name', 'Software')->orWhere('code', 'SOFTWARE')->first();

        $targetCat = $hardwareCat ?? TicketCategory::first();
        $targetSub = null;

        if ($targetCat && $targetCat->id === $hardwareCat?->id) {
            $subs = TicketSubcategory::where('ticket_category_id', $targetCat->id)->get();

            if (in_array($assetCategoryCode, ['NB', 'LAPTOP']) || str_contains(strtolower($assetCategoryName), 'notebook') || str_contains(strtolower($assetCategoryName), 'laptop')) {
                $targetSub = $subs->firstWhere('name', 'Laptop / Notebook');
            } elseif (in_array($assetCategoryCode, ['PC', 'DESKTOP']) || str_contains(strtolower($assetCategoryName), 'desktop') || str_contains(strtolower($assetCategoryName), 'pc')) {
                $targetSub = $subs->firstWhere('name', 'Desktop PC');
            } elseif (in_array($assetCategoryCode, ['PR', 'PRINTER', 'SCANNER']) || str_contains(strtolower($assetCategoryName), 'printer')) {
                $targetSub = $subs->firstWhere('name', 'Printer / Scanner');
            } elseif (in_array($assetCategoryCode, ['MON', 'MONITOR']) || str_contains(strtolower($assetCategoryName), 'monitor')) {
                $targetSub = $subs->firstWhere('name', 'Monitor / Display');
            } else {
                $targetSub = $subs->firstWhere('name', 'Peripherals (Mouse/Keyboard/Dock)');
            }
        }

        return [
            'category_id' => $targetCat?->id,
            'subcategory_id' => $targetSub?->id,
        ];
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
            $toUser->notify(new SystemNotification(
                'Asset Checked Out',
                "Asset '{$asset->tag} - ".($asset->product->name ?? 'Item')."' has been checked out to you.",
                route('assets.show', $asset->id),
                'arrow-up-right',
                'Asset'
            ));
        }

        // Automatic Ticket Creation for Checkout
        $ticketDate = Carbon::parse($request->checked_out_at);
        $reqType = TicketType::where('code', 'REQ')->first() ?? TicketType::first();
        $requestCode = strtoupper($reqType?->code ?: 'REQ');
        $prefix = $requestCode.'-'.$ticketDate->format('ymd').'-';

        $latestTicket = Ticket::where('request_id', 'like', $prefix.'%')
            ->orderByRaw('CAST(SUBSTRING_INDEX(request_id, "-", -1) AS UNSIGNED) DESC')
            ->first();

        $nextSeq = 1;
        if ($latestTicket) {
            $parts = explode('-', $latestTicket->request_id);
            $nextSeq = ((int) end($parts)) + 1;
        }

        $completedStatus = TicketStatus::where('name', 'Completed')->first() ?? TicketStatus::where('is_closed', true)->first();
        $fulfilledSla = SlaStatus::where('code', 'FULFILLED')->first();
        $mediumPriority = Priority::where('name', 'Medium')->first();
        $portalSource = Source::first();
        $catResolved = $this->resolveTicketCategoryForAsset($asset);

        Ticket::create([
            'request_id' => $prefix.str_pad((string) $nextSeq, 3, '0', STR_PAD_LEFT),
            'ticket_type_id' => $reqType?->id,
            'ticket_category_id' => $catResolved['category_id'],
            'ticket_subcategory_id' => $catResolved['subcategory_id'],
            'requester_user_id' => $toUser?->id,
            'requester_name' => $toUser?->name,
            'requester_email' => $toUser?->email,
            'requester_company_id' => $toUser?->company_id,
            'requester_department_id' => $toUser?->department_id,
            'assigned_to_id' => auth()->id(),
            'created_by_id' => auth()->id(),
            'asset_id' => $asset->id,
            'priority_id' => $mediumPriority?->id,
            'source_id' => $portalSource?->id,
            'status_id' => $completedStatus?->id,
            'sla_status_id' => $fulfilledSla?->id,
            'problem_description' => "[Auto-Generated] Checkout Asset: {$asset->asset_tag} - ".($asset->product->name ?? 'Asset').' ke '.($toUser?->name ?? 'User'),
            'resolution_notes' => 'Asset successfully checked out and handed over.',
            'request_date' => $ticketDate->toDateString(),
            'request_time' => $ticketDate,
            'completed_date' => $ticketDate->toDateString(),
            'completed_time' => $ticketDate,
            'created_at' => $ticketDate,
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

        $statuses = Status::whereHas('masterCategory', function ($q) {
            $q->where('name', 'IT Asset');
        })->get();

        return view('pages.assets.management.checkin', compact('asset', 'statuses'));
    }

    public function processCheckin(Request $request, $asset)
    {
        if (! ($asset instanceof Asset)) {
            $asset = Asset::findOrFail($asset);
        }

        $request->validate([
            'checked_in_at' => 'required|date',
            'status_id' => 'required|exists:statuses,id',
            'condition_report' => 'required|string',
            'checkin_notes' => 'nullable|string',
            'checkin_evidence' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'is_components_stripped' => 'nullable|boolean',
        ]);

        $checkout = $asset->currentCheckout;

        $evidencePath = null;
        if ($request->hasFile('checkin_evidence')) {
            $evidencePath = $request->file('checkin_evidence')->store('checkins', 'public');
        }

        $selectedStatus = Status::findOrFail($request->status_id);

        $checkout->update([
            'checked_in_at' => $request->checked_in_at,
            'checked_in_by_user_id' => auth()->id(),
            'condition_report' => $request->condition_report,
            'checkin_status_id' => $selectedStatus->id,
            'checkin_notes' => $request->checkin_notes,
            'checkin_evidence_file_path' => $evidencePath,
            'checkin_evidence_file_name' => $request->file('checkin_evidence')?->getClientOriginalName(),
        ]);

        $toUserId = $checkout->checked_out_to_user_id;

        $asset->update([
            'current_checkout_id' => null,
            'assigned_to_user_id' => null,
            'status_id' => $selectedStatus->id,
            'is_components_stripped' => $request->boolean('is_components_stripped'),
        ]);

        $toUser = User::find($toUserId);
        if ($toUser) {
            $toUser->notify(new SystemNotification(
                'Asset Checked In',
                "Asset '{$asset->tag} - ".($asset->product->name ?? 'Item')."' has been checked in / returned.",
                route('assets.show', $asset->id),
                'arrow-down-left',
                'Asset'
            ));
        }

        // Automatic Ticket Creation for Checkin
        $ticketDate = Carbon::parse($request->checked_in_at);
        $reqType = TicketType::where('code', 'REQ')->first() ?? TicketType::first();
        $requestCode = strtoupper($reqType?->code ?: 'REQ');
        $prefix = $requestCode.'-'.$ticketDate->format('ymd').'-';

        $latestTicket = Ticket::where('request_id', 'like', $prefix.'%')
            ->orderByRaw('CAST(SUBSTRING_INDEX(request_id, "-", -1) AS UNSIGNED) DESC')
            ->first();

        $nextSeq = 1;
        if ($latestTicket) {
            $parts = explode('-', $latestTicket->request_id);
            $nextSeq = ((int) end($parts)) + 1;
        }

        $completedStatus = TicketStatus::where('name', 'Completed')->first() ?? TicketStatus::where('is_closed', true)->first();
        $fulfilledSla = SlaStatus::where('code', 'FULFILLED')->first();
        $mediumPriority = Priority::where('name', 'Medium')->first();
        $portalSource = Source::first();
        $catResolved = $this->resolveTicketCategoryForAsset($asset);

        Ticket::create([
            'request_id' => $prefix.str_pad((string) $nextSeq, 3, '0', STR_PAD_LEFT),
            'ticket_type_id' => $reqType?->id,
            'ticket_category_id' => $catResolved['category_id'],
            'ticket_subcategory_id' => $catResolved['subcategory_id'],
            'requester_user_id' => $toUser?->id,
            'requester_name' => $toUser?->name,
            'requester_email' => $toUser?->email,
            'requester_company_id' => $toUser?->company_id,
            'requester_department_id' => $toUser?->department_id,
            'assigned_to_id' => auth()->id(),
            'created_by_id' => auth()->id(),
            'asset_id' => $asset->id,
            'priority_id' => $mediumPriority?->id,
            'source_id' => $portalSource?->id,
            'status_id' => $completedStatus?->id,
            'sla_status_id' => $fulfilledSla?->id,
            'problem_description' => "[Auto-Generated] Checkin Asset: {$asset->asset_tag} - ".($asset->product->name ?? 'Asset').' dari '.($toUser?->name ?? 'User').'. Kondisi: '.$request->condition_report,
            'resolution_notes' => 'Asset checked in and status updated to '.$selectedStatus->name.'.',
            'request_date' => $ticketDate->toDateString(),
            'request_time' => $ticketDate,
            'completed_date' => $ticketDate->toDateString(),
            'completed_time' => $ticketDate,
            'created_at' => $ticketDate,
        ]);

        return redirect()->route('assets.show', $asset)->with('success', 'Asset checked in successfully.');
    }

    public function showBAST(Request $request, $asset, AssetCheckout $checkout)
    {
        if (! ($asset instanceof Asset)) {
            $asset = Asset::findOrFail($asset);
        }

        $giver = $checkout->checkedOutByUser ?? auth()->user();
        $receiver = $checkout->checkedOutToUser;
        $receiver_supervisor = $checkout->supervisor ?? $receiver;

        $giver_position = $giver->position->name ?? 'IT Administrator';
        $receiver_position = $receiver->position->name ?? '-';
        $receiver_supervisor_position = $receiver_supervisor->position->name ?? '-';

        $companyCode = $receiver->company?->code ?? ($giver->company?->code ?? 'DB');
        $companyName = $receiver->company?->name ?? ($giver->company?->name ?? 'PT. INVENTORY SYSTEM');

        // Determine if checkout is returned
        if ($checkout->checked_in_at !== null) {
            $returnedAt = $checkout->checked_in_at;
            $yearVal = $returnedAt->year;
            $sequence = AssetCheckout::whereYear('checked_in_at', $yearVal)
                ->where('id', '<=', $checkout->id)
                ->whereNotNull('checked_in_at')
                ->count();

            $data = [
                'sequence_number' => str_pad((string) $sequence, 3, '0', STR_PAD_LEFT),
                'company_code' => $companyCode,
                'company_name' => $companyName,
                'checkout' => $checkout,
                'day' => $returnedAt->translatedFormat('l'),
                'date' => $returnedAt->translatedFormat('d'),
                'month' => $returnedAt->translatedFormat('F'),
                'year' => $returnedAt->translatedFormat('Y'),
                'asset' => $asset,
                'user' => $receiver,
                'user_position' => $receiver_position,
                'admin' => $giver,
                'admin_position' => $giver_position,
                'receiver' => $receiver,
                'receiver_position' => $receiver_position,
                'receiver_supervisor' => $receiver_supervisor,
                'receiver_supervisor_position' => $receiver_supervisor_position,
            ];

            $pdf = Pdf::loadView('pdf.bast_checkin', $data);

            return $pdf->stream("BAST-IT-RETURN-{$receiver->name}-{$returnedAt->format('Ymd')}.pdf");
        } else {
            $checkedOutAt = $checkout->checked_out_at ?? now();
            $yearVal = $checkedOutAt->year;
            $sequence = AssetCheckout::whereYear('checked_out_at', $yearVal)
                ->where('id', '<=', $checkout->id)
                ->count();

            $data = [
                'sequence_number' => str_pad((string) $sequence, 3, '0', STR_PAD_LEFT),
                'company_code' => $companyCode,
                'company_name' => $companyName,
                'checkout' => $checkout,
                'day' => $checkedOutAt->translatedFormat('l'),
                'date' => $checkedOutAt->translatedFormat('d'),
                'month' => $checkedOutAt->translatedFormat('F'),
                'year' => $checkedOutAt->translatedFormat('Y'),
                'asset' => $asset,
                'giver' => $giver,
                'giver_position' => $giver_position,
                'receiver' => $receiver,
                'receiver_position' => $receiver_position,
                'receiver_supervisor' => $receiver_supervisor,
                'receiver_supervisor_position' => $receiver_supervisor_position,
            ];

            $pdf = Pdf::loadView('pdf.bast', $data);

            return $pdf->stream("BAST-IT-{$receiver->name}-{$checkedOutAt->format('Ymd')}.pdf");
        }
    }

    public function showMaintenance(Request $request, $asset)
    {
        if (! ($asset instanceof Asset)) {
            $asset = Asset::findOrFail($asset);
        }

        $vendors = Vendor::all();

        return view('pages.assets.management.maintenance', compact('asset', 'vendors'));
    }

    public function processMaintenance(Request $request, $asset)
    {
        if (! ($asset instanceof Asset)) {
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
                'condition_report' => 'Sent to maintenance: '.($request->description ?? 'No description'),
                'checkin_notes' => 'Auto-checkin from maintenance dispatch. '.($request->notes ?? ''),
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
            $auth->notify(new SystemNotification(
                'Asset Sent to Maintenance',
                "Asset '{$asset->tag} - ".($asset->product->name ?? 'Item')."' is now In Repair.",
                route('assets.show', $asset->id),
                'wrench',
                'Maintenance'
            ));
        }

        // Automatic Ticket Creation for Sending to Service/Maintenance
        $ticketDate = Carbon::parse($request->start_date);
        $incType = TicketType::where('code', 'INC')->first() ?? TicketType::where('code', 'REQ')->first() ?? TicketType::first();
        $code = strtoupper($incType?->code ?: 'INC');
        $prefix = $code.'-'.$ticketDate->format('ymd').'-';

        $latestTicket = Ticket::where('request_id', 'like', $prefix.'%')
            ->orderByRaw('CAST(SUBSTRING_INDEX(request_id, "-", -1) AS UNSIGNED) DESC')
            ->first();

        $nextSeq = 1;
        if ($latestTicket) {
            $parts = explode('-', $latestTicket->request_id);
            $nextSeq = ((int) end($parts)) + 1;
        }

        $inProgressStatus = TicketStatus::where('name', 'In Progress')->first() ?? TicketStatus::first();
        $highPriority = Priority::where('name', 'High')->first() ?? Priority::first();
        $portalSource = Source::first();
        $catResolved = $this->resolveTicketCategoryForAsset($asset);

        Ticket::create([
            'request_id' => $prefix.str_pad((string) $nextSeq, 3, '0', STR_PAD_LEFT),
            'ticket_type_id' => $incType?->id,
            'ticket_category_id' => $catResolved['category_id'],
            'ticket_subcategory_id' => $catResolved['subcategory_id'],
            'requester_user_id' => auth()->id(),
            'requester_name' => auth()->user()?->name,
            'requester_email' => auth()->user()?->email,
            'requester_company_id' => auth()->user()?->company_id,
            'requester_department_id' => auth()->user()?->department_id,
            'assigned_to_id' => auth()->id(),
            'created_by_id' => auth()->id(),
            'asset_id' => $asset->id,
            'priority_id' => $highPriority?->id,
            'source_id' => $portalSource?->id,
            'status_id' => $inProgressStatus?->id,
            'problem_description' => "[Auto-Generated] Send to Service: {$asset->asset_tag} - ".($asset->product->name ?? 'Asset').". Service Provider: {$request->service_provider}. Problem: {$request->description}",
            'resolution_notes' => "Service in progress by {$request->service_provider}. Estimated cost: Rp ".number_format((float) $request->cost, 0, ',', '.'),
            'request_date' => $ticketDate->toDateString(),
            'request_time' => $ticketDate,
            'created_at' => $ticketDate,
        ]);

        return redirect()->route('assets.show', $asset)->with('success', 'Asset sent to maintenance successfully.');
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
            $auth->notify(new SystemNotification(
                'Maintenance Completed',
                "Maintenance for '{$asset->tag} - ".($asset->product->name ?? 'Item')."' has been completed.",
                route('assets.show', $asset->id),
                'check-circle',
                'Maintenance'
            ));
        }

        // Automatic Ticket Creation for Completed Service/Maintenance
        $ticketDate = Carbon::parse($request->completion_date);
        $reqType = TicketType::where('code', 'REQ')->first() ?? TicketType::first();
        $code = strtoupper($reqType?->code ?: 'REQ');
        $prefix = $code.'-'.$ticketDate->format('ymd').'-';

        $latestTicket = Ticket::where('request_id', 'like', $prefix.'%')
            ->orderByRaw('CAST(SUBSTRING_INDEX(request_id, "-", -1) AS UNSIGNED) DESC')
            ->first();

        $nextSeq = 1;
        if ($latestTicket) {
            $parts = explode('-', $latestTicket->request_id);
            $nextSeq = ((int) end($parts)) + 1;
        }

        $completedStatus = TicketStatus::where('name', 'Completed')->first() ?? TicketStatus::where('is_closed', true)->first();
        $fulfilledSla = SlaStatus::where('code', 'FULFILLED')->first();
        $mediumPriority = Priority::where('name', 'Medium')->first();
        $portalSource = Source::first();
        $catResolved = $this->resolveTicketCategoryForAsset($asset);

        Ticket::create([
            'request_id' => $prefix.str_pad((string) $nextSeq, 3, '0', STR_PAD_LEFT),
            'ticket_type_id' => $reqType?->id,
            'ticket_category_id' => $catResolved['category_id'],
            'ticket_subcategory_id' => $catResolved['subcategory_id'],
            'requester_user_id' => auth()->id(),
            'requester_name' => auth()->user()?->name,
            'requester_email' => auth()->user()?->email,
            'requester_company_id' => auth()->user()?->company_id,
            'requester_department_id' => auth()->user()?->department_id,
            'assigned_to_id' => auth()->id(),
            'created_by_id' => auth()->id(),
            'asset_id' => $asset->id,
            'priority_id' => $mediumPriority?->id,
            'source_id' => $portalSource?->id,
            'status_id' => $completedStatus?->id,
            'sla_status_id' => $fulfilledSla?->id,
            'problem_description' => "[Auto-Generated] Service Completed: {$asset->asset_tag} - ".($asset->product->name ?? 'Asset').'. Status: Ready/Available.',
            'resolution_notes' => "Service successfully completed on {$request->completion_date}. Final cost: Rp ".number_format((float) ($request->cost ?? $maintenance->cost), 0, ',', '.').'. Notes: '.($request->notes ?? '-'),
            'request_date' => $ticketDate->toDateString(),
            'request_time' => $ticketDate,
            'completed_date' => $ticketDate->toDateString(),
            'completed_time' => $ticketDate,
            'created_at' => $ticketDate,
        ]);

        return redirect()->route('assets.show', $asset)->with('success', 'Asset maintenance completed successfully.');
    }

    public function processRetire(Request $request, $asset)
    {
        if (! ($asset instanceof Asset)) {
            $asset = Asset::findOrFail($asset);
        }

        $request->validate([
            'retired_at' => 'required|date',
            'retirement_reason' => 'required|string',
            'retirement_notes' => 'nullable|string',
            'is_components_stripped' => 'nullable|boolean',
        ]);

        $retiredStatus = Status::where('name', 'Retired')->first()
            ?? Status::where('name', 'like', '%retired%')->first();

        if ($asset->isCurrentlyCheckedOut()) {
            $checkout = $asset->currentCheckout;
            if ($checkout) {
                $checkout->update([
                    'checked_in_at' => $request->retired_at,
                    'checked_in_by_user_id' => auth()->id(),
                    'condition_report' => 'Retired: '.$request->retirement_reason,
                    'checkin_status_id' => $retiredStatus?->id,
                    'checkin_notes' => 'Auto checked-in upon retirement: '.($request->retirement_notes ?? '-'),
                ]);
            }
        }

        $asset->update([
            'status_id' => $retiredStatus ? $retiredStatus->id : $asset->status_id,
            'retired_at' => $request->retired_at,
            'retirement_reason' => $request->retirement_reason,
            'retirement_notes' => $request->retirement_notes,
            'is_components_stripped' => $request->boolean('is_components_stripped'),
            'current_checkout_id' => null,
            'assigned_to_user_id' => null,
        ]);

        return redirect()->route('assets.show', $asset)->with('success', 'Asset has been successfully retired.');
    }
}
