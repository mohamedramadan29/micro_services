<!-- Start Navigation -->
<div class="header header-transparent change-logo">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <nav id="navigation" class="navigation navigation-landscape">
                    <div class="nav-header">
                        <a class="nav-brand static-logo" href="{{url('/')}}"><img
                                src="{{asset('assets/website/img/logo.png')}}" class="logo" alt=""/></a>
                        <a class="nav-brand fixed-logo" href="{{url('/')}}"><img
                                src="{{asset('assets/website/img/logo.png')}}" class="logo" alt=""/></a>
                        <div class="nav-toggle"></div>
                    </div>
                    @if(! Auth::check())
                    <div class="mobile_account">
                        <ul class="nav-menu nav-menu-social">
                            <li class="add-listing dark-bg">
                                <a href="{{url('login')}}">
                                    <i class=" fa fa-sign-in   mr-1"></i>   {{ __('public.login_mobile') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                    @endif
                    <div class="nav-menus-wrapper">
                        @if(\Illuminate\Support\Facades\Auth::user())
                            <ul class="nav-menu nav-menu-social">
                                <!----------------- Message Alerts --------------->

                                <div class="dropdown notificaion-alerts">
                                    <button class="dropdown-toggle" type="button" id="dropdownMenuButton1"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                        @if (Auth::user()->unreadNotifications->where('type', 'App\Notifications\NewMessage')->count() > 0)
                                            <span class="counter"> {{ Auth::user()->unreadNotifications->count() }}
                                                </span>
                                        @endif
                                            <li><a href=""> <i style="font-size: 20px" class="bi bi-envelope-fill"></i> </a></li>
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
                                                    <a class="dropdown-item" href="{{ url('chat-main') }}">
                                                        {{ $notification['data']['title'] }}
                                                        {{ $notification['data']['sender_username'] }}
                                                        <br>
                                                        <span class="timer"><i class="fa fa-clock"></i>
                                                                {{ $notification->created_at->diffForHumans() }}</span>
                                                    </a>
                                                </li>
                                                <hr>
                                            @empty
                                                <li><a class="dropdown-item"> لا يوجد لديك رسائل في الوقت
                                                        الحالي </a></li>
                                                <hr>
                                            @endforelse
                                        @else
                                            <li><a class="dropdown-item"> لا يوجد لديك رسائل في الوقت الحالي </a>
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
                                        <li><a href="#"> <i style="font-size: 20px" class="bi bi-bell-fill"></i> </a>
                                        </li>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                        @forelse ($unreadNotificationsUsers as $notification)
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
                                                <li><a class="dropdown-item"
                                                       href="{{ url('orders') }}">
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

                                <li><a href="{{url('dashboard')}}"> {{ __('public.account') }} </a></li>
                            </ul>
                        @else
                            <ul class="nav-menu nav-menu-social logins_button">
                                <li>
                                    <a href="{{url('register')}}">
                                        <i class="ti-user mr-1"></i>  {{ __('public.register') }}
                                    </a>
                                </li>
                                <li class="add-listing dark-bg">
                                    <a href="{{url('login')}}">
                                        <i class=" fa fa-sign-in   mr-1"></i>  {{ __('public.login') }}
                                    </a>
                                </li>
                            </ul>
                        @endif

                        <ul class="nav-menu">
                            <li><a href="{{url('/')}}"> {{ __('public.home') }} </a></li>
                            <li><a href="{{url('projects')}}"> {{ __('public.projects') }}  </a></li>
                            <li><a href="{{url('courses')}}"> {{ __('public.courses') }}   </a></li>
                            <li><a href="{{url('products')}}">  {{ __('public.products') }} </a></li>
                            <li><a href="{{url('categories')}}"> {{ __("public.categories") }} </a></li>
                            <li><a href="{{url('services')}}">  {{ __('public.services') }} </a></li>

                            @if(\Illuminate\Support\Facades\Auth::check())
                                <li><a href="{{url('purches')}}"> {{ __('public.purches') }} </a></li>
                                <li><a href="{{url('orders')}}"> {{ __('public.incomming_request') }} </a></li>
                            @endif
                            @php
                                $count_items = count(\App\Models\front\Cart::getCartItems());
                            @endphp
{{--                            <li class="cart_navbar_icon"><a href="{{url('cart')}}"> <i style="font-size: 20px"--}}
{{--                                                                                       class="bi bi-cart3"></i>--}}
{{--                                    @if($count_items > 0)--}}
{{--                                        <span class="counter_num"> {{$count_items}} </span>--}}
{{--                                    @endif--}}
{{--                                </a>--}}
{{--                            </li>--}}
                        </ul>

                    </div>
                </nav>
            </div>
        </div>
    </div>
</div>
<!-- End Navigation -->
<div class="clearfix"></div>
