<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetMaintenance;
use App\Models\Priority;
use App\Models\SlaStatus;
use App\Models\Source;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketStatus;
use App\Models\TicketSubcategory;
use App\Models\TicketType;
use App\Models\User;

class AssetTicketService
{
    public static function createCheckoutTicket(Asset $asset, ?User $toUser, ?string $notes = null): Ticket
    {
        $catResolved = static::resolveTicketCategoryForAsset($asset);
        $completedStatus = TicketStatus::where('name', 'Completed')->first() ?? TicketStatus::where('is_closed', true)->first();
        $fulfilledSla = SlaStatus::where('code', 'FULFILLED')->first();
        $mediumPriority = Priority::where('name', 'Medium')->first();
        $portalSource = Source::first();
        $reqType = TicketType::where('code', 'REQ')->first() ?? TicketType::first();

        $assetName = $asset->product?->name ?? $asset->device_name ?? 'Asset';
        $userName = $toUser?->name ?? 'User';
        $desc = "[Auto-Generated] Checkout Asset: {$asset->asset_tag} - {$assetName} ke {$userName}";
        if ($notes) {
            $desc .= "\nNotes: {$notes}";
        }

        return static::buildAndSaveTicket([
            'subject' => "Checkout Asset: {$asset->asset_tag} - {$assetName}",
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
            'problem_description' => $desc,
            'resolution_notes' => 'Asset successfully checked out and handed over.',
            'is_completed' => true,
        ]);
    }

    public static function createCheckinTicket(Asset $asset, ?User $fromUser, ?string $condition = null): Ticket
    {
        $catResolved = static::resolveTicketCategoryForAsset($asset);
        $completedStatus = TicketStatus::where('name', 'Completed')->first() ?? TicketStatus::where('is_closed', true)->first();
        $fulfilledSla = SlaStatus::where('code', 'FULFILLED')->first();
        $mediumPriority = Priority::where('name', 'Medium')->first();
        $portalSource = Source::first();
        $reqType = TicketType::where('code', 'REQ')->first() ?? TicketType::first();

        $assetName = $asset->product?->name ?? $asset->device_name ?? 'Asset';
        $userName = $fromUser?->name ?? 'User';
        $desc = "[Auto-Generated] Checkin Asset: {$asset->asset_tag} - {$assetName} dari {$userName}.";
        if ($condition) {
            $desc .= " Kondisi: {$condition}";
        }

        return static::buildAndSaveTicket([
            'subject' => "Checkin Asset: {$asset->asset_tag} - {$assetName}",
            'ticket_type_id' => $reqType?->id,
            'ticket_category_id' => $catResolved['category_id'],
            'ticket_subcategory_id' => $catResolved['subcategory_id'],
            'requester_user_id' => $fromUser?->id,
            'requester_name' => $fromUser?->name,
            'requester_email' => $fromUser?->email,
            'requester_company_id' => $fromUser?->company_id,
            'requester_department_id' => $fromUser?->department_id,
            'assigned_to_id' => auth()->id(),
            'created_by_id' => auth()->id(),
            'asset_id' => $asset->id,
            'priority_id' => $mediumPriority?->id,
            'source_id' => $portalSource?->id,
            'status_id' => $completedStatus?->id,
            'sla_status_id' => $fulfilledSla?->id,
            'problem_description' => $desc,
            'resolution_notes' => 'Asset successfully checked in and returned to stock.',
            'is_completed' => true,
        ]);
    }

    public static function createMaintenanceTicket(Asset $asset, string $provider, string $description): Ticket
    {
        $catResolved = static::resolveTicketCategoryForAsset($asset);
        $inProgressStatus = TicketStatus::where('name', 'In Progress')->first() ?? TicketStatus::where('is_closed', false)->first();
        $highPriority = Priority::where('name', 'High')->first() ?? Priority::first();
        $portalSource = Source::first();
        $incType = TicketType::where('code', 'INC')->first() ?? TicketType::first();

        $assetName = $asset->product?->name ?? $asset->device_name ?? 'Asset';
        $user = $asset->assignedToUser ?? auth()->user();

        return static::buildAndSaveTicket([
            'subject' => "Send to Service: {$asset->asset_tag} - {$assetName}",
            'ticket_type_id' => $incType?->id,
            'ticket_category_id' => $catResolved['category_id'],
            'ticket_subcategory_id' => $catResolved['subcategory_id'],
            'requester_user_id' => $user?->id,
            'requester_name' => $user?->name,
            'requester_email' => $user?->email,
            'requester_company_id' => $user?->company_id,
            'requester_department_id' => $user?->department_id,
            'assigned_to_id' => auth()->id(),
            'created_by_id' => auth()->id(),
            'asset_id' => $asset->id,
            'priority_id' => $highPriority?->id,
            'source_id' => $portalSource?->id,
            'status_id' => $inProgressStatus?->id,
            'problem_description' => "[Auto-Generated] Send to Service: {$asset->asset_tag} - {$assetName}. Service Provider: {$provider}. Problem: {$description}",
            'is_completed' => false,
        ]);
    }

