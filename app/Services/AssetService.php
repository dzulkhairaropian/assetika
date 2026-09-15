<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetCheckout;
use App\Models\AssetMaintenance;
use App\Models\Status;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AssetService
{
    public function createAsset(array $data, bool $isDraft = false): Asset
    {
        if (empty($data['status_id']) || $isDraft) {
            $draftStatus = Status::firstOrCreate(
                ['name' => 'Draft'],
                ['color' => 'gray', 'icon' => 'file-text']
            );
            $data['status_id'] = $draftStatus->id;
        }

        return Asset::create($data);
    }

    public function updateAsset(Asset $asset, array $data, bool $isDraft = false): Asset
    {
        if ($isDraft) {
            $draftStatus = Status::firstOrCreate(
                ['name' => 'Draft'],
                ['color' => 'gray', 'icon' => 'file-text']
            );
            $data['status_id'] = $draftStatus->id;
        }

        $asset->update($data);

        return $asset;
    }

    public function processCheckout(Asset $asset, User $toUser, array $params): AssetCheckout
    {
        return DB::transaction(function () use ($asset, $toUser, $params) {
            $deployedStatus = Status::where('name', 'Deployed')->first();

            $checkout = AssetCheckout::create([
                'asset_id' => $asset->id,
                'checked_out_to_user_id' => $toUser->id,
                'department_id' => $toUser->department_id,
                'location_id' => $params['location_id'] ?? $toUser->location_id,
                'checked_out_by_id' => auth()->id(),
                'checked_out_at' => $params['checkout_date'] ?? now(),
                'notes' => $params['notes'] ?? null,
            ]);

            $asset->update([
                'current_checkout_id' => $checkout->id,
                'assigned_to_user_id' => $toUser->id,
                'user_name' => $toUser->name,
                'department_id' => $toUser->department_id,
                'location_id' => $params['location_id'] ?? $toUser->location_id,
                'status_id' => $deployedStatus?->id ?? $asset->status_id,
            ]);

            AssetTicketService::createCheckoutTicket($asset, $toUser, $params['notes'] ?? null);

            return $checkout;
        });
    }

    public function processCheckin(Asset $asset, array $params): ?AssetCheckout
    {
        return DB::transaction(function () use ($asset, $params) {
            $readyStatus = Status::where('name', 'Ready to Deploy')->first();
            $checkout = $asset->currentCheckout;
            $fromUser = $asset->assignedToUser;

            if ($checkout) {
                $checkout->update([
                    'checked_in_by_id' => auth()->id(),
                    'checked_in_at' => $params['checkin_date'] ?? now(),
                    'notes' => $params['notes'] ?? $checkout->notes,
                ]);
            }

            $asset->update([
                'current_checkout_id' => null,
                'assigned_to_user_id' => null,
                'user_name' => null,
                'status_id' => $readyStatus?->id ?? $asset->status_id,
            ]);

            AssetTicketService::createCheckinTicket($asset, $fromUser, $params['condition_report'] ?? null);

            return $checkout;
        });
    }

    public function processMaintenance(Asset $asset, array $params): AssetMaintenance
    {
        return DB::transaction(function () use ($asset, $params) {
            $serviceStatus = Status::where('name', 'Service')->first() ?? Status::where('name', 'In Repair')->first();

            $maintenance = AssetMaintenance::create([
                'asset_id' => $asset->id,
                'service_provider' => $params['service_provider'],
                'cost' => $params['cost'] ?? 0,
                'start_date' => $params['start_date'] ?? now(),
                'notes' => $params['description'] ?? null,
                'is_components_stripped' => $params['is_components_stripped'] ?? false,
            ]);

            $asset->update([
                'status_id' => $serviceStatus?->id ?? $asset->status_id,
                'is_components_stripped' => $params['is_components_stripped'] ?? false,
            ]);

            AssetTicketService::createMaintenanceTicket($asset, $params['service_provider'], $params['description']);

            return $maintenance;
        });
    }

    public function processCompleteMaintenance(Asset $asset, AssetMaintenance $maintenance, array $params): AssetMaintenance
    {
        return DB::transaction(function () use ($asset, $maintenance, $params) {
            $readyStatus = Status::where('name', 'Ready to Deploy')->first();

            $maintenance->update([
                'completion_date' => $params['completion_date'] ?? now(),
                'cost' => $params['cost'] ?? $maintenance->cost,
                'notes' => $params['resolution_notes'] ?? $maintenance->notes,
            ]);

            $asset->update([
                'status_id' => $readyStatus?->id ?? $asset->status_id,
                'is_components_stripped' => false,
            ]);

            AssetTicketService::createCompleteMaintenanceTicket($asset, $maintenance);

            return $maintenance;
        });
    }
}
