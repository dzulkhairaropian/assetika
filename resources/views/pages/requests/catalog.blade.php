@extends('layouts.app')
@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Request Asset / Peripheral" />

        <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
            Browse available IT items and submit equipment requests.
        </p>
    @if(session('success')) <x-ui.alert variant="success" message="{{ session('success') }}" /> @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            {{-- Category Request --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <h3 class="text-base font-semibold mb-4 flex items-center gap-2"><i data-feather="hard-drive" class="w-4 h-4 text-brand-500"></i> Request Hardware Category</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($categories as $cat)
                        <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700 flex items-center justify-between">
                            <div>
                                <h4 class="font-medium text-sm text-gray-800 dark:text-white">{{ $cat->name }}</h4>
                                <p class="text-xs text-gray-400">IT Hardware Category</p>
                            </div>
                            <form action="{{ route('asset-requests.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="requestable_type" value="category">
                                <input type="hidden" name="requestable_id" value="{{ $cat->id }}">
                                <button type="submit" class="rounded bg-brand-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-600">Request</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Consumables Request --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <h3 class="text-base font-semibold mb-4 flex items-center gap-2"><i data-feather="package" class="w-4 h-4 text-brand-500"></i> Request Consumable / Item</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($consumables as $con)
                        <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700 flex items-center justify-between">
                            <div>
                                <h4 class="font-medium text-sm text-gray-800 dark:text-white">{{ $con->product?->name ?? 'Consumable' }}</h4>
                                <p class="text-xs text-gray-400">Stock: {{ $con->quantity }} available</p>
                            </div>
                            <form action="{{ route('asset-requests.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="requestable_type" value="consumable">
                                <input type="hidden" name="requestable_id" value="{{ $con->id }}">
                                <button type="submit" class="rounded bg-brand-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-600">Request</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- My Recent Requests --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:col-span-1">
            <h3 class="text-base font-semibold mb-4 flex items-center gap-2"><i data-feather="clock" class="w-4 h-4 text-brand-500"></i> My Recent Requests</h3>
            <div class="space-y-3">
                @forelse($myRequests as $req)
                    <div class="rounded-lg border border-gray-100 p-3 dark:border-gray-800">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-800 dark:text-white">{{ $req->requestable?->name ?? 'Item' }}</span>
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-medium {{ $req->status === 'approved' ? 'bg-green-100 text-green-800' : ($req->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-warning-100 text-warning-800') }}">{{ ucfirst($req->status) }}</span>
                        </div>
                        <span class="text-[10px] text-gray-400 block mt-1">{{ $req->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 italic">No requests submitted yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
