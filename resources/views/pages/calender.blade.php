@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Calendar & Events" />
        <x-calender-area />
    </div>
@endsection
