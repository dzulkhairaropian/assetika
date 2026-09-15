<?php

namespace App\Services;

use App\Models\License;
use App\Models\LicenseSeat;

class LicenseService
{
    public function checkoutSeat(License $license, array $params): LicenseSeat
    {
        $seat = $license->seatsRelation()->whereNull('assigned_to_user_id')->whereNull('asset_id')->first();

        if (! $seat) {
            $seat = LicenseSeat::create([
                'license_id' => $license->id,
                'seat_number' => $license->seatsRelation()->count() + 1,
            ]);
        }

        $seat->update([
            'assigned_to_user_id' => $params['assigned_to_user_id'] ?? null,
            'asset_id' => $params['asset_id'] ?? null,
            'notes' => $params['notes'] ?? null,
        ]);

        return $seat;
    }

    public function checkinSeat(LicenseSeat $seat): LicenseSeat
    {
        $seat->update([
            'assigned_to_user_id' => null,
            'asset_id' => null,
            'notes' => null,
        ]);

        return $seat;
    }
}
