@extends('layouts.export')

@section('title', __('expenses.print_page_title', ['number' => $expense->documentNumber()]))

@push('head')
    @include('partials.export-styles')
@endpush

@section('content')
    @include('expenses.partials.print-document', ['exportRender' => true])
@endsection
