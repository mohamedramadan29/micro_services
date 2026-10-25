{{-- A row of counts read like a dimension line: value + label, ticks between them. Items: [value, label, url|null]. --}}
<div class="nf-acc-line nf-sheet" role="list">
    @foreach ($items as [$value, $label, $url])
        <{{ $url ? 'a' : 'div' }} class="nf-acc-line-item {{ (string) $value === '0' ? 'zero' : '' }}" role="listitem" @if ($url) href="{{ $url }}" @endif>
            <strong>{{ $value }}</strong>
            <span>{{ $label }}</span>
        </{{ $url ? 'a' : 'div' }}>
    @endforeach
</div>
