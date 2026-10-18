@php $u = fn ($link) => \App\Support\SiteContent::url($link); @endphp
@if (($f['button_text'] && $f['button_link']) || (($f['button2_text'] ?? '') && ($f['button2_link'] ?? '')))
    <div class="rf-lp-buttons">
        @if ($f['button_text'] && $f['button_link'])<a href="{{ $u($f['button_link']) }}" class="rf-lp-btn">{{ $f['button_text'] }}</a>@endif
        @if (($f['button2_text'] ?? '') && ($f['button2_link'] ?? ''))<a href="{{ $u($f['button2_link']) }}" class="rf-lp-btn rf-lp-btn-outline">{{ $f['button2_text'] }}</a>@endif
    </div>
@endif
