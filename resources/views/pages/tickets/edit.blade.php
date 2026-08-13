@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Edit Ticket #{{ $ticket->request_id }}" />

    <form action="{{ route('tickets.update', $ticket) }}" method="POST" enctype="multipart/form-data" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_380px]">
        @csrf
        @method('PUT')
        @include('pages.tickets._form')
    </form>
</div>
@endsection
