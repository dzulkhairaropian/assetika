<?php

namespace App\Http\Controllers\Admin;

use App\Models\Priority;

class PriorityController extends LookupController
{
    protected function modelClass(): string
    {
        return Priority::class;
    }

    protected function viewName(): string
    {
        return 'admin.priorities';
    }

    protected function validationRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255'],
            'label' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:255'],
            'response_time_minutes' => ['nullable', 'integer', 'min:0'],
            'resolution_time_hours' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'example_cases' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function title(): string
    {
        return 'Priority';
    }
}
