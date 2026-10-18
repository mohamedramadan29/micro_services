{{-- One editable field. $name = input name, $field = definition, $value = current value, $media = library. --}}
<div class="form-group {{ $field['type'] === 'textarea' ? 'col-12' : 'col-md-6' }}">
    <label class="mb-1">{{ $field['label'] }}@if (! empty($field['required'])) <span class="text-danger">*</span>@endif</label>
    @switch($field['type'])
        @case('textarea')
            <textarea name="{{ $name }}" rows="3" class="form-control">{{ $value }}</textarea>
            @break
        @case('select')
            <select name="{{ $name }}" class="form-control">
                @foreach ($field['options'] as $optionKey => $optionLabel)
                    <option value="{{ $optionKey }}" @selected((string) $value === (string) $optionKey)>{{ $optionLabel }}</option>
                @endforeach
            </select>
            @break
        @case('color')
            <div class="d-flex" style="gap: 6px">
                <input type="color" class="form-control" style="max-width: 60px" value="{{ $value ?: '#ffffff' }}" oninput="this.nextElementSibling.value = this.value">
                <input type="text" name="{{ $name }}" class="form-control" dir="ltr" placeholder="فارغ = الافتراضي" value="{{ $value }}">
            </div>
            @break
        @case('image')
        @case('background')
            <select name="{{ $name }}" class="form-control lp-media-select">
                <option value="">— بدون —</option>
                @foreach ($media as $item)
                    <option value="{{ $item->id }}" data-url="{{ $item->url() }}" @selected((int) $value === $item->id)>{{ $item->alt ?: ($item->original_name ?: basename($item->path)) }} ({{ \App\Models\ContentMedia::KINDS[$item->kind] ?? $item->kind }})</option>
                @endforeach
            </select>
            <img class="lp-thumb" alt="" @if ($value && ($m = $media->firstWhere('id', (int) $value))) src="{{ $m->url() }}" @else hidden @endif>
            @break
        @default
            <input type="text" name="{{ $name }}" class="form-control" value="{{ $value }}"
                @if (in_array($field['type'], ['link', 'icon'], true)) dir="ltr" @endif
                @if ($field['type'] === 'link') placeholder="/request أو #steps أو https://…" @endif
                @if ($field['type'] === 'icon') placeholder="bi-search" @endif>
    @endswitch
    @if (! empty($field['help']))<small class="text-muted">{{ $field['help'] }}</small>@endif
</div>
