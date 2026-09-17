<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MasterCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::with('masterCategory')->withCount('products');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('code', 'like', "%$search%");
            });
        }

        if ($request->filled('master_category_id')) {
            $query->where('master_category_id', $request->master_category_id);
        }

        $summary = [
            'total' => Category::count(),
            'today' => Category::whereDate('created_at', Carbon::today())->count(),
        ];

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100, 250, 500]) ? (int) $request->input('per_page') : 50;
        $categories = $query->latest()->paginate($perPage)->withQueryString();
        $masterCategories = MasterCategory::all();

        return view('pages.master.categories.index', compact('categories', 'masterCategories', 'summary'));
    }

    public function create()
    {
        $masterCategories = MasterCategory::orderBy('name')->get();

        return view('pages.master.categories.create', compact('masterCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:categories,code',
            'master_category_id' => 'required|exists:master_categories,id',
            'description' => 'nullable|string',
        ]);

        Category::create($validated);

        if ($request->has('create_another')) {
            return redirect()->route('categories.create')->with('success', 'Category created. Add another one.');
        }

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        $masterCategories = MasterCategory::orderBy('name')->get();

        return view('pages.master.categories.edit', compact('category', 'masterCategories'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:categories,code,'.$category->id,
            'master_category_id' => 'required|exists:master_categories,id',
            'description' => 'nullable|string',
        ]);

        $category->update($validated);

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:categories,id',
        ]);

        Category::whereIn('id', $request->ids)->delete();

        return redirect()->route('categories.index')->with('success', 'Selected categories deleted successfully.');
    }
}
