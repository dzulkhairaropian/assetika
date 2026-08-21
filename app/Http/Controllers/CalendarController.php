<?php

namespace App\Http\Controllers;

use App\Models\AssetReservation;
use App\Models\Renewal;
use App\Models\Ticket;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function events(Request $request)
    {
        $events = [];

        // 1. Asset Reservations
        $reservations = AssetReservation::with(['user', 'reservable'])->get();
        foreach ($reservations as $res) {
            $events[] = [
                'id' => 'res_' . $res->id,
                'title' => '[Booking] ' . ($res->reservable->name ?? 'Asset') . ' - ' . ($res->user->name ?? 'User'),
                'start' => $res->start_time->toIso8601String(),
                'end' => $res->end_time->toIso8601String(),
                'allDay' => false,
                'extendedProps' => [
                    'calendar' => 'Primary',
                    'type' => 'reservation',
                    'real_id' => $res->id,
                    'purpose' => $res->purpose,
                ],
            ];
        }

        // 2. Renewal / Expiry Items
        $renewals = Renewal::all();
        foreach ($renewals as $renew) {
            $events[] = [
                'id' => 'renew_' . $renew->id,
                'title' => '[Expiry] ' . $renew->name,
                'start' => $renew->expiration_date->format('Y-m-d'),
                'allDay' => true,
                'extendedProps' => [
                    'calendar' => $renew->expiration_date->isPast() ? 'Danger' : 'Warning',
                    'type' => 'renewal',
                    'real_id' => $renew->id,
                ],
            ];
        }

        // 3. Tickets Due
        $tickets = Ticket::whereNotNull('sla_due_at')->get();
        foreach ($tickets as $t) {
            $events[] = [
                'id' => 't_' . $t->id,
                'title' => '[Ticket SLA] #' . $t->request_id,
                'start' => $t->sla_due_at->toIso8601String(),
                'allDay' => false,
                'extendedProps' => [
                    'calendar' => 'Danger',
                    'type' => 'ticket',
                    'real_id' => $t->id,
                ],
            ];
        }

        return response()->json($events);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'start' => 'required|date',
            'end' => 'nullable|date',
            'calendar' => 'nullable|string',
        ]);

        return response()->json(['success' => true, 'event' => $validated]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'start' => 'required|date',
            'end' => 'nullable|date',
        ]);

        if (str_starts_with($id, 'res_')) {
            $resId = str_replace('res_', '', $id);
            $res = AssetReservation::find($resId);
            if ($res) {
                $res->update([
                    'start_time' => $validated['start'],
                    'end_time' => $validated['end'] ?? $validated['start'],
                ]);
            }
        }

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        if (str_starts_with($id, 'res_')) {
            $resId = str_replace('res_', '', $id);
            AssetReservation::destroy($resId);
        }

        return response()->json(['success' => true]);
    }
}
