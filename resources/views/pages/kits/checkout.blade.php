@extends('layouts.app')

@section('content')
<div class="space-y-4">
    <x-common.page-breadcrumb pageTitle="Checkout Kit: {{ $kit->name }}" :items="[['label' => 'Kits', 'url' => route('kits.index')]]" />

    <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
        Deploy kit bundle items to a user in one step.
    </p>

    @if(session('success'))
        <x-ui.alert variant="success" message="{{ session('success') }}" />
    @endif

    <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="mb-5 border-b border-gray-100 pb-4 dark:border-gray-800">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Items in this Kit</h3>
            <ul class="space-y-1.5 text-xs text-gray-600 dark:text-gray-400">
                @forelse($kit->assets as $asset) <li class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-blue-500"></span> {{ $asset->name }} ({{ $asset->asset_tag }}) x{{ $asset->pivot->qty }}</li> @endforeach
                @forelse($kit->licenses as $license) <li class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-green-500"></span> {{ $license->name }} x{{ $license->pivot->qty }}</li> @endforeach
                @forelse($kit->components as $component) <li class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-purple-500"></span> {{ $component->name }} x{{ $component->pivot->qty }}</li> @endforeach
                @forelse($kit->consumables as $consumable) <li class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-orange-500"></span> {{ $consumable->name }} x{{ $consumable->pivot->qty }}</li> @endforeach
                @if($kit->assets->isEmpty() && $kit->licenses->isEmpty() && $kit->components->isEmpty() && $kit->consumables->isEmpty()) <li class="text-gray-400 italic">No items in kit</li> @endif
            </ul>
        </div>

        <form action="{{ route('kits.checkout', $kit) }}" method="POST">
            @csrf
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Checkout to User <span class="text-red-500">*</span></label>
                <select name="user_id" required class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                    <option value="">-- Select User --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
                @error('user_id') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-center justify-end gap-2.5 mt-5 border-t border-gray-100 pt-3 dark:border-gray-800">
                <a href="{{ route('kits.show', $kit) }}" class="inline-flex justify-center rounded-lg border border-gray-200 bg-white px-3.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 transition">Cancel</a>
                <button type="submit" class="inline-flex justify-center rounded-lg bg-brand-500 px-4 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 transition">Checkout All Items</button>
            </div>
        </form>
    </div>
</div>
@endsection
