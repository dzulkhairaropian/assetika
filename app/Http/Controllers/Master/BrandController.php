<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $query = Brand::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%')
                ->orWhere('code', 'like', '%'.$request->search.'%');
        }

        $summary = [
            'total' => Brand::count(),
            'today' => Brand::whereDate('created_at', Carbon::today())->count(),
        ];

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100, 250, 500]) ? (int) $request->input('per_page') : 50;
        $brands = $query->latest()->paginate($perPage)->withQueryString();

        return view('pages.master.brands.index', compact('brands', 'summary'));
    }

    public function create()
    {
        return view('pages.master.brands.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:brands,code',
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string',
        ]);

        Brand::create($validated);

        if ($request->has('create_another')) {
            return redirect()->route('brands.create')->with('success', 'Brand created successfully. Add another one.');
        }

        return redirect()->route('brands.index')->with('success', 'Brand created successfully.');
    }

    public function edit(Brand $brand)
    {
        return view('pages.master.brands.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:brands,code,'.$brand->id,
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string',
        ]);

        $brand->update($validated);

        return redirect()->route('brands.index')->with('success', 'Brand updated successfully.');
    }

    public function destroy(Brand $brand)
    {
        $brand->delete();

        return redirect()->route('brands.index')->with('success', 'Brand deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:brands,id',
        ]);

        Brand::whereIn('id', $request->ids)->delete();

        return redirect()->route('brands.index')->with('success', 'Selected brands deleted successfully.');
    }
}
