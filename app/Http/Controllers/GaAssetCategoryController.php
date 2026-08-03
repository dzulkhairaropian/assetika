<?php

namespace App\Http\Controllers;

use App\Models\GaAssetCategory;
use Illuminate\Http\Request;

class GaAssetCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = GaAssetCategory::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%$search%")
                  ->orWhere('code_prefix', 'like', "%$search%");
        }

        $categories = $query->latest()->paginate(25)->withQueryString();

        return view('pages.ga-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('pages.ga-categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code_prefix' => 'required|string|max:255|unique:ga_asset_categories,code_prefix',
            'depreciation_method' => 'required|in:straight_line,double_declining,sum_of_years_digits',
            'useful_life_years' => 'required|integer|min:1',
        ]);

        GaAssetCategory::create($validated);

        return redirect()->route('ga-categories.index')->with('success', 'GA Asset Category created successfully.');
    }

    public function edit(GaAssetCategory $ga_category)
    {
        return view('pages.ga-categories.edit', ['category' => $ga_category]);
    }

    public function update(Request $request, GaAssetCategory $ga_category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code_prefix' => 'required|string|max:255|unique:ga_asset_categories,code_prefix,' . $ga_category->id,
            'depreciation_method' => 'required|in:straight_line,double_declining,sum_of_years_digits',
            'useful_life_years' => 'required|integer|min:1',
        ]);

        $ga_category->update($validated);

        return redirect()->route('ga-categories.index')->with('success', 'GA Asset Category updated successfully.');
    }

    public function destroy(GaAssetCategory $ga_category)
    {
        // Check if category has any assets
        if ($ga_category->assets()->exists()) {
            return redirect()->route('ga-categories.index')->with('error', 'Cannot delete category because it has associated assets.');
        }

        $ga_category->delete();

        return redirect()->route('ga-categories.index')->with('success', 'GA Asset Category deleted successfully.');
    }
}
