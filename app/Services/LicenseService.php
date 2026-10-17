<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use InvalidArgumentException;

class LicenseService
{
    public function checkoutSeat(License $license, array $params): LicenseSeat
    {
        // ponytail: basic seat checkout, add reservation lock if high concurrency needed
        if ($license->availableSeats() <= 0) {
            throw new InvalidArgumentException('No available seats for this license.');
        }

        $type = ($params['assigned_to_type'] ?? '') === 'asset' ? Asset::class : User::class;
        $targetId = $params['assigned_to_id'] ?? $params['assigned_to_user_id'] ?? null;

        return $license->seats()->create([
            'assigned_to_type' => $type,
            'assigned_to_id' => $targetId,
            'checked_out_at' => now(),
            'notes' => $params['notes'] ?? null,
        ]);
    }

    public function checkinSeat(LicenseSeat $seat): bool
    {
        return (bool) $seat->delete();
    }
}
