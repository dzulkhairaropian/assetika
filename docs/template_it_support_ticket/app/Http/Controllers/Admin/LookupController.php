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

        return $query->latest();
    }

    public function index(Request $request): View
    {
        $items = $this->query($request)->paginate(10)->withQueryString();

        return view('admin.lookups.index', [
            'title' => $this->title(),
            'items' => $items,
            'viewName' => $this->viewName(),
            'search' => $request->string('search')->toString(),
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
}
