<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
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
                'id' => 'renew_'.$renew->id,
                'title' => '[Expiry] '.$renew->name,
                'start' => $renew->expiration_date->format('Y-m-d'),
                'allDay' => true,
                'extendedProps' => [
                    'calendar' => $renew->expiration_date->isPast() ? 'Danger' : 'Warning',
                    'type' => 'renewal',
                    'real_id' => $renew->id,
                ],
            ];
        }

        // 2. Custom User Scheduled Events
        try {
            $customEvents = CalendarEvent::all();
            foreach ($customEvents as $ce) {
                $events[] = [
                    'id' => (string) $ce->id,
                    'title' => $ce->title,
                    'start' => $ce->start?->toIso8601String() ?? $ce->start,
                    'end' => $ce->end ? $ce->end->toIso8601String() : null,
                    'allDay' => false,
                    'extendedProps' => [
                        'calendar' => $ce->calendar ?: 'Primary',
                        'type' => 'custom',
                        'real_id' => $ce->id,
                    ],
                ];
            }
        } catch (\Throwable) {
            // Table might not exist yet if migration pending
        }

        // 3. Tickets Due
        $tickets = Ticket::whereNotNull('sla_due_at')->get();
        foreach ($tickets as $t) {
            $events[] = [
                'id' => 't_'.$t->id,
                'title' => '[Ticket SLA] #'.$t->request_id,
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
            'calendar' => 'nullable|string|max:50',
        ]);

        $event = CalendarEvent::create([
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'start' => $validated['start'],
            'end' => $validated['end'] ?? null,
            'calendar' => $validated['calendar'] ?? 'Primary',
        ]);

        return response()->json(['success' => true, 'event' => $event]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'start' => 'required|date',
            'end' => 'nullable|date',
            'calendar' => 'nullable|string|max:50',
        ]);

        $numericId = (int) str_replace('custom_', '', (string) $id);
        $event = CalendarEvent::find($numericId);
        if ($event) {
            $event->update($validated);
        }

        return response()->json(['success' => true, 'event' => $event]);
    }

    public function destroy($id)
    {
        $numericId = (int) str_replace('custom_', '', (string) $id);
        CalendarEvent::where('id', $numericId)->delete();

        return response()->json(['success' => true]);
    }
}
