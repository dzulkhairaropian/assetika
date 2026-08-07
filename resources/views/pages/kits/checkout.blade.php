@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Checkout Kit: {{ $kit->name }}" />

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-2">Items in this Kit</h3>
            <ul class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                @forelse($kit->assets as $asset) <li class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-blue-500"></span> {{ $asset->name }} ({{ $asset->asset_tag }}) x{{ $asset->pivot->qty }}</li> @endforeach
                @forelse($kit->licenses as $license) <li class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-green-500"></span> {{ $license->name }} x{{ $license->pivot->qty }}</li> @endforeach
                @forelse($kit->components as $component) <li class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-purple-500"></span> {{ $component->name }} x{{ $component->pivot->qty }}</li> @endforeach
                @forelse($kit->consumables as $consumable) <li class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-orange-500"></span> {{ $consumable->name }} x{{ $consumable->pivot->qty }}</li> @endforeach
                @if($kit->assets->isEmpty() && $kit->licenses->isEmpty()) <li class="text-gray-400 italic">No items in kit</li> @endif
            </ul>
        </div>

        <form action="{{ route('kits.checkout', $kit) }}" method="POST">
            @csrf
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Checkout to User <span class="text-error-500">*</span></label>
                <select name="user_id" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    <option value="">-- Select User --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
                @error('user_id') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-center justify-end gap-3 mt-6">
                <a href="{{ route('kits.show', $kit) }}" class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">Cancel</a>
                <button type="submit" class="inline-flex justify-center rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition">Checkout All Items</button>
            </div>
        </form>
    </div>
</div>
@endsection
