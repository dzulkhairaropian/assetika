@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{}">
    <x-common.page-breadcrumb pageTitle="Create New Ticket" />

    <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_380px]">
        @csrf
        @include('pages.tickets._form')
    </form>
</div>
@endsection
