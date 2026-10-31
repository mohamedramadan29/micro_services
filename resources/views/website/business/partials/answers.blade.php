{{-- A company request's content: the free text, or the guided answers (snapshot), with its files. Expects $item. --}}
@if ($item->details)
    <div class="bz-text">{{ $item->details }}</div>
@endif
@if ($item->answers)
    @foreach (collect($item->answers)->groupBy('section') as $section => $rows)
        <h6 class="bz-answers-title">{{ $section ?: 'التفاصيل' }}</h6>
        <dl class="bz-answers">
            @foreach ($rows as $row)
                @php
                    $value = $row['value'];
                    $text = match (true) {
                        $row['type'] === 'file' => ($row['files'] ?? 0) . ' ملفات (في المرفقات)',
                        is_array($value) && array_key_exists('min', $value) => trim(($value['min'] ?? '—') . ' – ' . ($value['max'] ?? '—') . ' ' . ($value['unit'] ?? '')),
                        is_array($value) && array_key_exists('value', $value) => $value['value'] . ' / ' . ($value['scale'] ?? 5),
                        is_array($value) => implode('، ', $value),
                        default => (string) $value,
                    };
                @endphp
                <dt>{{ $row['label'] }}</dt>
                <dd>@if ($row['type'] === 'url')<a href="{{ $value }}" target="_blank" rel="noopener nofollow" dir="ltr">{{ $value }}</a>@else<span style="white-space: pre-line">{{ $text }}</span>@endif</dd>
            @endforeach
        </dl>
    @endforeach
@endif
@if ($item->files->isNotEmpty())
    <h6 class="bz-answers-title">المرفقات</h6>
    <ul class="list-unstyled mb-0">
        @foreach ($item->files as $file)
            <li><a href="{{ route('business.files.request', $file->id) }}"><i class="bi bi-paperclip"></i> {{ $file->original_name }}</a> <small class="rf-muted">({{ number_format($file->size / 1048576, 1) }} م.ب)</small></li>
        @endforeach
    </ul>
@endif
