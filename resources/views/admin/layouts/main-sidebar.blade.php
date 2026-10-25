<!-- main-sidebar -->
<div class="app-sidebar__overlay" data-toggle="sidebar"></div>
<aside class="app-sidebar sidebar-scroll rf-admin-nav">
    <div class="main-sidebar-header active">
        <a class="desktop-logo logo-light active" href="{{ route('admin.dashboard') }}">لوحة تحكم نفّذها</a>
        <a class="desktop-logo logo-dark active" href="{{ route('admin.dashboard') }}"><img
                src="{{ URL::asset('assets/website/img/logo.png') }}" class="main-logo dark-theme" alt="نفّذها"></a>
        <a class="logo-icon mobile-logo icon-light active" href="{{ route('admin.dashboard') }}"><img
                src="{{ URL::asset('assets/website/img/logo.png') }}" class="logo-icon" alt="نفّذها"></a>
        <a class="logo-icon mobile-logo icon-dark active" href="{{ route('admin.dashboard') }}"><img
                src="{{ URL::asset('assets/website/img/logo.png') }}" class="logo-icon dark-theme" alt="نفّذها"></a>
    </div>
    <div class="main-sidemenu">
        <div class="rf-an-user">
            <img alt="" class="avatar avatar-md brround" src="{{ Auth::user()->avatarUrl(128) }}">
            <div>
                <strong>{{ Auth::user()->name }}</strong>
                <small>{{ \App\Support\StaffPermissions::ROLES[\App\Support\StaffPermissions::role(Auth::user()) ?? 'manager'] ?? '' }}</small>
            </div>
        </div>

        @if (Auth::user()->account_type == 'admin')
            @php($adminMenu = \App\Support\AdminMenu::build(request()))
            <div class="rf-an-search">
                <i class="fa fa-search"></i>
                <input type="search" id="rf-an-filter" placeholder="ابحث في القائمة…" autocomplete="off" aria-label="ابحث في القائمة">
            </div>

            <ul class="side-menu" data-server-active="1">
                <li class="slide">
                    <a class="side-menu__item {{ request()->is('admin/dashboard') ? 'active rf-an-current' : '' }}" href="{{ route('admin.dashboard') }}">
                        <i class="fa fa-home rf-an-icon"></i>
                        <span class="side-menu__label">الرئيسية والتقارير</span>
                    </a>
                </li>

                @foreach ($adminMenu as $group)
                    <li class="slide rf-an-group {{ $group['active'] ? 'is-expanded active' : '' }}">
                        <a class="side-menu__item {{ $group['active'] ? 'active' : '' }}" data-toggle="slide" href="#">
                            <i class="fa {{ $group['icon'] }} rf-an-icon"></i>
                            <span class="side-menu__label">{{ $group['label'] }}</span>
                            @if ($group['badge'] > 0 && ! $group['active'])
                                <span class="rf-an-badge" title="بانتظار الفريق">{{ $group['badge'] }}</span>
                            @endif
                            <i class="angle fe fe-chevron-down"></i>
                        </a>
                        <ul class="slide-menu">
                            @foreach ($group['items'] as $item)
                                <li class="{{ $item['active'] ? 'active' : '' }}">
                                    <a class="slide-item {{ $item['active'] ? 'active rf-an-current' : '' }}" href="{{ $item['href'] }}" @if ($item['active']) aria-current="page" @endif>
                                        {{ $item['label'] }}
                                        @if ($item['badge'] > 0)
                                            <span class="rf-an-badge">{{ $item['badge'] }}</span>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endforeach
            </ul>
            <p class="rf-an-empty" id="rf-an-empty" hidden>لا توجد صفحة بهذا الاسم</p>
        @endif
    </div>
</aside>
<!-- main-sidebar -->
