<?php

namespace App\Http\Controllers\Admin;

use App\Models\Source;

class SourceController extends LookupController
{
    protected function modelClass(): string
    {
        return Source::class;
    }

    protected function viewName(): string
    {
        return 'admin.sources';
    }

    protected function validationRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function title(): string
    {
        return 'Source';
    }
}
