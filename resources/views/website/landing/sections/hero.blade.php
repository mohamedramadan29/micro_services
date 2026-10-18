@php $image = \App\Support\SiteContent::media($f['image']); @endphp
<div class="rf-lp-hero-grid {{ $image ? 'has-image' : '' }}">
    <div>
        @if ($f['badge'])<span class="rf-lp-badge">{{ $f['badge'] }}</span>@endif
        <h1>{{ $f['title'] }}</h1>
        @if ($f['subtitle'])<p class="rf-lp-lead">{{ $f['subtitle'] }}</p>@endif
        @include('website.landing.sections._buttons')
    </div>
    @if ($image)<img src="{{ $image->url() }}" alt="{{ $image->alt }}" class="rf-lp-hero-img">@endif
</div>
