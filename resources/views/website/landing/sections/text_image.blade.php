@php $image = \App\Support\SiteContent::media($f['image']); @endphp
<div class="rf-lp-split {{ $f['image_side'] === 'start' ? 'image-start' : '' }} {{ $image ? '' : 'no-image' }}">
    <div>
        @if ($f['badge'])<span class="rf-lp-badge">{{ $f['badge'] }}</span>@endif
        <h2>{{ $f['title'] }}</h2>
        @if ($f['body'])<p class="rf-lp-body">{!! nl2br(e($f['body'])) !!}</p>@endif
        @include('website.landing.sections._buttons')
    </div>
    @if ($image)<img src="{{ $image->url() }}" alt="{{ $image->alt }}" loading="lazy">@endif
</div>
