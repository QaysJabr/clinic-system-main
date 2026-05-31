@extends('layouts.pdf')

@section('title', __('patients.print_title_statement', ['name' => $patient->full_name]))

@section('content')
    @include('patients.partials.statement-document')
@endsection
