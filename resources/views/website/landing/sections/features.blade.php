@include('website.landing.sections._head')
<div class="rf-lp-features">
    @foreach ($items as $feature)
        <div class="rf-lp-feature">
            @if ($feature['icon'])<span class="rf-lp-icon" aria-hidden="true"><i class="bi {{ $feature['icon'] }}"></i></span>@endif
            <h3>{{ $feature['title'] }}</h3>
            @if ($feature['description'])<p>{{ $feature['description'] }}</p>@endif
        </div>
    @endforeach
</div>
