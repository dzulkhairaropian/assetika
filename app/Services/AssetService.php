<?php

namespace App\Services;

use App\Mail\AssetCheckinMail;
use App\Mail\AssetCheckoutMail;
use App\Mail\AssetMaintenanceCompletedMail;
use App\Mail\AssetMaintenanceStartedMail;
use App\Models\Asset;
use App\Models\AssetCheckout;
use App\Models\AssetMaintenance;
use App\Models\Status;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
                'checked_out_by_user_id' => auth()->id() ?? $toUser->id,
                'department_id' => $toUser->department_id,
                'location_id' => $params['location_id'] ?? $toUser->location_id,
                'checked_out_at' => $params['checkout_date'] ?? now(),
                'checkout_notes' => $params['notes'] ?? $params['checkout_notes'] ?? null,
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

            try {
                if ($toUser->email) {
                    Mail::to($toUser->email)->send(new AssetCheckoutMail($asset, $checkout));
                }
            } catch (\Throwable $e) {
                Log::warning('Failed sending asset checkout email: '.$e->getMessage());
            }

            return $checkout;
        });
    }

    public function processCheckin(Asset $asset, array $params): ?AssetCheckout
    {
        return DB::transaction(function () use ($asset, $params) {
            $readyStatus = Status::where('name', 'Ready to Deploy')->first();
            $checkout = $asset->currentCheckout;
            $fromUser = $asset->assignedToUser;

            // Honor the operator-selected post-checkin status; fall back to Ready to Deploy
            $statusId = ! empty($params['status_id']) ? (int) $params['status_id'] : ($readyStatus?->id ?? $asset->status_id);

            if ($checkout) {
                $checkout->update([
                    'checked_in_by_user_id' => auth()->id() ?? $checkout->checked_out_to_user_id,
                    'supervisor_user_id' => $params['supervisor_user_id'] ?? $checkout->supervisor_user_id,
                    'checked_in_at' => $params['checkin_date'] ?? $params['checked_in_at'] ?? now(),
                    'checkin_notes' => $params['notes'] ?? $params['checkin_notes'] ?? null,
                    'condition_report' => $params['condition_report'] ?? null,
                ]);
            }

            $asset->update([
                'current_checkout_id' => null,
                'assigned_to_user_id' => null,
                'user_name' => null,
                'status_id' => $statusId,
                'is_components_stripped' => array_key_exists('is_components_stripped', $params)
                    ? (bool) $params['is_components_stripped']
                    : $asset->is_components_stripped,
            ]);

            AssetTicketService::createCheckinTicket($asset, $fromUser, $params['condition_report'] ?? null);

            try {
                $recipientEmail = $fromUser?->email ?? auth()->user()?->email;
                if ($recipientEmail) {
                    Mail::to($recipientEmail)->send(new AssetCheckinMail($asset, $checkout));
                }
            } catch (\Throwable $e) {
                Log::warning('Failed sending asset checkin email: '.$e->getMessage());
            }

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

            try {
                $recipientEmail = $asset->assignedToUser?->email ?? auth()->user()?->email;
                if ($recipientEmail) {
                    Mail::to($recipientEmail)->send(new AssetMaintenanceStartedMail($asset, $maintenance));
                }
            } catch (\Throwable $e) {
                Log::warning('Failed sending asset maintenance started email: '.$e->getMessage());
            }

            return $maintenance;
        });
    }

    public function processCompleteMaintenance(Asset $asset, AssetMaintenance $maintenance, array $params): AssetMaintenance
    {
        return DB::transaction(function () use ($asset, $maintenance, $params) {
            $readyStatus = Status::where('name', 'Ready to Deploy')->first();

            $cost = isset($params['cost']) && $params['cost'] !== '' && $params['cost'] !== null ? (float) $params['cost'] : ($maintenance->cost ?? 0);
            $notes = ! empty($params['notes']) ? $params['notes'] : (! empty($params['resolution_notes']) ? $params['resolution_notes'] : $maintenance->notes);

            $maintenance->update([
                'completion_date' => $params['completion_date'] ?? now(),
                'cost' => $cost,
                'notes' => $notes,
            ]);

            $asset->update([
                'status_id' => $readyStatus?->id ?? $asset->status_id,
                'is_components_stripped' => false,
            ]);

            AssetTicketService::createCompleteMaintenanceTicket($asset, $maintenance);

            try {
                $recipientEmail = $asset->assignedToUser?->email ?? auth()->user()?->email;
                if ($recipientEmail) {
                    Mail::to($recipientEmail)->send(new AssetMaintenanceCompletedMail($asset, $maintenance));
                }
            } catch (\Throwable $e) {
                Log::warning('Failed sending asset maintenance completed email: '.$e->getMessage());
            }

            return $maintenance;
        });
    }
}
