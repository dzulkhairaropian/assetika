<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\CustomField;
use App\Models\Fieldset;
use Illuminate\Http\Request;

class FieldsetController extends Controller
{
    public function index(Request $request)
    {
        $query = Fieldset::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%$search%");
        }

        $fieldsets = $query->withCount('customFields')->latest()->paginate(50)->withQueryString();

        return view('pages.master.fieldsets.index', compact('fieldsets'));
    }

    public function create()
    {
        $availableFields = CustomField::orderBy('name')->get();

        return view('pages.master.fieldsets.create', compact('availableFields'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'custom_fields' => 'nullable|array',
            'custom_fields.*.id' => 'exists:custom_fields,id',
            'custom_fields.*.order' => 'nullable|integer|min:0',
            'custom_fields.*.required' => 'nullable|boolean',
        ]);

        $fieldset = Fieldset::create(['name' => $validated['name']]);

        if ($request->has('custom_fields')) {
            $syncData = [];
            foreach ($request->custom_fields as $index => $field) {
                if (!empty($field['id'])) {
                    $syncData[$field['id']] = [
                        'order' => $field['order'] ?? $index,
                        'is_required' => !empty($field['required']),
                    ];
                }
            }
            $fieldset->customFields()->sync($syncData);
        }

        if ($request->has('create_another')) {
            return redirect()->route('fieldsets.create')->with('success', 'Fieldset created successfully. Add another one.');
        }

        return redirect()->route('fieldsets.index')->with('success', 'Fieldset created successfully.');
    }

    public function edit(Fieldset $fieldset)
    {
        $availableFields = CustomField::orderBy('name')->get();
        $fieldset->load('customFields');

        return view('pages.master.fieldsets.edit', compact('fieldset', 'availableFields'));
    }

    public function update(Request $request, Fieldset $fieldset)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'custom_fields' => 'nullable|array',
            'custom_fields.*.id' => 'exists:custom_fields,id',
            'custom_fields.*.order' => 'nullable|integer|min:0',
            'custom_fields.*.required' => 'nullable|boolean',
        ]);

        $fieldset->update(['name' => $validated['name']]);

        $syncData = [];
        if ($request->has('custom_fields')) {
            foreach ($request->custom_fields as $index => $field) {
                if (!empty($field['id'])) {
                    $syncData[$field['id']] = [
                        'order' => $field['order'] ?? $index,
                        'is_required' => !empty($field['required']),
                    ];
                }
            }
        }
        $fieldset->customFields()->sync($syncData);

        return redirect()->route('fieldsets.index')->with('success', 'Fieldset updated successfully.');
    }

    public function destroy(Fieldset $fieldset)
    {
        $fieldset->delete();

        return redirect()->route('fieldsets.index')->with('success', 'Fieldset deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:fieldsets,id',
        ]);

        Fieldset::whereIn('id', $request->ids)->delete();

        return redirect()->route('fieldsets.index')->with('success', 'Selected fieldsets deleted successfully.');
    }
}
