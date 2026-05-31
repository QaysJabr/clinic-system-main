@props(['type'])

@php
    $model = $type instanceof \App\Enums\StaffCompensationModel ? $type : \App\Enums\StaffCompensationModel::from($type);
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold '.$model->badgeClass()]) }}>
    {{ $model->label() }}
</span>