    public static function createCompleteMaintenanceTicket(Asset $asset, AssetMaintenance $maintenance): Ticket
    {
        $catResolved = static::resolveTicketCategoryForAsset($asset);
        $completedStatus = TicketStatus::where('name', 'Completed')->first() ?? TicketStatus::where('is_closed', true)->first();
        $fulfilledSla = SlaStatus::where('code', 'FULFILLED')->first();
        $mediumPriority = Priority::where('name', 'Medium')->first();
        $portalSource = Source::first();
        $reqType = TicketType::where('code', 'REQ')->first() ?? TicketType::first();

        $assetName = $asset->product?->name ?? $asset->device_name ?? 'Asset';
        $user = $asset->assignedToUser ?? auth()->user();

        // Close any existing open "Send to Service" tickets for this asset
        $openServiceTickets = Ticket::where('asset_id', $asset->id)
            ->where('problem_description', 'like', '%[Auto-Generated] Send to Service%')
            ->whereHas('status', fn ($q) => $q->where('is_closed', false))
            ->get();

        foreach ($openServiceTickets as $openTicket) {
            $openTicket->update([
                'status_id' => $completedStatus?->id ?? $openTicket->status_id,
                'sla_status_id' => $fulfilledSla?->id ?? $openTicket->sla_status_id,
                'resolution_notes' => "Service completed: {$maintenance->notes}. Provider: {$maintenance->service_provider}. Cost: Rp ".number_format($maintenance->cost, 0, ',', '.'),
                'completed_date' => now()->toDateString(),
                'completed_time' => now(),
            ]);
        }

        return static::buildAndSaveTicket([
            'subject' => "Service Completed: {$asset->asset_tag} - {$assetName}",
            'ticket_type_id' => $reqType?->id,
            'ticket_category_id' => $catResolved['category_id'],
            'ticket_subcategory_id' => $catResolved['subcategory_id'],
            'requester_user_id' => $user?->id,
            'requester_name' => $user?->name,
            'requester_email' => $user?->email,
            'requester_company_id' => $user?->company_id,
            'requester_department_id' => $user?->department_id,
            'assigned_to_id' => auth()->id(),
            'created_by_id' => auth()->id(),
            'asset_id' => $asset->id,
            'priority_id' => $mediumPriority?->id,
            'source_id' => $portalSource?->id,
            'status_id' => $completedStatus?->id,
            'sla_status_id' => $fulfilledSla?->id,
            'problem_description' => "[Auto-Generated] Service Completed: {$asset->asset_tag} - {$assetName}. Status: Ready/Available.",
            'resolution_notes' => "Maintenance completed: {$maintenance->notes}. Provider: {$maintenance->service_provider}. Cost: Rp ".number_format($maintenance->cost, 0, ',', '.'),
            'is_completed' => true,
        ]);
    }

    public static function completeOpenTicketsForRetiredAsset(Asset $asset, string $reason, ?string $notes = null): void
    {
        $completedStatus = TicketStatus::where('name', 'Completed')->first() ?? TicketStatus::where('is_closed', true)->first();
        $fulfilledSla = SlaStatus::where('code', 'FULFILLED')->first();

        $openTickets = Ticket::where('asset_id', $asset->id)
            ->whereHas('status', fn ($q) => $q->where('is_closed', false))
            ->get();

        foreach ($openTickets as $ticket) {
            $ticket->update([
                'status_id' => $completedStatus?->id ?? $ticket->status_id,
                'sla_status_id' => $fulfilledSla?->id ?? $ticket->sla_status_id,
                'resolution_notes' => "Ticket closed automatically because linked asset {$asset->asset_tag} was retired. Reason: {$reason}".($notes ? " ({$notes})" : ''),
                'completed_date' => now()->toDateString(),
                'completed_time' => now(),
            ]);
        }
    }

    private static function buildAndSaveTicket(array $data): Ticket
    {
        $ticketDate = now();
        $ticketType = ! empty($data['ticket_type_id']) ? TicketType::find($data['ticket_type_id']) : null;
        $requestCode = strtoupper($ticketType?->code ?: 'REQ');
        $prefix = $requestCode.'-'.$ticketDate->format('ymd').'-';

        $latestTicket = Ticket::where('request_id', 'like', $prefix.'%')
            ->orderBy('id', 'desc')
            ->first();

        $nextSeq = 1;
        if ($latestTicket) {
            $parts = explode('-', $latestTicket->request_id);
            $nextSeq = ((int) end($parts)) + 1;
        }

        $data['request_id'] = $prefix.str_pad((string) $nextSeq, 3, '0', STR_PAD_LEFT);
        $data['request_date'] = $ticketDate->toDateString();
        $data['request_time'] = $ticketDate;
        $data['created_at'] = $ticketDate;

        if (! empty($data['is_completed'])) {
            $data['completed_date'] = $ticketDate->toDateString();
            $data['completed_time'] = $ticketDate;
        }
        unset($data['is_completed']);

        return Ticket::create($data);
    }

    public static function resolveTicketCategoryForAsset(?Asset $asset): array
    {
        if (! $asset) {
            $defaultCat = TicketCategory::first();

            return ['category_id' => $defaultCat?->id, 'subcategory_id' => null];
        }

        $assetCategoryName = optional(optional($asset->product)->category)->name ?? '';
        $assetCategoryCode = optional(optional($asset->product)->category)->code ?? '';

        $hardwareCat = TicketCategory::where('name', 'Hardware')->orWhere('code', 'HARDWARE')->first();
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
}
