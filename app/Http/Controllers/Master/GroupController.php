<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GroupController extends Controller
{
    public function index(Request $request)
    {
        $query = Group::withCount('companies');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $groups = $query->latest()->paginate(50)->withQueryString();

        return view('pages.master.groups.index', compact('groups'));
    }

    public function create()
    {
        $companies = Company::whereNull('group_id')->orderBy('name')->get();

        return view('pages.master.groups.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20|unique:groups,code',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'company_ids' => 'nullable|array',
            'company_ids.*' => 'exists:companies,id',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('groups/logos', 'public');
        }

        $group = Group::create($validated);

        if (! empty($request->company_ids)) {
            Company::whereIn('id', $request->company_ids)->update(['group_id' => $group->id]);
        }

        if ($request->has('create_another')) {
            return redirect()->route('groups.create')->with('success', 'Group created successfully. Add another one.');
        }

        return redirect()->route('groups.index')->with('success', 'Group created successfully.');
    }

    public function edit(Group $group)
    {
        $group->load('companies');
        $availableCompanies = Company::whereNull('group_id')
            ->orWhere('group_id', $group->id)
            ->orderBy('name')
            ->get();

        return view('pages.master.groups.edit', compact('group', 'availableCompanies'));
    }

    public function update(Request $request, Group $group)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20|unique:groups,code,'.$group->id,
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'company_ids' => 'nullable|array',
            'company_ids.*' => 'exists:companies,id',
        ]);

        if ($request->hasFile('logo')) {
            if ($group->logo) {
                Storage::disk('public')->delete($group->logo);
            }
            $validated['logo'] = $request->file('logo')->store('groups/logos', 'public');
        }

        $group->update($validated);

        // Reset companies previously attached then sync selected
        Company::where('group_id', $group->id)->update(['group_id' => null]);
        if (! empty($request->company_ids)) {
            Company::whereIn('id', $request->company_ids)->update(['group_id' => $group->id]);
        }

        return redirect()->route('groups.index')->with('success', 'Group updated successfully.');
    }

    public function destroy(Group $group)
    {
        if ($group->logo) {
            Storage::disk('public')->delete($group->logo);
        }
        Company::where('group_id', $group->id)->update(['group_id' => null]);
        $group->delete();

        return redirect()->route('groups.index')->with('success', 'Group deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:groups,id',
        ]);

        $groups = Group::whereIn('id', $request->ids)->get();
        foreach ($groups as $group) {
            if ($group->logo) {
                Storage::disk('public')->delete($group->logo);
            }
        }
        Company::whereIn('group_id', $request->ids)->update(['group_id' => null]);
        Group::whereIn('id', $request->ids)->delete();

        return redirect()->route('groups.index')->with('success', 'Selected groups deleted successfully.');
    }
}
