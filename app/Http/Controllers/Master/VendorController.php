<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $query = Vendor::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('contact_person', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }

        $vendors = $query->latest()->paginate(50)->withQueryString();
        return view('pages.master.vendors.index', compact('vendors'));
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
        ]);

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
            'email' => 'nullable|email|max:255|unique:vendors,email,' . $vendor->id,
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50|unique:vendors,phone,' . $vendor->id,
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string',
        ]);

        $vendor->update($validated);

        return redirect()->route('vendors.index')->with('success', 'Vendor updated successfully.');
    }

    public function destroy(Vendor $vendor)
    {
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
