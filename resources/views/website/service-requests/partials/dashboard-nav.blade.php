{{-- User dashboard side column: avatar + sidebar links (same as other dashboard pages). --}}
<div class="dashboard-navbar">
    <div class="d-user-avater">
        @if (Auth::user()->image != '')
            <img src="{{ optional(Auth::user())->avatarUrl(128) }}" class="img-fluid rounded" alt="">
        @else
            <img src="{{ asset('assets/website/img/avatar.png') }}" class="img-fluid rounded" alt="">
        @endif
        <h4> {{ Auth::user()->user_name }} </h4>
        <span> {{ Auth::user()->email }} </span>
    </div>
    @include('website.layouts.dashboard-sidebar')
</div>
