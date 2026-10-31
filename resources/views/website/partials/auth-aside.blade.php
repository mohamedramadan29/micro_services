{{-- Side of the sign-in / sign-up sheets: the four promises from the homepage («ليه نفّذها», content area home.trust_items). --}}
@php
    $trust = \App\Support\SiteContent::first('home.trust');
    $trustItems = \App\Support\SiteContent::items('home.trust_items');
@endphp
@if ($trustItems->isNotEmpty())
    <aside class="nf-auth-aside" aria-label="{{ $trust['title'] }}">
        <h2>{{ $trust['title'] }}</h2>
        <ol>
            @foreach ($trustItems as $item)
                <li>
                    @include('website.partials.nf-icon', ['name' => $item['icon']])
                    <div><strong>{{ $item['title'] }}</strong><span>{{ $item['text'] }}</span></div>
                </li>
            @endforeach
        </ol>
    </aside>
@endif
