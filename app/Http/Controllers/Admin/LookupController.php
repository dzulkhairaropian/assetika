<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

abstract class LookupController extends Controller
{
    abstract protected function modelClass(): string;

    abstract protected function viewName(): string;

    abstract protected function validationRules(): array;

    abstract protected function title(): string;

    protected function query(Request $request)
    {
        $model = $this->modelClass();

        $query = $model::query();

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active') && $request->input('is_active') !== null && $request->input('is_active') !== '') {
            $query->where('is_active', (bool) $request->input('is_active'));
        }

        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function index(Request $request): View
    {
        $model = $this->modelClass();
        $totalCount = $model::count();
        $activeCount = $model::where('is_active', true)->count();
        $inactiveCount = $model::where('is_active', false)->count();

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100, 250, 500]) ? (int) $request->input('per_page') : 50;
        $items = $this->query($request)->paginate($perPage)->withQueryString();

        return view('admin.lookups.index', [
            'title' => $this->title(),
            'items' => $items,
            'viewName' => $this->viewName(),
            'search' => $request->string('search')->toString(),
            'counts' => [
                'total' => $totalCount,
                'active' => $activeCount,
                'inactive' => $inactiveCount,
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.lookups.form', [
            'title' => $this->title(),
            'item' => null,
            'viewName' => $this->viewName(),
            'rules' => $this->validationRules(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->validationRules());
        $model = $this->modelClass();
        $model::create($data);

        return redirect()->route($this->viewName().'.index')->with('success', $this->title().' created.');
    }

    public function show(int $id): View
    {
        $model = $this->modelClass();
        $item = $model::query()->findOrFail($id);

        return view('admin.lookups.show', [
            'title' => $this->title(),
            'item' => $item,
            'viewName' => $this->viewName(),
        ]);
    }

    public function edit(int $id): View
    {
        $model = $this->modelClass();
        $item = $model::query()->findOrFail($id);

        return view('admin.lookups.form', [
            'title' => $this->title(),
            'item' => $item,
            'viewName' => $this->viewName(),
            'rules' => $this->validationRules(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $model = $this->modelClass();
        $item = $model::query()->findOrFail($id);
        $item->update($request->validate($this->validationRules($item)));

        return redirect()->route($this->viewName().'.index')->with('success', $this->title().' updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $model = $this->modelClass();
        $model::query()->findOrFail($id)->delete();

        return redirect()->route($this->viewName().'.index')->with('success', $this->title().' deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return redirect()->route($this->viewName().'.index')->with('error', 'No items selected.');
        }

        $model = $this->modelClass();
        $model::query()->whereIn('id', $ids)->delete();

        return redirect()->route($this->viewName().'.index')->with('success', count($ids).' '.mb_strtolower($this->title()).'(s) deleted.');
    }

    public function bulkEdit(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return redirect()->route($this->viewName().'.index')->with('error', 'No items selected.');
        }

        $request->validate([
            'ids' => 'required|array',
            'is_active' => 'nullable|in:0,1',
            'color' => 'nullable|string|max:255',
        ]);

        $data = [];
        if ($request->filled('is_active')) {
            $data['is_active'] = (bool) $request->input('is_active');
        }
        if ($request->filled('color')) {
            $data['color'] = $request->input('color');
        }

        if (empty($data)) {
            return redirect()->route($this->viewName().'.index')->with('error', 'No changes specified for bulk update.');
        }

        $model = $this->modelClass();
        $model::query()->whereIn('id', $ids)->update($data);

        return redirect()->route($this->viewName().'.index')->with('success', count($ids).' '.mb_strtolower($this->title()).'(s) updated successfully.');
    }
}
