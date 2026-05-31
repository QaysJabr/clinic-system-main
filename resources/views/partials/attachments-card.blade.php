@php
    use App\Enums\AttachmentCategory;
    $canView = auth()->user()->can('view attachments') || auth()->user()->can('manage attachments');
    $canManage = auth()->user()->can('manage attachments');
    $showCategories = $showCategories ?? false;
@endphp

@if($canView)
<div class="mt-8 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
    <div class="bg-[#1F7A8C] px-4 py-3 text-white sm:px-5">
        <h2 class="text-base font-bold m-0">{{ __('common.attachments_title') }}</h2>
    </div>
    <div class="p-5 sm:p-6">
        @if($canManage)
            <form action="{{ $uploadUrl }}" method="POST" enctype="multipart/form-data" class="mb-6 space-y-4 rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-4 sm:p-5">
                @csrf
                <p class="text-sm font-semibold text-gray-800 dark:text-[#F3F4F6] m-0">{{ __('common.attachments_upload_heading') }}</p>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="attachment_file" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('common.attachments_file_label') }}</label>
                        <input type="file" name="file" id="attachment_file" required accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/*"
                            class="@error('file') border-red-300 @else border-slate-200 @enderror block w-full rounded-lg border border-dashed bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm file:me-4 file:rounded-md file:border-0 file:bg-[#0F4C81] file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-white hover:file:bg-[#0c3d66] focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20">
                        <p class="mt-1.5 text-xs text-gray-500 dark:text-[#9CA3AF] m-0">{{ __('common.attachments_file_hint') }}</p>
                        @error('file')
                            <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    @if($showCategories)
                    <div>
                        <label for="attachment_category" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('emr.attachment_category') }}</label>
                        <select name="category" id="attachment_category" class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm dark:border-[#374151] dark:bg-[#111827]">
                            @foreach (AttachmentCategory::cases() as $cat)
                                <option value="{{ $cat->value }}">{{ __($cat->labelKey()) }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="md:col-span-2">
                        <label for="attachment_notes" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]">{{ __('common.attachments_notes_label') }}</label>
                        <input type="text" name="notes" id="attachment_notes" value="{{ old('notes') }}"
                            class="@error('notes') border-red-300 @else border-slate-200 @enderror block w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20">
                        @error('notes')
                            <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">{{ __('common.attachments_upload_button') }}</button>
                </div>
            </form>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50 dark:border-[#374151] dark:bg-[#111827]">
                        <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('common.attachments_col_filename') }}</th>
                        @if($showCategories)<th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('emr.attachment_category') }}</th>@endif
                        <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5">{{ __('common.attachments_col_type') }}</th>
                        <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5 whitespace-nowrap">{{ __('common.attachments_col_size') }}</th>
                        <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5 whitespace-nowrap">{{ __('common.attachments_col_uploaded') }}</th>
                        <th class="px-4 py-3 text-start font-bold text-gray-700 dark:text-[#E5E7EB] sm:px-5 w-[1%] whitespace-nowrap">{{ __('common.attachments_col_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attachments as $att)
                        <tr class="border-b border-gray-100 transition-colors hover:bg-gray-50 dark:hover:bg-[#111827]/80">
                            <td class="px-4 py-3 font-medium text-gray-900 sm:px-5">{{ $att->file_name }}</td>
                            @if($showCategories)
                            <td class="px-4 py-3 text-gray-600 sm:px-5">{{ __(AttachmentCategory::tryFrom($att->category ?? 'document')?->labelKey() ?? 'emr.attachment_category_document') }}</td>
                            @endif
                            <td class="px-4 py-3 text-gray-600 dark:text-[#9CA3AF] sm:px-5">{{ \Illuminate\Support\Str::limit($att->file_type, 40) }}</td>
                            <td class="px-4 py-3 tabular-nums text-gray-700 dark:text-[#E5E7EB] sm:px-5">
                                @if($att->file_size !== null)
                                    {{ $att->file_size >= 1048576 ? __('common.attachments_size_mb', ['size' => number_format($att->file_size / 1048576, 2)]) : __('common.attachments_size_kb', ['size' => number_format($att->file_size / 1024, 1)]) }}
                                @else
                                    {{ __('common.em_dash') }}
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-[#9CA3AF] sm:px-5 whitespace-nowrap">{{ $att->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 sm:px-5 whitespace-nowrap">
                                <div class="flex flex-wrap items-center gap-2 justify-end">
                                    @can('view', $att)
                                    @if(str_contains((string) $att->file_type, 'image') || str_contains((string) $att->file_type, 'pdf'))
                                    <a href="{{ route('attachments.preview', $att) }}" target="_blank" class="inline-flex items-center justify-center rounded-lg border border-teal-600 bg-white px-3 py-1.5 text-xs font-semibold text-teal-800 hover:bg-teal-50">{{ __('emr.preview') }}</a>
                                    @endif
                                    <a href="{{ route('attachments.download', $att) }}" class="inline-flex items-center justify-center rounded-lg border border-[#0F4C81] bg-white px-3 py-1.5 text-xs font-semibold text-[#0F4C81] hover:bg-blue-50 dark:border-[#3B82F6]/55 dark:bg-[#111827] dark:text-[#93C5FD] dark:hover:bg-[#1E3A5F]/50">{{ __('common.attachments_download') }}</a>
                                    @endcan
                                    @if($canManage)
                                        <form method="POST" action="{{ route('attachments.destroy', $att) }}" class="inline" data-confirm-title="{{ __('common.attachments_confirm_delete_title') }}" data-confirm="{{ __('common.attachments_confirm_delete_body') }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50">{{ __('common.attachments_delete') }}</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $showCategories ? 6 : 5 }}" class="px-4 py-8 text-center text-gray-500 dark:text-[#9CA3AF] sm:px-5">{{ __('common.attachments_empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
