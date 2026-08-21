<?php

namespace App\Http\Controllers;

use App\Models\SlaPolicy;
use App\Models\Priority;
use Illuminate\Http\Request;

class SlaPolicyController extends Controller
{
    public function index()
    {
        $priorities = Priority::all();
        $policies = SlaPolicy::with('priority')->get()->keyBy('priority_id');

        return view('pages.sla.index', compact('priorities', 'policies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'policies' => 'required|array',
            'policies.*.priority_id' => 'required|exists:priorities,id',
            'policies.*.response_target_minutes' => 'required|integer|min:1',
            'policies.*.resolution_target_hours' => 'required|integer|min:1',
        ]);

        foreach ($request->policies as $data) {
            SlaPolicy::updateOrCreate(
                ['priority_id' => $data['priority_id']],
                [
                    'response_target_minutes' => $data['response_target_minutes'],
                    'resolution_target_hours' => $data['resolution_target_hours'],
                ]
            );
        }

        return redirect()->back()->with('success', 'SLA policies updated successfully.');
    }
}
