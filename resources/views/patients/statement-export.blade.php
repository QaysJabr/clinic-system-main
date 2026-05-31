@extends('layouts.export')

@section('title', __('patients.print_title_statement', ['name' => $patient->full_name]))

@push('head')
    <style>
        @include('partials.pdf-document-styles')
    </style>
@endpush

@section('content')
    @include('patients.partials.statement-document', ['exportRender' => true])
@endsection
