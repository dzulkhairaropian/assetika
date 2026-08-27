<?php

namespace App\Http\Controllers;

use App\Models\Renewal;
use App\Models\Ticket;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function events(Request $request)
    {
        $events = [];

        // 1. Renewal / Expiry Items
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

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        return response()->json(['success' => true]);
    }
}
