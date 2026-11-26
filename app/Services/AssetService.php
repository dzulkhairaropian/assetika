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
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
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

            $blockedStatusNames = ['broken', 'service', 'in repair', 'service & repair', 'maintenance', 'retired', 'disposed', 'disposal', 'sold'];
            $currentStatusName = strtolower((string) ($lockedAsset?->status?->name ?? $asset->status?->name ?? ''));
            if (in_array($currentStatusName, $blockedStatusNames, true)) {
                throw new \RuntimeException("Cannot check out asset while its status is {$asset->status->name}.");
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
                'expected_return_date' => $params['expected_return_date'] ?? null,
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
            $serviceStatus = Status::whereIn('name', ['Service', 'In Repair', 'Service & Repair', 'Maintenance'])
                ->whereHas('masterCategory', fn ($q) => $q->where('name', 'IT Asset'))
                ->first()
                ?? Status::whereIn('name', ['Service', 'In Repair', 'Service & Repair', 'Maintenance'])->first();

            // Capture affected holder BEFORE clearing checkout/assignment
            $affectedUser = $asset->currentCheckout?->checkedOutToUser ?? $asset->assignedToUser;

            $evidencePath = $params['evidence_file_path'] ?? null;
            if ($evidence) {
                $evidencePath = $evidence->store('evidence/maintenance', 'public');
            }

            $maintenance = AssetMaintenance::create([
                'asset_id' => $asset->id,
                'service_provider' => $params['service_provider'],
                'cost' => $params['cost'] ?? 0,
                'start_date' => $params['start_date'] ?? now(),
                'description' => $params['description'] ?? null,
                'notes' => $params['notes'] ?? null,
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
                'is_components_stripped' => ! empty($params['is_components_stripped']),
            ]);

            AssetTicketService::createMaintenanceTicket($asset, $params['service_provider'], $params['description'] ?? 'Maintenance', $affectedUser);

            try {
                $recipientEmail = $affectedUser?->email ?? auth()->user()?->email;
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
            $readyStatus = Status::where('name', 'Ready to Deploy')
                ->whereHas('masterCategory', fn ($q) => $q->where('name', 'IT Asset'))
                ->first()
                ?? Status::where('name', 'Ready to Deploy')->first();

            $targetStatus = ! empty($params['status_id'])
                ? Status::find($params['status_id'])
                : null;
            $resultingStatus = $targetStatus ?? $readyStatus;
            $resultingSlug = strtolower((string) ($resultingStatus?->name ?? 'ready to deploy'));

            $cost = isset($params['cost']) && $params['cost'] !== '' && $params['cost'] !== null ? (float) $params['cost'] : ($maintenance->cost ?? 0);
            $notes = ! empty($params['notes']) ? $params['notes'] : (! empty($params['resolution_notes']) ? $params['resolution_notes'] : $maintenance->notes);
            $completionDate = $params['completion_date'] ?? now();

            $evidencePath = $maintenance->evidence_file_path;
            if ($evidence) {
                $evidencePath = $evidence->store('evidence/maintenance', 'public');
            }

            $maintenance->update([
                'completion_date' => $completionDate,
                'cost' => $cost,
                'notes' => $notes,
                'evidence_file_path' => $evidencePath,
            ]);

            $isRetired = in_array($resultingSlug, ['retired', 'disposed', 'disposal'], true);
            $isBroken = $resultingSlug === 'broken';

            $assetUpdate = [
                'status_id' => $resultingStatus?->id ?? $asset->status_id,
                'is_components_stripped' => array_key_exists('is_components_stripped', $params)
                    ? (bool) $params['is_components_stripped']
                    : ($isBroken ? $asset->is_components_stripped : false),
            ];

            if ($isRetired) {
                $assetUpdate['retired_at'] = $completionDate;
                $assetUpdate['retirement_reason'] = 'Broken Beyond Repair';
                $assetUpdate['retirement_notes'] = $notes;
            }

            $asset->update($assetUpdate);

            if ($isRetired) {
                AssetTicketService::completeOpenTicketsForRetiredAsset($asset, 'Broken Beyond Repair', $notes);
            } else {
                AssetTicketService::createCompleteMaintenanceTicket($asset, $maintenance, $resultingStatus);
            }

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

    /**
     * Swap/replace a user's currently deployed asset with a ready-to-deploy unit atomically.
     *
     * @return array{old_asset: Asset, new_asset: Asset, user: User}
     */
    public function replaceAsset(Asset $currentAsset, Asset $replacementAsset, array $params): array
    {
        return DB::transaction(function () use ($currentAsset, $replacementAsset, $params) {
            if ($currentAsset->id === $replacementAsset->id) {
                throw new \RuntimeException('Replacement asset must be different from the current asset.');
            }

            $currentCheckout = $currentAsset->currentCheckout;
            $targetUser = $currentCheckout?->checkedOutToUser ?? $currentAsset->assignedToUser;
            if (! $targetUser) {
                throw new \RuntimeException('Current asset is not assigned to any active user.');
            }

            if ($replacementAsset->isCurrentlyCheckedOut()) {
                throw new \RuntimeException('Selected replacement unit is already checked out to another user.');
            }

            $date = $params['replacement_date'] ?? now();
            $mode = $params['replacement_mode'] ?? 'permanent';
            $notes = trim((string) ($params['notes'] ?? ''));

            $checkinNotes = trim("Replaced with {$replacementAsset->asset_tag}".($notes ? " — {$notes}" : ''));
            $this->processCheckin($currentAsset, [
                'status_id' => $params['checkin_status_id'],
                'condition_report' => $params['condition_report'] ?? 'Returned for hardware replacement',
                'notes' => $checkinNotes,
                'checkin_date' => $date,
            ]);

            $modeLabel = $mode === 'loaner' ? 'Loaner' : 'Permanent';
            $checkoutNotes = trim("[{$modeLabel} Replacement for {$currentAsset->asset_tag}]".($notes ? " {$notes}" : ''));

            $this->processCheckout($replacementAsset, $targetUser, [
                'department_id' => $currentCheckout?->department_id ?? $targetUser->department_id,
                'location_id' => $currentCheckout?->location_id ?? $targetUser->location_id,
                'checkout_date' => $date,
                'expected_return_date' => $mode === 'loaner' ? ($params['expected_return_date'] ?? null) : null,
                'notes' => $checkoutNotes,
            ]);

            return [
                'old_asset' => $currentAsset->fresh(),
                'new_asset' => $replacementAsset->fresh(),
                'user' => $targetUser,
            ];
        });
    }

    /**
     * Mark an asset as Broken and automatically create a hardware incident ticket.
     */
    public function markAsBroken(Asset $asset, array $params): Ticket
    {
        return DB::transaction(function () use ($asset, $params) {
            $brokenStatus = Status::where('name', 'Broken')
                ->whereHas('masterCategory', fn ($q) => $q->where('name', 'IT Asset'))
                ->first()
                ?? Status::where('name', 'Broken')->first()
                ?? Status::firstOrCreate(['name' => 'Broken'], ['color' => 'danger', 'icon' => 'alert-triangle']);

            $brokenDate = ! empty($params['broken_date']) ? Carbon::parse($params['broken_date']) : now();
            $description = trim((string) $params['description']);
            $priorityName = $params['priority'] ?? 'High';

            // Create incident ticket while asset holder info is still attached
            $ticket = AssetTicketService::createBrokenTicket($asset, $description, $priorityName);

            $updateData = [
                'status_id' => $brokenStatus->id,
                'is_components_stripped' => ! empty($params['is_components_stripped']),
            ];

            if (! empty($params['save_to_notes'])) {
                $noteEntry = "[Broken {$brokenDate->format('d/m/Y')}] {$description}";
                $updateData['notes'] = filled($asset->notes)
                    ? $asset->notes."\n".$noteEntry
                    : $noteEntry;
            }

            if (! empty($params['checkin_from_holder']) && $asset->currentCheckout) {
                $asset->currentCheckout->update([
                    'checked_in_at' => $brokenDate,
                    'checked_in_by_user_id' => auth()->id() ?? $asset->currentCheckout->checked_out_to_user_id,
                    'checkin_status_id' => $brokenStatus->id,
                    'checkin_notes' => "Marked Broken (Ticket #{$ticket->request_id})",
                    'condition_report' => $description,
                ]);

                $updateData['current_checkout_id'] = null;
                $updateData['assigned_to_user_id'] = null;
                $updateData['user_name'] = null;
            }

            $asset->update($updateData);

            return $ticket;
        });
    }
}
