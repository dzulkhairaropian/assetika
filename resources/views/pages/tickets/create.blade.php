@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <x-common.page-breadcrumb pageTitle="Create New Ticket" :items="[['label' => 'Tickets', 'url' => route('tickets.index')], ['label' => 'Create']]" />
            <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
                Submit an IT support incident, hardware fault, or service request.
            </p>
        </div>
        <a href="{{ route('tickets.index') }}"
           class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            Back to Tickets
        </a>
    </div>

    @if(session('success'))
        <x-ui.alert variant="success" message="{{ session('success') }}" />
    @endif
    @if(session('error'))
        <x-ui.alert variant="error" message="{{ session('error') }}" />
    @endif

    <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_360px]">
        @csrf
        @include('pages.tickets._form')
    </form>
</div>
@endsection
