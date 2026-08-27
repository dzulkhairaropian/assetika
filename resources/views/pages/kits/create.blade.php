@extends('layouts.app')

@section('content')
<div class="space-y-4">
    <x-common.page-breadcrumb pageTitle="Create Kit" :items="[['label' => 'Kits', 'url' => route('kits.index')]]" />

    <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
        Create a new equipment kit bundle.
    </p>

    @if(session('success')) <x-ui.alert variant="success" message="{{ session('success') }}" /> @endif

    <form action="{{ route('kits.store') }}" method="POST" class="grid gap-4 xl:grid-cols-[minmax(0,1.75fr)_340px]">
        @csrf

        <div class="space-y-4">
            <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2 border-b border-gray-100 pb-2.5 dark:border-gray-800">
                        <i data-feather="box" class="w-4 h-4 text-brand-500"></i> Kit Details
                    </h3>
                    <div class="grid grid-cols-1 gap-x-4 gap-y-3 lg:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Kit Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g., Kit Developer" required
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Description</label>
                            <input type="text" name="description" value="{{ old('description') }}" placeholder="Brief description"
                                class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white" />
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
                        <div class="mt-4">
                            <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">{{ $section['label'] }}</h4>
                            <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800 max-h-48 overflow-y-auto">
                                <table class="w-full text-xs">
                                    <thead class="border-b border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-950 sticky top-0">
                                        <tr>
                                            <th class="px-3 py-2 text-start font-medium text-gray-500 w-12">Add</th>
                                            <th class="px-3 py-2 text-start font-medium text-gray-500">Name</th>
                                            <th class="px-3 py-2 text-start font-medium text-gray-500 w-24">Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        @foreach($section['items'] as $item)
                                            @php $itemName = $section['key'] === 'consumables' ? ($item->product?->name ?? 'Consumable') : $item->name; @endphp
                                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                                                <td class="px-3 py-1.5"><input type="checkbox" name="{{ $section['key'] }}[{{ $loop->index }}][id]" value="{{ $item->id }}" class="rounded border-gray-300 text-brand-500 focus:ring-brand-500"></td>
                                                <td class="px-3 py-1.5 text-gray-800 dark:text-gray-200 font-medium">{{ $itemName }}</td>
                                                <td class="px-3 py-1.5"><input type="number" name="{{ $section['key'] }}[{{ $loop->index }}][qty]" value="1" min="1" class="h-7 w-16 rounded border border-gray-200 bg-gray-50 px-2 text-xs text-gray-800 focus:border-brand-500 focus:bg-white dark:border-gray-800 dark:bg-gray-950 dark:text-white"></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-3 flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                    <i data-feather="save" class="w-4 h-4 text-brand-500"></i>
                    Actions
                </h3>
                <div class="space-y-2.5">
                    <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                        Create Kit
                    </button>
                    <button type="submit" name="create_another" value="1" class="inline-flex w-full justify-center rounded-lg border border-brand-500 bg-white px-4 py-2 text-xs font-semibold text-brand-600 hover:bg-brand-50 dark:border-brand-500 dark:bg-gray-950 dark:text-brand-400 transition">
                        Create & Create Another
                    </button>
                    <a href="{{ route('kits.index') }}" class="inline-flex w-full justify-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 transition">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
