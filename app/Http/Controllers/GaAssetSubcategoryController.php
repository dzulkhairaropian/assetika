<?php

namespace App\Http\Controllers;

use App\Models\GaAssetCategory;
use App\Models\GaAssetSubcategory;
use Illuminate\Http\Request;

class GaAssetSubcategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = GaAssetSubcategory::with('category');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%$search%")
                ->orWhereHas('category', fn ($q) => $q->where('name', 'like', "%$search%"));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $subcategories = $query->latest()->paginate(25)->withQueryString();
        $categories = GaAssetCategory::all();

        return view('pages.ga-subcategories.index', compact('subcategories', 'categories'));
    }

    public function create()
    {
        $categories = GaAssetCategory::all();

        return view('pages.ga-subcategories.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:ga_asset_categories,id',
            'name' => 'required|string|max:100',
        ]);

        GaAssetSubcategory::create($validated);

        return redirect()->route('ga-subcategories.index')->with('success', 'GA Asset Subcategory created successfully.');
    }

    public function edit(GaAssetSubcategory $ga_subcategory)
    {
        $categories = GaAssetCategory::all();

        return view('pages.ga-subcategories.edit', [
            'subcategory' => $ga_subcategory,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, GaAssetSubcategory $ga_subcategory)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:ga_asset_categories,id',
            'name' => 'required|string|max:100',
        ]);

        $ga_subcategory->update($validated);

        return redirect()->route('ga-subcategories.index')->with('success', 'GA Asset Subcategory updated successfully.');
    }

    public function destroy(GaAssetSubcategory $ga_subcategory)
    {
        if ($ga_subcategory->assets()->exists()) {
            return redirect()->route('ga-subcategories.index')->with('error', 'Cannot delete subcategory because it has associated assets.');
        }

        $ga_subcategory->delete();

        return redirect()->route('ga-subcategories.index')->with('success', 'GA Asset Subcategory deleted successfully.');
    }
}
