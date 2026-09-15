<?php

namespace App\Services;

use App\Models\Consumable;
use App\Models\ConsumableTransaction;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ConsumableService
{
    public function processCheckout(Consumable $consumable, User $toUser, int $quantity, array $params, ?UploadedFile $evidence = null): ConsumableTransaction
    {
        return DB::transaction(function () use ($consumable, $toUser, $quantity, $params, $evidence) {
            $unitCost = $consumable->purchase_cost ?? 0;
            $totalCost = $quantity * $unitCost;

            $evidencePath = null;
            if ($evidence) {
                $evidencePath = $evidence->store('consumables/transactions', 'public');
            }

            $before = $consumable->quantity;
            $after = $before - $quantity;

            $transaction = ConsumableTransaction::create([
                'consumable_id' => $consumable->id,
                'user_id' => auth()->id(),
                'checked_out_to_user_id' => $toUser->id,
                'type' => 'checkout',
                'quantity' => $quantity,
                'quantity_before' => $before,
                'quantity_after' => $after,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'evidence_file_path' => $evidencePath,
                'notes' => $params['notes'] ?? null,
                'transaction_date' => $params['transaction_date'] ?? now(),
            ]);

            $consumable->update([
                'quantity' => $after,
            ]);

            // Alert IT Admins when stock drops to or below low threshold
            if ($after <= ($consumable->low_threshold ?? 5)) {
                $admins = User::whereHas('roles', fn ($q) => $q->whereIn('name', ['Admin', 'Super Admin', 'IT Admin']))->get();
                foreach ($admins as $admin) {
                    $admin->notify(new SystemNotification(
                        'Consumable Low Stock Alert',
                        "Low Stock Warning: '".($consumable->product?->name ?? 'Consumable')."' remaining count is {$after} (threshold: {$consumable->low_threshold}).",
                        route('consumables.show', $consumable),
                        'alert-triangle',
                        'Consumable'
                    ));
                }
            }

            $toUser->notify(new SystemNotification(
                'Consumable Checked Out',
                "{$quantity} units of '".($consumable->product?->name ?? 'Consumable')."' have been checked out to you.",
                route('consumables.index'),
                'shopping-bag',
                'Consumable'
            ));

            return $transaction;
        });
    }

    public function processBulkCheckout(array $items, User $toUser, array $params, ?UploadedFile $evidence = null): array
    {
        return DB::transaction(function () use ($items, $toUser, $params, $evidence) {
            $evidencePath = null;
            if ($evidence) {
                $evidencePath = $evidence->store('consumables/transactions', 'public');
            }

            $transactions = [];
            $details = [];

            foreach ($items as $consumableId => $qty) {
                $qty = (int) $qty;
                if ($qty <= 0) {
                    continue;
                }

                $consumable = Consumable::findOrFail($consumableId);
                $unitCost = $consumable->purchase_cost ?? 0;
                $totalCost = $qty * $unitCost;

                $before = $consumable->quantity;
                $after = $before - $qty;

                $transactions[] = ConsumableTransaction::create([
                    'consumable_id' => $consumable->id,
                    'user_id' => auth()->id(),
                    'checked_out_to_user_id' => $toUser->id,
                    'type' => 'checkout',
                    'quantity' => $qty,
                    'quantity_before' => $before,
                    'quantity_after' => $after,
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,
                    'evidence_file_path' => $evidencePath,
                    'notes' => $params['notes'] ?? null,
                    'transaction_date' => $params['transaction_date'] ?? now(),
                ]);

                $consumable->update([
                    'quantity' => $after,
                ]);

                $details[] = "{$qty}x ".($consumable->product?->name ?? 'Consumable');
            }

            if (! empty($details)) {
                $toUser->notify(new SystemNotification(
                    'Consumables Checked Out',
                    'Consumables checked out to you: '.implode(', ', $details),
                    route('consumables.index'),
                    'shopping-bag',
                    'Consumable'
                ));
            }

            return $transactions;
        });
    }

    public function processRestock(Consumable $consumable, int $quantity, array $params, ?UploadedFile $evidence = null): ConsumableTransaction
    {
        return DB::transaction(function () use ($consumable, $quantity, $params, $evidence) {
            $unitCost = $params['unit_cost'] ?? ($consumable->purchase_cost ?? 0);
            $totalCost = $quantity * $unitCost;

            $evidencePath = null;
            if ($evidence) {
                $evidencePath = $evidence->store('consumables/transactions', 'public');
            }

            $before = $consumable->quantity;
            $after = $before + $quantity;

            $transaction = ConsumableTransaction::create([
                'consumable_id' => $consumable->id,
                'user_id' => auth()->id(),
                'type' => 'restock',
                'quantity' => $quantity,
                'quantity_before' => $before,
                'quantity_after' => $after,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'evidence_file_path' => $evidencePath,
                'notes' => $params['notes'] ?? null,
                'transaction_date' => $params['transaction_date'] ?? now(),
            ]);

            $consumable->update([
                'quantity' => $after,
                'purchase_cost' => $unitCost > 0 ? $unitCost : $consumable->purchase_cost,
            ]);

            return $transaction;
        });
    }
}
