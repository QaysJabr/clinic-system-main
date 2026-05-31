<form method="POST" action="{{ $template ? route('inventory.templates.update', $template) : route('inventory.templates.store') }}" class="space-y-4 border rounded-xl p-6 bg-white dark:bg-[#1F2937]">
@csrf @if($template) @method('PUT') @endif
<input type="text" name="name" value="{{ old('name', $template?->name) }}" required class="w-full rounded-lg border px-3 py-2" placeholder="{{ __('inventory.field_template_name') }}">
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="auto_consume" value="1" @checked(old('auto_consume', $template?->auto_consume ?? true))> {{ __('inventory.field_auto_consume') }}</label>
@php $lines = old('lines', $template ? $template->lines->map(fn($l)=>['inventory_item_id'=>$l->inventory_item_id,'quantity'=>$l->quantity])->all() : [['inventory_item_id'=>'','quantity'=>1]]); @endphp
@foreach($lines as $idx => $line)
<div class="flex gap-2">
<select name="lines[{{ $idx }}][inventory_item_id]" class="flex-1 rounded-lg border px-2 py-2">
<option value="">—</option>
@foreach($items as $i)<option value="{{ $i->id }}" @selected(($line['inventory_item_id']??'')==$i->id)>{{ $i->name }}</option>@endforeach
</select>
<input type="number" step="0.001" name="lines[{{ $idx }}][quantity]" value="{{ $line['quantity'] ?? 1 }}" class="w-24 rounded-lg border px-2 py-2">
</div>
@endforeach
<button type="submit" class="rounded-lg bg-[#0F4C81] px-4 py-2 text-white font-bold">{{ __('common.save') }}</button>
</form>
