@extends('layouts.pdf')

@section('title', $invoice->invoice_number)

@section('content')
    @include('invoices.partials.invoice-document')
@endsection
