@extends('layouts.app')

@section('content')
<div class="space-y-4">
    <x-common.page-breadcrumb pageTitle="Edit Ticket #{{ $ticket->request_id }}" :items="[['label' => 'Tickets', 'url' => route('tickets.index')]]" />

    <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
        Update ticket details for request {{ $ticket->request_id }}.
    </p>

    @if(session('success'))
        <x-ui.alert variant="success" message="{{ session('success') }}" />
    @endif

    <form action="{{ route('tickets.update', $ticket) }}" method="POST" enctype="multipart/form-data" class="grid gap-4 xl:grid-cols-[minmax(0,1.75fr)_340px]">
        @csrf
        @method('PUT')
        @include('pages.tickets._form')
    </form>
</div>
@endsection
