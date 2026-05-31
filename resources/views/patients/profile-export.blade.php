@extends('layouts.export')

@section('title', __('patients.print_title_profile', ['name' => $patient->full_name]))

@push('head')
    @include('partials.export-styles')
    <style>
        @include('partials.pdf-document-styles')
    </style>
@endpush

@section('content')
    @include('patients.partials.profile-document', ['exportRender' => true])
@endsection
