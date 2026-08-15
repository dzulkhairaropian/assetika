@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{}">
    <x-common.page-breadcrumb pageTitle="Create New Ticket" :items="[['label' => 'Tickets', 'url' => route('tickets.index')]]" />

        <p class="-mt-3 text-sm text-gray-500 dark:text-gray-400">
            Submit a new support ticket with requester, priority, and problem details.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif

    <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_380px]">
        @csrf
        @include('pages.tickets._form')
    </form>
</div>
@endsection
