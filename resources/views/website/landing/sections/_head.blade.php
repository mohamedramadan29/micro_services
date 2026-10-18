{{-- Badge, title and description shared by most section types. --}}
@if (! empty($f['badge']) || ! empty($f['title']) || ! empty($f['subtitle']))
    <div class="rf-lp-head">
        @if (! empty($f['badge']))<span class="rf-lp-badge">{{ $f['badge'] }}</span>@endif
        @if (! empty($f['title']))<h2>{{ $f['title'] }}</h2>@endif
        @if (! empty($f['subtitle']))<p>{{ $f['subtitle'] }}</p>@endif
    </div>
@endif
