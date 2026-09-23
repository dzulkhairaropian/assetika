<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = TicketCategory::withCount('subcategories');

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->boolean('status'));
        }

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100]) ? (int) $request->input('per_page') : 25;
        $categories = $query->latest()->paginate($perPage)->withQueryString();

        return view('pages.admin.ticket-categories.index', compact('categories', 'search'));
    }

    public function create(): View
    {
        return view('pages.admin.ticket-categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (empty($data['code'])) {
            $data['code'] = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $data['name']), 0, 8));
        }

        $data['is_active'] = $request->boolean('is_active', true);

        TicketCategory::create($data);

        return redirect()->route('admin.ticket-categories.index')->with('success', 'Ticket Category created successfully.');
    }

    public function edit(TicketCategory $ticketCategory): View
    {
        return view('pages.admin.ticket-categories.edit', ['category' => $ticketCategory]);
    }

    public function update(Request $request, TicketCategory $ticketCategory): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (empty($data['code'])) {
            $data['code'] = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $data['name']), 0, 8));
        }

        $data['is_active'] = $request->boolean('is_active', true);

        $ticketCategory->update($data);

        return redirect()->route('admin.ticket-categories.index')->with('success', 'Ticket Category updated successfully.');
    }

    public function destroy(TicketCategory $ticketCategory): RedirectResponse
    {
        $ticketCategory->delete();

        return redirect()->route('admin.ticket-categories.index')->with('success', 'Ticket Category deleted successfully.');
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);
        if (! empty($ids)) {
            TicketCategory::whereIn('id', $ids)->delete();
        }

        return redirect()->route('admin.ticket-categories.index')->with('success', 'Selected Ticket Categories deleted successfully.');
    }
}
