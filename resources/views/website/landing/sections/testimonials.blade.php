@include('website.landing.sections._head')
<div class="rf-lp-quotes">
    @foreach ($items as $quote)
        @php $image = \App\Support\SiteContent::media($quote['image']); @endphp
        <figure class="rf-lp-quote">
            <blockquote>{{ $quote['quote'] }}</blockquote>
            <figcaption>
                @if ($image)<img src="{{ $image->url() }}" alt="" width="44" height="44" loading="lazy">@endif
                <span><strong>{{ $quote['name'] }}</strong>@if ($quote['role'])<small>{{ $quote['role'] }}</small>@endif</span>
            </figcaption>
        </figure>
    @endforeach
</div>
