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
use Illuminate\Http\UploadedFile;
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

    public function processCheckout(Asset $asset, User $toUser, array $params, ?UploadedFile $evidence = null): AssetCheckout
    {
        return DB::transaction(function () use ($asset, $toUser, $params, $evidence) {
            // ponytail: row lock prevents concurrent double checkout
            $lockedAsset = Asset::lockForUpdate()->find($asset->id);
            if ($lockedAsset && $lockedAsset->isCurrentlyCheckedOut()) {
                throw new \RuntimeException('Asset is already checked out.');
            }

            $deployedStatus = Status::where('name', 'Deployed')->first();
            $deptId = $params['department_id'] ?? $toUser->department_id;
            $locId = $params['location_id'] ?? $toUser->location_id;

            $evidencePath = $params['checkout_evidence_file_path'] ?? null;
            $evidenceName = $params['checkout_evidence_file_name'] ?? null;
            if ($evidence) {
                $evidencePath = $evidence->store('evidence/checkouts', 'public');
                $evidenceName = $evidence->getClientOriginalName();
            }

            $checkout = AssetCheckout::create([
                'asset_id' => $asset->id,
                'checked_out_to_user_id' => $toUser->id,
                'checked_out_by_user_id' => auth()->id() ?? $toUser->id,
                'supervisor_user_id' => $params['supervisor_user_id'] ?? null,
                'department_id' => $deptId,
                'location_id' => $locId,
                'checked_out_at' => $params['checkout_date'] ?? now(),
                'checkout_notes' => $params['notes'] ?? $params['checkout_notes'] ?? null,
                'checkout_evidence_file_path' => $evidencePath,
                'checkout_evidence_file_name' => $evidenceName,
            ]);

            $asset->update([
                'current_checkout_id' => $checkout->id,
                'assigned_to_user_id' => $toUser->id,
                'user_name' => $toUser->name,
                'department_id' => $deptId,
                'location_id' => $locId,
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

    public function processCheckin(Asset $asset, array $params, ?UploadedFile $evidence = null): ?AssetCheckout
    {
        return DB::transaction(function () use ($asset, $params, $evidence) {
            $readyStatus = Status::where('name', 'Ready to Deploy')->first();
            $checkout = $asset->currentCheckout;
            $fromUser = $asset->assignedToUser;

            // Honor the operator-selected post-checkin status; fall back to Ready to Deploy
            $statusId = ! empty($params['status_id']) ? (int) $params['status_id'] : ($readyStatus?->id ?? $asset->status_id);

            $evidencePath = $params['checkin_evidence_file_path'] ?? null;
            $evidenceName = $params['checkin_evidence_file_name'] ?? null;
            if ($evidence) {
                $evidencePath = $evidence->store('evidence/checkins', 'public');
                $evidenceName = $evidence->getClientOriginalName();
            }

            if ($checkout) {
                $checkout->update([
                    'checked_in_by_user_id' => auth()->id() ?? $checkout->checked_out_to_user_id,
                    'supervisor_user_id' => $params['supervisor_user_id'] ?? $checkout->supervisor_user_id,
                    'checkin_status_id' => $statusId,
                    'checked_in_at' => $params['checkin_date'] ?? $params['checked_in_at'] ?? now(),
                    'checkin_notes' => $params['notes'] ?? $params['checkin_notes'] ?? null,
                    'condition_report' => $params['condition_report'] ?? null,
                    'checkin_evidence_file_path' => $evidencePath ?? $checkout->checkin_evidence_file_path,
                    'checkin_evidence_file_name' => $evidenceName ?? $checkout->checkin_evidence_file_name,
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

    public function processMaintenance(Asset $asset, array $params, ?UploadedFile $evidence = null): AssetMaintenance
    {
        return DB::transaction(function () use ($asset, $params, $evidence) {
            $serviceStatus = Status::where('name', 'Service')->first() ?? Status::where('name', 'In Repair')->first();

            $evidencePath = $params['evidence_file_path'] ?? null;
            if ($evidence) {
                $evidencePath = $evidence->store('evidence/maintenance', 'public');
            }

            $maintenance = AssetMaintenance::create([
                'asset_id' => $asset->id,
                'service_provider' => $params['service_provider'],
                'cost' => $params['cost'] ?? 0,
                'start_date' => $params['start_date'] ?? now(),
                'notes' => $params['description'] ?? null,
                'evidence_file_path' => $evidencePath,
            ]);

            // If asset was checked out, cleanly close active checkout to prevent state inconsistency
            if ($asset->currentCheckout) {
                $asset->currentCheckout->update([
                    'checked_in_at' => $params['start_date'] ?? now(),
                    'checked_in_by_user_id' => auth()->id() ?? $asset->currentCheckout->checked_out_to_user_id,
                    'checkin_status_id' => $serviceStatus?->id,
                    'checkin_notes' => 'Sent to maintenance: '.($params['service_provider'] ?? 'Service Provider'),
                    'condition_report' => $params['description'] ?? 'Requires maintenance',
                ]);
            }

            $asset->update([
                'status_id' => $serviceStatus?->id ?? $asset->status_id,
                'current_checkout_id' => null,
                'assigned_to_user_id' => null,
                'user_name' => null,
                'is_components_stripped' => $params['is_components_stripped'] ?? false,
            ]);

            AssetTicketService::createMaintenanceTicket($asset, $params['service_provider'], $params['description'] ?? 'Maintenance');

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

    public function processCompleteMaintenance(Asset $asset, AssetMaintenance $maintenance, array $params, ?UploadedFile $evidence = null): AssetMaintenance
    {
        return DB::transaction(function () use ($asset, $maintenance, $params, $evidence) {
            $readyStatus = Status::where('name', 'Ready to Deploy')->first();

            $cost = isset($params['cost']) && $params['cost'] !== '' && $params['cost'] !== null ? (float) $params['cost'] : ($maintenance->cost ?? 0);
            $notes = ! empty($params['notes']) ? $params['notes'] : (! empty($params['resolution_notes']) ? $params['resolution_notes'] : $maintenance->notes);

            $evidencePath = $maintenance->evidence_file_path;
            if ($evidence) {
                $evidencePath = $evidence->store('evidence/maintenance', 'public');
            }

            $maintenance->update([
                'completion_date' => $params['completion_date'] ?? now(),
                'cost' => $cost,
                'notes' => $notes,
                'evidence_file_path' => $evidencePath,
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
