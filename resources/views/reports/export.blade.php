@extends('layouts.export')

@section('title', __('reports.page_title'))

@push('head')
    @include('partials.export-styles')
@endpush

@section('content')
    @include('reports.partials.print-document', ['exportRender' => true])
@endsection
