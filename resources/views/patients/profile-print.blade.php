@extends('layouts.print')

@section('title', __('patients.print_title_profile', ['name' => $patient->full_name]))

@section('content')
    @include('patients.partials.profile-document')
@endsection
