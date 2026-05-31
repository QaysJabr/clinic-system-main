@extends('layouts.print')

@section('title', __('reports.page_title'))

@section('content')
    @include('reports.partials.print-document')
@endsection
