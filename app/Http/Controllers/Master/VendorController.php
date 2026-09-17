<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $query = Vendor::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('contact_person', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%")
                    ->orWhere('npwp_number', 'like', "%$search%")
                    ->orWhere('npwp_name', 'like', "%$search%")
                    ->orWhere('nib', 'like', "%$search%")
                    ->orWhere('bank_name', 'like', "%$search%")
                    ->orWhere('bank_account_number', 'like', "%$search%");
            });
        }

        $summary = [
            'total' => Vendor::count(),
            'today' => Vendor::whereDate('created_at', Carbon::today())->count(),
        ];

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100, 250, 500]) ? (int) $request->input('per_page') : 50;
        $vendors = $query->latest()->paginate($perPage)->withQueryString();

        return view('pages.master.vendors.index', compact('vendors', 'summary'));
    }

    public function create()
    {
        return view('pages.master.vendors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:vendors,email',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50|unique:vendors,phone',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string',
            'npwp_number' => 'nullable|string|max:100',
            'npwp_name' => 'nullable|string|max:255',
            'npwp_photo' => 'nullable|image|mimes:jpeg,jpg,png,pdf|max:4096',
            'nib' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:100',
            'bank_account_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($request->hasFile('npwp_photo')) {
            $path = $request->file('npwp_photo')->store('vendors/npwp', 'public');
            $validated['npwp_photo'] = $path;
        }

        Vendor::create($validated);

        if ($request->has('create_another')) {
            return redirect()->route('vendors.create')->with('success', 'Vendor created. Add another one.');
        }

        return redirect()->route('vendors.index')->with('success', 'Vendor created successfully.');
    }

    public function edit(Vendor $vendor)
    {
        return view('pages.master.vendors.edit', compact('vendor'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:vendors,email,'.$vendor->id,
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50|unique:vendors,phone,'.$vendor->id,
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string',
            'npwp_number' => 'nullable|string|max:100',
            'npwp_name' => 'nullable|string|max:255',
            'npwp_photo' => 'nullable|image|mimes:jpeg,jpg,png,pdf|max:4096',
            'nib' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:100',
            'bank_account_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($request->hasFile('npwp_photo')) {
            if ($vendor->npwp_photo && Storage::disk('public')->exists($vendor->npwp_photo)) {
                Storage::disk('public')->delete($vendor->npwp_photo);
            }
            $path = $request->file('npwp_photo')->store('vendors/npwp', 'public');
            $validated['npwp_photo'] = $path;
        }

        $vendor->update($validated);

        return redirect()->route('vendors.index')->with('success', 'Vendor updated successfully.');
    }

    public function destroy(Vendor $vendor)
    {
        if ($vendor->npwp_photo && Storage::disk('public')->exists($vendor->npwp_photo)) {
            Storage::disk('public')->delete($vendor->npwp_photo);
        }
        $vendor->delete();

        return redirect()->route('vendors.index')->with('success', 'Vendor deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:vendors,id',
        ]);

        Vendor::whereIn('id', $request->ids)->delete();

        return redirect()->route('vendors.index')->with('success', 'Selected vendors deleted successfully.');
    }
}
