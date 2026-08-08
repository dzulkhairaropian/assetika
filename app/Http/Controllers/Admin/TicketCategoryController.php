<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketCategory;
use App\Models\TicketSubcategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = TicketCategory::with('subcategories');

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%");
        }

        $categories = $query->latest()->paginate(15)->withQueryString();

        return view('pages.admin.ticket-categories.index', compact('categories', 'search'));
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ]);

        if (empty($data['code'])) {
            $data['code'] = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $data['name']), 0, 8));
        }

        TicketCategory::create($data);

        return redirect()->back()->with('success', 'Ticket Category created successfully.');
    }

    public function updateCategory(Request $request, TicketCategory $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $category->update($data);

        return redirect()->back()->with('success', 'Ticket Category updated successfully.');
    }

    public function destroyCategory(TicketCategory $category): RedirectResponse
    {
        $category->delete();

        return redirect()->back()->with('success', 'Ticket Category deleted successfully.');
    }

    public function storeSubcategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ticket_category_id' => ['required', 'exists:ticket_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ]);

        if (empty($data['code'])) {
            $data['code'] = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $data['name']), 0, 8));
        }

        TicketSubcategory::create($data);

        return redirect()->back()->with('success', 'Ticket Subcategory created successfully.');
    }

    public function destroySubcategory(TicketSubcategory $subcategory): RedirectResponse
    {
        $subcategory->delete();

        return redirect()->back()->with('success', 'Ticket Subcategory deleted successfully.');
    }

    public function getSubcategories(TicketCategory $category)
    {
        return response()->json($category->subcategories()->where('is_active', true)->get());
    }
}
