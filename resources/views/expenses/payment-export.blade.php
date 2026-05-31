@extends('layouts.export')

@section('title', __('expenses.print_payment_page_title', ['tag' => __('expenses.pdf_tag_expense_payment'), 'number' => $expense->documentNumber()]))

@push('head')
    @include('partials.export-styles')
@endpush

@section('content')
    @include('expenses.partials.payment-document')
@endsection
