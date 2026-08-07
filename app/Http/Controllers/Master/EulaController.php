<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Eula;
use Illuminate\Http\Request;

class EulaController extends Controller
{
    public function index(Request $request)
    {
        $query = Eula::with('category');

        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        $eulas = $query->latest()->paginate(50)->withQueryString();

        return view('pages.master.eulas.index', compact('eulas'));
    }

    public function create()
    {
        $categories = Category::whereHas('masterCategory', fn($q) => $q->where('name', 'IT Asset'))->orderBy('name')->get();

        return view('pages.master.eulas.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        Eula::create($validated);

        if ($request->has('create_another')) {
            return redirect()->route('eulas.create')->with('success', 'EULA created. Add another one.');
        }

        return redirect()->route('eulas.index')->with('success', 'EULA created successfully.');
    }

    public function edit(Eula $eula)
    {
        $categories = Category::whereHas('masterCategory', fn($q) => $q->where('name', 'IT Asset'))->orderBy('name')->get();

        return view('pages.master.eulas.edit', compact('eula', 'categories'));
    }

    public function update(Request $request, Eula $eula)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $eula->update($validated);

        return redirect()->route('eulas.index')->with('success', 'EULA updated successfully.');
    }

    public function destroy(Eula $eula)
    {
        $eula->delete();

        return redirect()->route('eulas.index')->with('success', 'EULA deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:eulas,id']);
        Eula::whereIn('id', $request->ids)->delete();

        return redirect()->route('eulas.index')->with('success', 'Selected EULAs deleted successfully.');
    }
}
