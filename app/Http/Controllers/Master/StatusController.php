<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\MasterCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StatusController extends Controller
{
    private function convertColorNameToHex(string $colorName): string
    {
        // Peta warna yang komprehensif dari nama ke heksadesimal Tailadmin
        $colorMap = [
            'primary' => '#0d6efd',
            'secondary' => '#6c757d',
            'success' => '#198754',
            'danger' => '#dc3545',
            'warning' => '#ffc107',
            'info' => '#0dcaf0',
            'light' => '#f8f9fa',
            'dark' => '#212529',
            // Tambahan alias untuk warna umum agar lebih fleksibel
            'red' => '#dc3545',
            'blue' => '#0d6efd',
            'green' => '#198754',
            'yellow' => '#ffc107',
            'grey' => '#6c757d', // Alias untuk secondary
        ];

        $normalizedColorName = Str::lower($colorName);

        // Jika warna sudah dalam format hex, kembalikan saja
        if (preg_match('/^#[a-f0-9]{6}$/i', $colorName)) {
            return $colorName;
        }

        return $colorMap[$normalizedColorName] ?? '#98A2B3'; // Default to a neutral grey if not found
    }

    public function index(Request $request)
    {
        $query = Status::with('masterCategory');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%$search%");
        }

        if ($request->filled('master_category_id')) {
            $query->where('master_category_id', $request->master_category_id);
        }

        $statuses = $query->latest()->paginate(50)->withQueryString();
        $masterCategories = MasterCategory::all();

        return view('pages.master.statuses.index', compact('statuses', 'masterCategories'));
    }

    public function create()
    {
        $masterCategories = MasterCategory::orderBy('name')->get();
        return view('pages.master.statuses.create', compact('masterCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:50',
            'master_category_id' => 'required|exists:master_categories,id',
            'description' => 'nullable|string',
        ]);

        Status::create($validated);

        if ($request->has('create_another')) {
            return redirect()->route('statuses.create')->with('success', 'Status created. Add another one.');
        }

        return redirect()->route('statuses.index')->with('success', 'Status created successfully.');
    }

    public function edit(Status $status)
    {
        $masterCategories = MasterCategory::orderBy('name')->get();
        return view('pages.master.statuses.edit', compact('status', 'masterCategories'));
    }

    public function update(Request $request, Status $status)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:50', // Tetap string, konversi setelah validasi
            'master_category_id' => 'required|exists:master_categories,id',
            'description' => 'nullable|string',
        ]);

        // Konversi nama warna ke heksadesimal sebelum disimpan
        if (isset($validated['color'])) {
            $validated['color'] = $this->convertColorNameToHex($validated['color']);
        }

        $status->update($validated);

        return redirect()->route('statuses.index')->with('success', 'Status updated successfully.');
    }

    public function destroy(Status $status)
    {
        $status->delete();
        return redirect()->route('statuses.index')->with('success', 'Status deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:statuses,id',
        ]);

        Status::whereIn('id', $request->ids)->delete();

        return redirect()->route('statuses.index')->with('success', 'Selected statuses deleted successfully.');
    }
}
