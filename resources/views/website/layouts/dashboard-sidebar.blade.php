{{-- Account menu: built per account type by App\Support\AccountMenu (same list as the navbar dropdown). --}}
<button type="button" class="rf-acc-menu-btn" aria-expanded="false" aria-controls="rf-acc-nav"
        onclick="var n=document.getElementById('rf-acc-nav');var o=n.classList.toggle('open');this.setAttribute('aria-expanded',o)">
    <i class="bi bi-list"></i> قائمة حسابي <i class="bi bi-chevron-down rf-acc-chev"></i>
</button>
<div class="d-navigation rf-acc-nav" id="rf-acc-nav">
    <ul id="metismenu">
        @foreach (\App\Support\AccountMenu::sections(Auth::user(), request()) as $section)
            @if ($section['title'])
                <li class="rf-acc-section" aria-hidden="true">{{ $section['title'] }}</li>
            @endif
            @foreach ($section['items'] as $item)
                <li class="{{ $item['active'] ? 'active' : '' }}">
                    <a href="{{ $item['href'] }}" @if ($item['active']) aria-current="page" @endif>
                        <i class="{{ $item['icon'] }}"></i> {{ $item['label'] }}
                        @if ($item['badge'] > 0)
                            <span class="rf-acc-badge">{{ $item['badge'] }}</span>
                        @endif
                    </a>
                </li>
            @endforeach
        @endforeach
        <li class="rf-acc-section" aria-hidden="true"></li>
        <li>
            <a href="{{ url('logout') }}" onclick="event.preventDefault(); document.getElementById('site-logout-form').submit();"><i class="ti-power-off"></i> تسجيل خروج</a>
        </li>
    </ul>
</div>
