@extends('layouts.app')

@section('content')
    <div class="space-y-4">
        <x-common.page-breadcrumb pageTitle="Component Detail" :items="[['label' => 'Components', 'url' => route('components.index')]]" />

        <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
            View stock, specification, and assigned assets for component {{ $component->name }}.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            {{-- Component Info --}}
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 lg:col-span-1">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                    <i data-feather="cpu" class="w-4 h-4 text-brand-500"></i>
                    Component Info
                </h3>

                <dl class="space-y-2.5">
                    <div>
                        <dt class="text-[11px] font-medium text-gray-400">Name</dt>
                        <dd class="text-xs font-semibold text-gray-900 dark:text-white">{{ $component->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-medium text-gray-400">Category</dt>
                        <dd class="text-xs font-medium text-gray-800 dark:text-gray-200">{{ $component->category?->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-medium text-gray-400">Manufacturer</dt>
                        <dd class="text-xs font-medium text-gray-800 dark:text-gray-200">{{ $component->manufacturer ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-medium text-gray-400">Model Number</dt>
                        <dd class="text-xs font-medium text-gray-800 dark:text-gray-200">{{ $component->model_number ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-medium text-gray-400">Serial</dt>
                        <dd class="text-xs font-mono font-medium text-gray-800 dark:text-gray-200">{{ $component->serial ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-medium text-gray-400">Location</dt>
                        <dd class="text-xs font-medium text-gray-800 dark:text-gray-200">{{ $component->location?->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-medium text-gray-400">Purchase Cost</dt>
                        <dd class="text-xs font-semibold text-gray-900 dark:text-white">Rp {{ number_format($component->purchase_cost, 0, ',', '.') ?? '0' }}</dd>
                    </div>
                    @if($component->notes)
                    <div>
                        <dt class="text-[11px] font-medium text-gray-400">Notes</dt>
                        <dd class="text-xs text-gray-600 dark:text-gray-400 italic">{{ $component->notes }}</dd>
                    </div>
                    @endif
                </dl>

                <div class="mt-5 flex gap-2 pt-3 border-t border-gray-100 dark:border-gray-800">
                    <a href="{{ route('components.edit', $component) }}" class="inline-flex items-center gap-1 rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 transition">
                        <i data-feather="edit-2" class="w-3.5 h-3.5"></i> Edit
                    </a>
                    <a href="{{ route('components.index') }}" class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 transition">
                        Back
                    </a>
                </div>
            </div>

            {{-- Stock & Checkout --}}
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 lg:col-span-2">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <i data-feather="package" class="w-4 h-4 text-brand-500"></i>
                        Stock & Assignments
                    </h3>
                    <div class="flex items-center gap-3">
                        <div class="text-right">
                            <span class="block text-xl font-bold text-gray-900 dark:text-white">{{ $component->qty }}</span>
                            <span class="text-[11px] text-gray-400">Total Qty</span>
                        </div>
                        <div class="h-8 w-px bg-gray-200 dark:bg-gray-800"></div>
                        <div class="text-right">
                            <span class="block text-xl font-bold {{ $available <= $component->min_qty ? 'text-red-600 dark:text-red-400' : 'text-brand-600 dark:text-brand-400' }}">
                                {{ $available }}
                            </span>
                            <span class="text-[11px] text-gray-400">Available</span>
                        </div>
                    </div>
                </div>

                {{-- Checkout Form --}}
                <form action="{{ route('components.checkout', $component) }}" method="POST" class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-800 dark:bg-gray-950 mb-4">
                    @csrf
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Asset</label>
                            <select name="asset_id" required class="h-8 w-full rounded-lg border border-gray-200 bg-white px-2.5 text-xs dark:border-gray-800 dark:bg-gray-900">
                                <option value="">-- Select Asset --</option>
                                @foreach(\App\Models\Asset::orderBy('device_name')->get() as $asset)
                                    <option value="{{ $asset->id }}">{{ $asset->name }} ({{ $asset->asset_tag }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Quantity</label>
                            <input type="number" name="qty" value="1" min="1" max="{{ $available }}" required
                                class="h-8 w-full rounded-lg border border-gray-200 bg-white px-2.5 text-xs dark:border-gray-800 dark:bg-gray-900">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="h-8 w-full rounded-lg bg-brand-500 px-3 text-xs font-semibold text-white hover:bg-brand-600 transition">
                                Checkout to Asset
                            </button>
                        </div>
                    </div>
                </form>

                {{-- Checked-out Assets --}}
                <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Checked Out Assets</h4>
                @if($checkouts->isNotEmpty())
                    <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800">
                        <table class="w-full text-xs">
                            <thead class="bg-gray-50/50 dark:bg-gray-800/30 border-b border-gray-100 dark:border-gray-800">
                                <tr>
                                    <th class="px-3 py-2 text-start font-medium text-gray-500">Asset</th>
                                    <th class="px-3 py-2 text-start font-medium text-gray-500">Qty</th>
                                    <th class="px-3 py-2 text-start font-medium text-gray-500">Date</th>
                                    <th class="px-3 py-2 text-end font-medium text-gray-500">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach($checkouts as $checkout)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                                        <td class="px-3 py-2">
                                            <a href="{{ route('assets.show', $checkout) }}" class="font-medium text-brand-600 hover:underline dark:text-brand-400">{{ $checkout->name }}</a>
                                        </td>
                                        <td class="px-3 py-2 font-semibold text-gray-900 dark:text-white">{{ $checkout->pivot->qty }}</td>
                                        <td class="px-3 py-2 text-gray-500">{{ $checkout->pivot->checked_out_at ? \Carbon\Carbon::parse($checkout->pivot->checked_out_at)->format('d M Y') : '-' }}</td>
                                        <td class="px-3 py-2 text-end">
                                            <form action="{{ route('components.checkin', ['component' => $component->id, 'asset' => $checkout->id]) }}" method="POST" class="inline-flex items-center gap-1.5 justify-end">
                                                @csrf
                                                <input type="number" name="qty" value="{{ $checkout->pivot->qty }}" min="1" max="{{ $checkout->pivot->qty }}" class="h-7 w-14 rounded border border-gray-200 bg-white px-1.5 text-center text-xs dark:border-gray-800 dark:bg-gray-950">
                                                <button type="submit" class="rounded bg-orange-50 px-2 py-1 text-xs font-semibold text-orange-700 hover:bg-orange-100 dark:bg-orange-500/10 dark:text-orange-400 transition">
                                                    Checkin
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="rounded-lg border border-gray-200 bg-gray-50/50 p-4 text-center dark:border-gray-800 dark:bg-gray-950">
                        <p class="text-xs text-gray-400">No assets have this component checked out yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
