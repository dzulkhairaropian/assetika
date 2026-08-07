@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Create Kit" />
    @if(session('success')) <x-ui.alert variant="success" message="{{ session('success') }}" /> @endif

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <form action="{{ route('kits.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Kit Name <span class="text-error-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g., Kit Developer" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Description</label>
                    <input type="text" name="description" value="{{ old('description') }}" placeholder="Brief description" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                </div>
            </div>

            @php $sections = [
                ['key' => 'assets', 'label' => 'Assets', 'items' => $assets, 'idField' => 'id', 'nameField' => 'name'],
                ['key' => 'licenses', 'label' => 'Licenses', 'items' => $licenses, 'idField' => 'id', 'nameField' => 'name'],
                ['key' => 'components', 'label' => 'Components', 'items' => $components, 'idField' => 'id', 'nameField' => 'name'],
                ['key' => 'consumables', 'label' => 'Consumables', 'items' => $consumables, 'idField' => 'id', 'nameField' => 'name'],
            ];
            @endphp

            @foreach($sections as $section)
                @if($section['items']->isNotEmpty())
                <div class="mt-6">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-400 mb-2">{{ $section['label'] }}</h3>
                    <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 max-h-48 overflow-y-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-900 sticky top-0">
                                <tr><th class="px-4 py-2 text-left text-xs font-medium text-gray-500 w-12">Add</th><th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Name</th><th class="px-4 py-2 text-left text-xs font-medium text-gray-500 w-24">Qty</th></tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                                @foreach($section['items'] as $item)
                                    <tr>
                                        <td class="px-4 py-2"><input type="checkbox" name="{{ $section['key'] }}[{{ $loop->index }}][id]" value="{{ $item->{$section['idField']} }}" class="rounded border-gray-300 text-brand-500 focus:ring-brand-500"></td>
                                        <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-400">{{ $item->{$section['nameField']} }}</td>
                                        <td class="px-4 py-2"><input type="number" name="{{ $section['key'] }}[{{ $loop->index }}][qty]" value="1" min="1" class="h-8 w-16 rounded border border-gray-300 bg-white px-2 text-xs dark:border-gray-700 dark:bg-gray-900"></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            @endforeach

            <div class="flex items-center justify-end gap-3 mt-6">
                <a href="{{ route('kits.index') }}" class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">Cancel</a>
                <button type="submit" name="create_another" value="1" class="inline-flex justify-center rounded-lg border border-brand-500 bg-white px-4 py-2.5 text-sm font-medium text-brand-500 hover:bg-brand-50 transition">Create & Create New</button>
                <button type="submit" class="inline-flex justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition">Create Kit</button>
            </div>
        </form>
    </div>
</div>
@endsection
