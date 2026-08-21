<?php

namespace App\Http\Controllers;

use App\Models\AssetReservation;
use App\Models\Asset;
use App\Models\GaAsset;
use Illuminate\Http\Request;

class AssetReservationController extends Controller
{
    public function index(Request $request)
    {
        $reservations = AssetReservation::with(['user', 'reservable', 'approvedBy'])
            ->latest()
            ->paginate(15);

        return view('pages.reservations.index', compact('reservations'));
    }

    public function create()
    {
        $assets = Asset::all();
        $gaAssets = GaAsset::all();

        return view('pages.reservations.create', compact('assets', 'gaAssets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'reservable_type' => 'required|string',
            'reservable_id' => 'required|integer',
            'start_time' => 'required|date|after_or_equal:now',
            'end_time' => 'required|date|after:start_time',
            'purpose' => 'required|string|max:500',
        ]);

        $hasOverlap = AssetReservation::where('reservable_type', $request->reservable_type)
            ->where('reservable_id', $request->reservable_id)
            ->whereIn('status', ['pending', 'approved', 'checked_out'])
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                      ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                      ->orWhere(function ($q) use ($request) {
                          $q->where('start_time', '<=', $request->start_time)
                            ->where('end_time', '>=', $request->end_time);
                      });
            })->exists();

        if ($hasOverlap) {
            return redirect()->back()->withInput()->with('error', 'Asset is already reserved for the selected time range.');
        }

        AssetReservation::create([
            'reservable_type' => $request->reservable_type,
            'reservable_id' => $request->reservable_id,
            'user_id' => auth()->id(),
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'purpose' => $request->purpose,
            'status' => 'pending',
        ]);

        return redirect()->route('reservations.index')->with('success', 'Reservation request submitted successfully.');
    }

    public function approve(AssetReservation $reservation)
    {
        $reservation->update([
            'status' => 'approved',
            'approved_by_id' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Reservation approved.');
    }

    public function reject(AssetReservation $reservation)
    {
        $reservation->update([
            'status' => 'rejected',
            'approved_by_id' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Reservation rejected.');
    }
}
