@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="SLA Policy Configuration" />

    <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
        Configure response and resolution target times per priority level.
    </p>

    @if(session('success'))
        <x-ui.alert variant="success" message="{{ session('success') }}" />
    @endif

    <form method="POST" action="{{ route('sla-policies.store') }}">
        @csrf
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="max-w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead class="border-b border-gray-100 dark:border-gray-800">
                        <tr>
                            <th class="px-5 py-3 sm:px-6">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Ticket Priority</p>
                            </th>
                            <th class="px-5 py-3 sm:px-6">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">First Response Target (Minutes)</p>
                            </th>
                            <th class="px-5 py-3 sm:px-6">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Resolution Target (Hours)</p>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($priorities as $index => $priority)
                            @php
                                $policy = $policies[$priority->id] ?? null;
                            @endphp
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4 sm:px-6 font-medium text-gray-800 dark:text-white/90">
                                    <input type="hidden" name="policies[{{ $index }}][priority_id]" value="{{ $priority->id }}">
                                    {{ $priority->name }}
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <input type="number" name="policies[{{ $index }}][response_target_minutes]"
                                        value="{{ old("policies.{$index}.response_target_minutes", $policy->response_target_minutes ?? 60) }}" min="1" required
                                        class="h-10 w-48 rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <input type="number" name="policies[{{ $index }}][resolution_target_hours]"
                                        value="{{ old("policies.{$index}.resolution_target_hours", $policy->resolution_target_hours ?? 24) }}" min="1" required
                                        class="h-10 w-48 rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-100 p-4 dark:border-gray-800 sm:p-5 flex justify-end">
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                    <i data-feather="save" class="w-4 h-4"></i>
                    Save SLA Policies
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
