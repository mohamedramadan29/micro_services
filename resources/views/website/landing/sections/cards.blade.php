@include('website.landing.sections._head')
<div class="rf-lp-cards">
    @foreach ($items as $card)
        @php
            $href = \App\Support\SiteContent::url($card['link']);
            $image = \App\Support\SiteContent::media($card['image']);
        @endphp
        <div class="rf-lp-card" @if ($card['background_color']) style="background-color: {{ $card['background_color'] }}" @endif>
            <span class="rf-lp-num">{{ $loop->iteration }}</span>
            @if ($image)
                <img src="{{ $image->url() }}" alt="{{ $image->alt }}" class="rf-lp-card-img" loading="lazy">
            @elseif ($card['icon'])
                <span class="rf-lp-icon" aria-hidden="true"><i class="bi {{ $card['icon'] }}"></i></span>
            @endif
            <h3>{{ $card['title'] }}</h3>
            @if ($card['description'])<p>{{ $card['description'] }}</p>@endif
            @if ($href)
                <a href="{{ $href }}" class="rf-lp-btn">{{ $card['button_text'] ?: 'ابدأ الآن' }}</a>
            @else
                <span class="rf-lp-soon">قريبًا</span>
            @endif
        </div>
    @endforeach
</div>
@if ($f['footnote'])<p class="rf-lp-note">{{ $f['footnote'] }}</p>@endif
