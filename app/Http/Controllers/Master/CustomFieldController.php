<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\CustomField;
use Illuminate\Http\Request;

class CustomFieldController extends Controller
{
    public function index(Request $request)
    {
        $query = CustomField::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('field_type', 'like', "%$search%");
            });
        }

        $fields = $query->latest()->paginate(50)->withQueryString();

        return view('pages.master.custom-fields.index', compact('fields'));
    }

    public function create()
    {
        return view('pages.master.custom-fields.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'field_type' => 'required|string|in:text,textarea,dropdown,date,boolean,number',
            'help_text' => 'nullable|string|max:255',
            'default_value' => 'nullable|string|max:255',
            'order' => 'nullable|integer|min:0',
        ]);

        if ($request->has('field_values') && $validated['field_type'] === 'dropdown') {
            $options = array_filter(explode("\n", str_replace("\r", '', $request->field_values)));
            $validated['field_values'] = array_values(array_map('trim', $options));
        } else {
            $validated['field_values'] = null;
        }

        CustomField::create($validated);

        if ($request->has('create_another')) {
            return redirect()->route('custom-fields.create')->with('success', 'Custom field created successfully. Add another one.');
        }

        return redirect()->route('custom-fields.index')->with('success', 'Custom field created successfully.');
    }

    public function edit(CustomField $customField)
    {
        return view('pages.master.custom-fields.edit', compact('customField'));
    }

    public function update(Request $request, CustomField $customField)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'field_type' => 'required|string|in:text,textarea,dropdown,date,boolean,number',
            'help_text' => 'nullable|string|max:255',
            'default_value' => 'nullable|string|max:255',
            'order' => 'nullable|integer|min:0',
        ]);

        if ($request->has('field_values') && $validated['field_type'] === 'dropdown') {
            $options = array_filter(explode("\n", str_replace("\r", '', $request->field_values)));
            $validated['field_values'] = array_values(array_map('trim', $options));
        } elseif ($validated['field_type'] !== 'dropdown') {
            $validated['field_values'] = null;
        }

        $customField->update($validated);

        return redirect()->route('custom-fields.index')->with('success', 'Custom field updated successfully.');
    }

    public function destroy(CustomField $customField)
    {
        $customField->delete();

        return redirect()->route('custom-fields.index')->with('success', 'Custom field deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:custom_fields,id',
        ]);

        CustomField::whereIn('id', $request->ids)->delete();

        return redirect()->route('custom-fields.index')->with('success', 'Selected custom fields deleted successfully.');
    }
}
