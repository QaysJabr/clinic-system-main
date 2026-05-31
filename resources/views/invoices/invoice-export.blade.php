@extends('layouts.export')

@section('title', __('invoices.print_page_title', ['number' => $invoice->invoice_number ?? '']))

@push('head')
    <style>
        @include('partials.pdf-document-styles')
    </style>
@endpush

@section('content')
    @include('invoices.partials.invoice-document', ['exportRender' => true])
@endsection
