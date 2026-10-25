<!-- Start Navigation -->
<div class="header header-transparent change-logo">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <nav id="navigation" class="navigation navigation-landscape">
                    <div class="nav-header">
                        <a class="nav-brand static-logo" href="{{ url('/') }}" style="padding: 0 15px">
                            {{-- <video style="width: 120px; height: auto;" src="{{ asset('assets/uploads/logo.mp4') }}"
                                autoplay loop muted playsinline preload="auto">
                                <!-- fallback لو المتصفح ما يدعم الفيديو -->

                            </video> --}}
                            <img src="{{ asset('assets/website/img/logo.png') }}" class="logo" alt="نفّذها — الرئيسية" />
                        </a>
                        <a class="nav-brand fixed-logo" href="{{ url('/') }}" style="padding: 0 15px">
                            <img src="{{ asset('assets/website/img/logo.png') }}" class="logo" alt="نفّذها — الرئيسية" />

                            {{-- <video style="width: 120px; height: auto;" src="{{ asset('assets/uploads/logo.mp4') }}"
                                autoplay loop muted playsinline preload="auto">


                            </video> --}}
                        </a>
                        <button type="button" class="nav-toggle" aria-label="فتح قائمة الموقع" aria-expanded="false" aria-controls="site-navigation"></button>
                    </div>
                    @if (!Auth::check())
                        <div class="mobile_account">
                            <ul class="nav-menu nav-menu-social">
                                <li class="add-listing dark-bg">
                                    <a href="{{ url('login') }}">
                                        <i class=" fa fa-sign-in   mr-1"></i> {{ __('public.login_mobile') }}
                                    </a>
                                </li>
                            </ul>
                        </div>
                    @else
                        <div class="mobile_alerts">
                            <ul class="nav-menu nav-menu-social">
                                <!----------------- Message Alerts --------------->
                                <div class="dropdown notificaion-alerts">
                                    <button class="dropdown-toggle" type="button" id="dropdownMenuButton1"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        @if (Auth::user()->unreadNotifications->where('type', 'App\Notifications\NewMessage')->count() > 0)
                                            <span class="counter"> {{ Auth::user()->unreadNotifications->where('type', 'App\Notifications\NewMessage')->count() }}
                                            </span>
                                        @endif
                                        <li><a href=""> <i style="font-size: 20px"
                                                    class="bi bi-envelope-fill"></i> </a>
                                        </li>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                        @php
                                            $newMessageNotifications = Auth::user()->unreadNotifications->where(
                                                'type',
                                                'App\Notifications\NewMessage',
                                            );
                                        @endphp

                                        @if ($newMessageNotifications->count() > 0)
                                            @forelse ($newMessageNotifications as $notification)
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ url('chat-main/' . $notification['data']['conversation_id']) }}">
                                                        {{ $notification['data']['title'] }}
                                                        {{ $notification['data']['sender_username'] }}
                                                        <br>
                                                        <span class="timer"><i class="fa fa-clock"></i>
                                                            {{ $notification->created_at->diffForHumans() }}</span>
                                                    </a>
                                                </li>
                                                <hr>
                                            @empty
                                                <li><a class="dropdown-item" href="{{ url('chats') }}"> عرض جميع
                                                        المحادثات </a></li>
                                                <hr>
                                            @endforelse
                                        @else
                                            <li><a class="dropdown-item" href="{{ url('chats') }}"> عرض جميع المحادثات
                                                </a>
                                            </li>
                                            <hr>
                                        @endif
                                    </ul>
                                </div>
                                <!------------------------ Notification Alerts For Users  --------------->
                                <div class="dropdown notificaion-alerts">
                                    <button class="dropdown-toggle" type="button" id="dropdownMenuButton1"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        @php
                                            $unreadNotificationsUsers = \Illuminate\Support\Facades\Auth::user()->unreadNotifications->filter(
                                                function ($notification) {
                                                    return $notification['type'] !== 'App\Notifications\NewMessage';
                                                },
                                            );
                                        @endphp
                                        @if ($unreadNotificationsUsers->count() > 0)
                                            <span class="counter"> {{ $unreadNotificationsUsers->count() }}
                                            </span>
                                        @endif
                                        <li><a href="#"> <i style="font-size: 20px" class="bi bi-bell-fill"></i>
                                            </a>
                                        </li>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                        @forelse ($unreadNotificationsUsers as $notification)
                                            @if (isset($notification['data']['url']))
                                                @include('website.layouts.partials.notification-item', ['notification' => $notification])
                                                @continue
                                            @endif
                                            @if ($notification['type'] == 'App\Notifications\AcceptJobFromAdmin')
                                                <li><a class="dropdown-item"
                                                        href="{{ url('service/' . $notification['data']['serv_id'] . '-' . $notification['data']['serv_slug']) }}">
                                                        {{ $notification['data']['noti_title'] }}
                                                        : {{ $notification['data']['serv_name'] }}
                                                        <br>
                                                        <span class="timer"> <i class="fa fa-clock"></i>
                                                            {{ $notification->created_at->diffForHumans() }}
                                                        </span>
                                                    </a>

                                                </li>
                                                <hr>
                                            @elseif($notification['type'] == 'App\Notifications\NewOrderNotification')
                                                <li><a class="dropdown-item" href="{{ url('orders') }}">
                                                        {{ $notification['data']['buyer_name'] }}
                                                        {{ $notification['data']['noti_title'] }}
                                                        : {{ $notification['data']['serv_name'] }}
                                                        <br>
                                                        <span class="timer"> <i class="fa fa-clock"></i>
                                                            {{ $notification->created_at->diffForHumans() }}
                                                        </span>
                                                    </a>

                                                </li>
                                                <hr>
                                            @endif
                                        @empty
                                            <li><a class="dropdown-item"> لا يوجد لديك اشعارات في الوقت الحالي </a>
                                            </li>
                                            <hr>
                                        @endforelse
                                    </ul>
                                </div>
                                <div class="dropdown profile-dropdown">
                                    <button class="btn btn-secondary dropdown-toggle" type="button"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        @if (Auth::user()->image != '')
                                            <img src="{{ optional(Auth::user())->avatarUrl(128) }}"
                                                class="img-fluid rounded" alt="">
                                        @else
                                            <img src="{{ asset('assets/website/img/avatar.png') }}"
                                                class="img-fluid rounded" alt="">
                                        @endif
                                    </button>
                                    <ul class="dropdown-menu rf-acc-dropdown">
                                        <li class="rf-acc-dd-head">{{ Auth::user()->name }} <small>{{ Auth::user()->typeLabel() }}</small></li>
                                        @foreach (\App\Support\AccountMenu::sections(Auth::user(), request()) as $section)
                                            @if (! $loop->first)<li><hr class="dropdown-divider"></li>@endif
                                            @foreach ($section['items'] as $item)
                                                <li><a class="dropdown-item {{ $item['active'] ? 'active' : '' }}" href="{{ $item['href'] }}">
                                                    {{ $item['label'] }} <i class="{{ $item['icon'] }}"></i>
                                                    @if ($item['badge'] > 0)<span class="rf-acc-badge">{{ $item['badge'] }}</span>@endif
                                                </a></li>
                                            @endforeach
                                        @endforeach
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="{{ url('logout') }}" onclick="event.preventDefault(); document.getElementById('site-logout-form').submit();"> تسجيل خروج <i
                                                    class="ti-power-off"></i> </a></li>
                                    </ul>
                                </div>
                                {{-- <li class="mobile_account">
                                <a href="{{ url('dashboard') }}"> {{ __('public.account') }} </a>
                            </li> --}}
                            </ul>
                        </div>
                    @endif
                    <div id="site-navigation" class="nav-menus-wrapper d-flex">
                        @if (\Illuminate\Support\Facades\Auth::user())
                            <ul class="nav-menu nav-menu-social large_desktop_notification">
                                <!----------------- Message Alerts --------------->
                                <div class="dropdown notificaion-alerts">
                                    <button class="dropdown-toggle" type="button" id="dropdownMenuButton1"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        @if (Auth::user()->unreadNotifications->where('type', 'App\Notifications\NewMessage')->count() > 0)
                                            <span class="counter"> {{ Auth::user()->unreadNotifications->where('type', 'App\Notifications\NewMessage')->count() }}
                                            </span>
                                        @endif
                                        <li><a href=""> <i style="font-size: 20px"
                                                    class="bi bi-envelope-fill"></i> </a>
                                        </li>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                        @php
                                            $newMessageNotifications = Auth::user()->unreadNotifications->where(
                                                'type',
                                                'App\Notifications\NewMessage',
                                            );
                                        @endphp

                                        @if ($newMessageNotifications->count() > 0)
                                            @forelse ($newMessageNotifications as $notification)
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ url('chat-main/' . $notification['data']['conversation_id']) }}">
                                                        {{ $notification['data']['title'] }}
                                                        {{ $notification['data']['sender_username'] }}
                                                        <br>
                                                        <span class="timer"><i class="fa fa-clock"></i>
                                                            {{ $notification->created_at->diffForHumans() }}</span>
                                                    </a>
                                                </li>
                                                <hr>
                                            @empty
                                                <li><a class="dropdown-item" href="{{ url('chats') }}"> عرض جميع
                                                        المحادثات </a></li>
                                                <hr>
                                            @endforelse
                                        @else
                                            <li><a class="dropdown-item" href="{{ url('chats') }}"> عرض جميع
                                                    المحادثات </a>
                                            </li>
                                            <hr>
                                        @endif
                                    </ul>
                                </div>
                                <!------------------------ Notification Alerts For Users  --------------->
                                <div class="dropdown notificaion-alerts">
                                    <button class="dropdown-toggle" type="button" id="dropdownMenuButton1"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        @php
                                            $unreadNotificationsUsers = \Illuminate\Support\Facades\Auth::user()->unreadNotifications->filter(
                                                function ($notification) {
                                                    return $notification['type'] !== 'App\Notifications\NewMessage';
                                                },
                                            );
                                        @endphp
                                        @if ($unreadNotificationsUsers->count() > 0)
                                            <span class="counter"> {{ $unreadNotificationsUsers->count() }}
                                            </span>
                                        @endif
                                        <li><a href="#"> <i style="font-size: 20px"
                                                    class="bi bi-bell-fill"></i>
                                            </a>
                                        </li>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                        @forelse ($unreadNotificationsUsers as $notification)
                                            @if (isset($notification['data']['url']))
                                                @include('website.layouts.partials.notification-item', ['notification' => $notification])
                                                @continue
                                            @endif
                                            @if ($notification['type'] == 'App\Notifications\AcceptJobFromAdmin')
                                                <li><a class="dropdown-item"
                                                        href="{{ url('service/' . $notification['data']['serv_id'] . '-' . $notification['data']['serv_slug']) }}">
                                                        {{ $notification['data']['noti_title'] }}
                                                        : {{ $notification['data']['serv_name'] }}
                                                        <br>
                                                        <span class="timer"> <i class="fa fa-clock"></i>
                                                            {{ $notification->created_at->diffForHumans() }}
                                                        </span>
                                                    </a>

                                                </li>
                                                <hr>
                                            @elseif($notification['type'] == 'App\Notifications\NewOrderNotification')
                                                <li><a class="dropdown-item" href="{{ url('orders') }}">
                                                        {{ $notification['data']['buyer_name'] }}
                                                        {{ $notification['data']['noti_title'] }}
                                                        : {{ $notification['data']['serv_name'] }}
                                                        <br>
                                                        <span class="timer"> <i class="fa fa-clock"></i>
                                                            {{ $notification->created_at->diffForHumans() }}
                                                        </span>
                                                    </a>
                                                </li>
                                                <hr>
                                            @elseif($notification['type'] == 'App\Notifications\AdminActiveCourse')
                                                <li><a class="dropdown-item"
                                                        href="{{ url('course/' . $notification['data']['course_id'] . '-' . $notification['data']['course_slug']) }}">
                                                        {{ $notification['data']['noti_title'] }}
                                                        : {{ $notification['data']['course_title'] }}
                                                        <br>
                                                        <span class="timer"> <i class="fa fa-clock"></i>
                                                            {{ $notification->created_at->diffForHumans() }}
                                                        </span>
                                                    </a>

                                                </li>
                                                <hr>
                                            @elseif($notification['type'] == 'App\Notifications\NewCourseRegister')
                                                <li><a class="dropdown-item"
                                                        href="{{ url('course/' . $notification['data']['course_id'] . '-' . $notification['data']['course_slug']) }}">
                                                        {{ $notification['data']['noti_title'] }}
                                                        : {{ $notification['data']['course_title'] }}
                                                        <br>
                                                        <span class="timer"> <i class="fa fa-clock"></i>
                                                            {{ $notification->created_at->diffForHumans() }}
                                                        </span>
                                                    </a>

                                                </li>
                                                <hr>
                                            @endif
                                        @empty
                                            <li><a class="dropdown-item"> لا يوجد لديك اشعارات في الوقت الحالي </a>
                                            </li>
                                            <hr>
                                        @endforelse
                                    </ul>
                                </div>
                                <li>
                                    <a href="{{ url('dashboard') }}"> حسابي </a>
                                </li>
                            </ul>
                        @else
                            <ul class="nav-menu nav-menu-social logins_button">
                                <li>
                                    <a href="{{ url('register') }}">
                                        <i class="ti-user mr-1"></i> {{ __('public.register') }}
                                    </a>
                                </li>
                                <li class="add-listing dark-bg">
                                    <a href="{{ url('login') }}">
                                        <i class=" fa fa-sign-in   mr-1"></i> {{ __('public.login') }}
                                    </a>
                                </li>
                            </ul>
                        @endif

                        <ul class="nav-menu">
                            {{-- Links managed in admin «إدارة محتوى الواجهة» → Navbar. --}}
                            @foreach (\App\Support\SiteContent::items('navbar.links') as $navLink)
                                @if (\App\Support\SiteContent::shownTo($navLink['audience']))
                                    <li class="{{ \App\Support\SiteContent::isActive($navLink['link']) ? 'active' : '' }}">
                                        <a href="{{ \App\Support\SiteContent::url($navLink['link']) }}"> {{ $navLink['label'] }} </a>
                                    </li>
                                @endif
                            @endforeach
                            @if (\App\Support\Money::usdAvailable())
                                {{-- Approximate dollar prices; payment always in EGP. --}}
                                @php $fxUsd = \App\Support\Money::wantsUsd(); @endphp
                                <li class="rf-fx-switch">
                                    <span class="rf-fx-seg" role="group" aria-label="عملة عرض الأسعار" title="الدفع دائمًا بالجنيه المصري؛ الدولار للعرض التقريبي فقط">
                                        <a href="{{ route('currency.switch', 'EGP') }}" class="{{ $fxUsd ? '' : 'active' }}" @unless ($fxUsd) aria-current="true" @endunless>ج.م</a>
                                        <a href="{{ route('currency.switch', 'USD') }}" class="{{ $fxUsd ? 'active' : '' }}" @if ($fxUsd) aria-current="true" @endif>$</a>
                                    </span>
                                </li>
                            @endif
                            @php
                                $count_items = count(\App\Models\front\Cart::getCartItems());
                            @endphp

                        </ul>

                    </div>
                </nav>
            </div>
        </div>
    </div>
</div>
<!-- End Navigation -->
<div class="clearfix"></div>
