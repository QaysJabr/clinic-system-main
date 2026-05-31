@extends('layouts.print')

@section('title', __('expenses.print_payment_page_title', ['tag' => __('expenses.pdf_tag_expense_payment'), 'number' => $expense->documentNumber()]))

@section('content')
    @include('expenses.partials.payment-document')
@endsection
