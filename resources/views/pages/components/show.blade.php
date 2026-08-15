@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Component Detail" :items="[['label' => 'Components', 'url' => route('components.index')]]" />

        <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
            Process show.blade
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Component Info --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 lg:col-span-1">
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 flex items-center gap-2">
                    <i data-feather="cpu" class="w-5 h-5 text-brand-500"></i>
                    Component Info
                </h3>

                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Name</dt>
                        <dd class="text-sm text-gray-800 dark:text-white/90">{{ $component->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Category</dt>
                        <dd class="text-sm text-gray-800 dark:text-white/90">{{ $component->category?->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Manufacturer</dt>
                        <dd class="text-sm text-gray-800 dark:text-white/90">{{ $component->manufacturer ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Model Number</dt>
                        <dd class="text-sm text-gray-800 dark:text-white/90">{{ $component->model_number ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Serial</dt>
                        <dd class="text-sm text-gray-800 dark:text-white/90">{{ $component->serial ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Location</dt>
                        <dd class="text-sm text-gray-800 dark:text-white/90">{{ $component->location?->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Purchase Cost</dt>
                        <dd class="text-sm text-gray-800 dark:text-white/90">Rp {{ number_format($component->purchase_cost, 0, ',', '.') ?? '0' }}</dd>
                    </div>
                    @if($component->notes)
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Notes</dt>
                        <dd class="text-sm text-gray-800 dark:text-white/90">{{ $component->notes }}</dd>
                    </div>
                    @endif
                </dl>

                <div class="mt-6 flex gap-2">
                    <a href="{{ route('components.edit', $component) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 transition">
                        <i data-feather="edit-2" class="w-4 h-4"></i> Edit
                    </a>
                    <a href="{{ route('components.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                        Back
                    </a>
                </div>
            </div>

            {{-- Stock & Checkout --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 lg:col-span-2">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90 flex items-center gap-2">
                        <i data-feather="package" class="w-5 h-5 text-brand-500"></i>
                        Stock & Assignments
                    </h3>
                    <div class="flex items-center gap-4">
                        <div class="text-right">
                            <span class="block text-2xl font-bold text-gray-800 dark:text-white/90">{{ $component->qty }}</span>
                            <span class="text-xs text-gray-500">Total Qty</span>
                        </div>
                        <div class="h-10 w-px bg-gray-200 dark:bg-gray-700"></div>
                        <div class="text-right">
                            <span class="block text-2xl font-bold {{ $available <= $component->min_qty ? 'text-error-500' : 'text-brand-500' }}">
                                {{ $available }}
                            </span>
                            <span class="text-xs text-gray-500">Available</span>
                        </div>
                    </div>
                </div>

                {{-- Checkout Form --}}
                <form action="{{ route('components.checkout', $component) }}" method="POST" class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/50 mb-6">
                    @csrf
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Asset</label>
                            <select name="asset_id" required class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
                                <option value="">-- Select Asset --</option>
                                @foreach(\App\Models\Asset::orderBy('device_name')->get() as $asset)
                                    <option value="{{ $asset->id }}">{{ $asset->name }} ({{ $asset->asset_tag }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">Quantity</label>
                            <input type="number" name="qty" value="1" min="1" max="{{ $available }}" required
                                class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="h-10 w-full rounded-lg bg-brand-500 px-4 text-sm font-medium text-white hover:bg-brand-600 transition">
                                Checkout to Asset
                            </button>
                        </div>
                    </div>
                </form>

                {{-- Checked-out Assets --}}
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-400 mb-3">Checked Out Assets</h4>
                @if($checkouts->isNotEmpty())
                    <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-500">Asset</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-500">Qty</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-500">Date</th>
                                    <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-500">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                                @foreach($checkouts as $checkout)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-400">
                                            <a href="{{ route('assets.show', $checkout) }}" class="text-brand-500 hover:underline">{{ $checkout->name }}</a>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-400">{{ $checkout->pivot->qty }}</td>
                                        <td class="px-4 py-3 text-xs text-gray-500">{{ $checkout->pivot->checked_out_at ? \Carbon\Carbon::parse($checkout->pivot->checked_out_at)->format('d M Y') : '-' }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <form action="{{ route('components.checkin', ['component' => $component->id, 'asset' => $checkout->id]) }}" method="POST" class="inline-flex items-center gap-2">
                                                @csrf
                                                <input type="number" name="qty" value="{{ $checkout->pivot->qty }}" min="1" max="{{ $checkout->pivot->qty }}" class="h-8 w-16 rounded border border-gray-300 bg-white px-2 text-xs dark:border-gray-700 dark:bg-gray-900">
                                                <button type="submit" class="rounded bg-warning-100 px-2.5 py-1 text-xs font-medium text-warning-700 hover:bg-warning-200 dark:bg-warning-500/10 dark:text-warning-400">
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
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-6 text-center dark:border-gray-700 dark:bg-gray-900/50">
                        <p class="text-sm text-gray-500">No assets have this component checked out yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
