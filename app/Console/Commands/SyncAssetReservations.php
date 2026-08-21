<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Models\AssetCheckout;
use App\Models\AssetReservation;
use App\Models\Status;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SyncAssetReservations extends Command
{
    protected $signature = 'reservations:sync';
    protected $description = 'Automate checkout and checkin for approved asset reservations based on start_time and end_time.';

    public function handle(): int
    {
        $now = now();

        // 1. Process Auto Checkout for reservations starting now
        $startingReservations = AssetReservation::where('status', 'approved')
            ->where('start_time', '<=', $now)
            ->where('end_time', '>', $now)
            ->get();

        foreach ($startingReservations as $res) {
            if ($res->reservable_type === Asset::class && $res->reservable) {
                $asset = $res->reservable;

                if (! $asset->isCurrentlyCheckedOut()) {
                    $deployedStatus = Status::where('name', 'Deployed')->first();

                    $checkout = AssetCheckout::create([
                        'asset_id' => $asset->id,
                        'checked_out_by_user_id' => $res->approved_by_id ?? $res->user_id,
                        'checked_out_to_user_id' => $res->user_id,
                        'location_id' => $asset->location_id,
                        'department_id' => $asset->department_id,
                        'checked_out_at' => $res->start_time,
                        'checkout_notes' => 'Auto checkout via reservation: ' . $res->purpose,
                    ]);

                    $asset->update([
                        'current_checkout_id' => $checkout->id,
                        'assigned_to_user_id' => $res->user_id,
                        'status_id' => $deployedStatus ? $deployedStatus->id : $asset->status_id,
                    ]);

                    // Send notification
                    if ($res->user) {
                        $res->user->notify(new SystemNotification(
                            'Reservation Started (Checked Out)',
                            "Your reservation for asset '{$asset->name}' has started and is now checked out.",
                            route('assets.show', $asset->id),
                            'arrow-up-right',
                            'Asset'
                        ));
                    }
                }
            }

            $res->update(['status' => 'checked_out']);
            $this->info("Reservation #{$res->id} checked out automatically.");
        }

        // 2. Process Auto Checkin for expired reservations
        $expiredReservations = AssetReservation::whereIn('status', ['approved', 'checked_out'])
            ->where('end_time', '<=', $now)
            ->get();

        foreach ($expiredReservations as $res) {
            if ($res->reservable_type === Asset::class && $res->reservable) {
                $asset = $res->reservable;

                if ($asset->isCurrentlyCheckedOut()) {
                    $readyStatus = Status::where('name', 'Ready to Deploy')->first();

                    if ($asset->currentCheckout) {
                        $asset->currentCheckout->update([
                            'checked_in_at' => $res->end_time,
                            'checkin_notes' => 'Auto checkin via reservation completion.',
                        ]);
                    }

                    $asset->update([
                        'current_checkout_id' => null,
                        'assigned_to_user_id' => null,
                        'status_id' => $readyStatus ? $readyStatus->id : $asset->status_id,
                    ]);

                    if ($res->user) {
                        $res->user->notify(new SystemNotification(
                            'Reservation Ended (Checked In)',
                            "Your reservation for asset '{$asset->name}' has ended and the asset is returned.",
                            route('assets.show', $asset->id),
                            'check-circle',
                            'Asset'
                        ));
                    }
                }
            }

            $res->update(['status' => 'completed']);
            $this->info("Reservation #{$res->id} completed and checked in automatically.");
        }

        return Command::SUCCESS;
    }
}
