<?php

namespace Tests\Unit;

use App\Models\License;
use App\Models\User;
use App\Services\LicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class LicenseServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_license_seat_checkout_and_available_seats(): void
    {
        $license = License::create([
            'name' => 'IntelliJ IDEA',
            'seats' => 2,
        ]);

        $this->assertEquals(2, $license->availableSeats());

        $user = User::factory()->create();
        $service = new LicenseService;

        $seat = $service->checkoutSeat($license, [
            'assigned_to_type' => 'user',
            'assigned_to_id' => $user->id,
            'notes' => 'Dev tool',
        ]);

        $this->assertNotNull($seat->id);
        $this->assertEquals(1, $license->fresh()->availableSeats());

        $service->checkinSeat($seat);
        $this->assertEquals(2, $license->fresh()->availableSeats());
    }

    public function test_checkout_throws_when_seats_exhausted(): void
    {
        $license = License::create([
            'name' => 'Photoshop',
            'seats' => 0,
        ]);

        $user = User::factory()->create();
        $service = new LicenseService;

        $this->expectException(InvalidArgumentException::class);
        $service->checkoutSeat($license, [
            'assigned_to_type' => 'user',
            'assigned_to_id' => $user->id,
        ]);
    }
}
