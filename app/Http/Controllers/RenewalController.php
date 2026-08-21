<?php

namespace App\Http\Controllers;

use App\Models\Renewal;
use App\Models\Vendor;
use App\Models\User;
use Illuminate\Http\Request;

class RenewalController extends Controller
{
    public function index(Request $request)
    {
        $query = Renewal::with(['vendor', 'responsibleUser']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $renewals = $query->orderBy('expiration_date')->paginate(15);

        return view('pages.renewals.index', compact('renewals'));
    }

    public function create()
    {
        $vendors = Vendor::all();
        $users = User::all();

        return view('pages.renewals.create', compact('vendors', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'issued_date' => 'nullable|date',
            'expiration_date' => 'required|date',
            'reminder_days_before' => 'required|integer|min:1',
            'vendor_id' => 'nullable|exists:vendors,id',
            'responsible_user_id' => 'nullable|exists:users,id',
            'cost' => 'nullable|numeric|min:0',
            'auto_renew' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['auto_renew'] = $request->has('auto_renew');
        Renewal::create($validated);

        return redirect()->route('renewals.index')->with('success', 'Renewal item created successfully.');
    }

    public function edit(Renewal $renewal)
    {
        $vendors = Vendor::all();
        $users = User::all();

        return view('pages.renewals.edit', compact('renewal', 'vendors', 'users'));
    }

    public function update(Request $request, Renewal $renewal)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'issued_date' => 'nullable|date',
            'expiration_date' => 'required|date',
            'reminder_days_before' => 'required|integer|min:1',
            'vendor_id' => 'nullable|exists:vendors,id',
            'responsible_user_id' => 'nullable|exists:users,id',
            'cost' => 'nullable|numeric|min:0',
            'auto_renew' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['auto_renew'] = $request->has('auto_renew');
        $renewal->update($validated);

        return redirect()->route('renewals.index')->with('success', 'Renewal item updated successfully.');
    }

    public function destroy(Renewal $renewal)
    {
        $renewal->delete();

        return redirect()->route('renewals.index')->with('success', 'Renewal item deleted.');
    }
}
