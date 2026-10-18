@include('website.landing.sections._head')
<ol class="rf-lp-steps">
    @foreach ($items as $step)
        <li>
            <span class="rf-lp-step-num">@if ($step['icon'])<i class="bi {{ $step['icon'] }}" aria-hidden="true"></i>@else{{ $loop->iteration }}@endif</span>
            <h3>{{ $step['title'] }}</h3>
            @if ($step['description'])<p>{{ $step['description'] }}</p>@endif
        </li>
    @endforeach
</ol>
