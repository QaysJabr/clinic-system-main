@extends('layouts.print')

@section('title', __('expenses.print_page_title', ['number' => $expense->documentNumber()]))

@section('content')
    @include('expenses.partials.print-document')
@endsection
