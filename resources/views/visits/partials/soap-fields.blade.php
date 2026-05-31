@php
    $soap = $soap ?? ['subjective' => '', 'objective' => '', 'assessment' => '', 'plan' => '', 'notes' => ''];
    $inputClass = $inputClass ?? 'block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25';
    $labelClass = $labelClass ?? 'mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]';
@endphp
<div class="overflow-hidden rounded-xl border border-[#0F4C81]/20 bg-blue-50/40 dark:border-blue-500/30 dark:bg-blue-950/20">
    <div class="border-b border-[#0F4C81]/10 bg-blue-50/60 px-4 py-4 dark:border-blue-500/20 dark:bg-blue-950/30 sm:px-5">
        <h3 class="m-0 text-sm font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('emr.soap_heading') }}</h3>
        <p class="m-0 mt-1 text-xs text-gray-500 dark:text-slate-400">{{ __('emr.legacy_fields_hint') }}</p>
    </div>
    <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 sm:p-5">
        <div>
            <label for="soap_subjective" class="{{ $labelClass }}">{{ __('emr.soap_subjective') }}</label>
            <textarea name="soap_subjective" id="soap_subjective" rows="3" class="{{ $inputClass }}">{{ old('soap_subjective', $soap['subjective']) }}</textarea>
        </div>
        <div>
            <label for="soap_objective" class="{{ $labelClass }}">{{ __('emr.soap_objective') }}</label>
            <textarea name="soap_objective" id="soap_objective" rows="3" class="{{ $inputClass }}">{{ old('soap_objective', $soap['objective']) }}</textarea>
        </div>
        <div>
            <label for="soap_assessment" class="{{ $labelClass }}">{{ __('emr.soap_assessment') }}</label>
            <textarea name="soap_assessment" id="soap_assessment" rows="3" class="{{ $inputClass }}">{{ old('soap_assessment', $soap['assessment']) }}</textarea>
        </div>
        <div>
            <label for="soap_plan" class="{{ $labelClass }}">{{ __('emr.soap_plan') }}</label>
            <textarea name="soap_plan" id="soap_plan" rows="3" class="{{ $inputClass }}">{{ old('soap_plan', $soap['plan']) }}</textarea>
        </div>
        <div class="sm:col-span-2">
            <label for="visit_notes" class="{{ $labelClass }}">{{ __('emr.soap_supplemental_notes') }}</label>
            <textarea name="notes" id="visit_notes" rows="2" class="{{ $inputClass }}">{{ old('notes', $soap['notes']) }}</textarea>
        </div>
    </div>
</div>
