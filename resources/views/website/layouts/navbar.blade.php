<!-- Start Navigation -->
<div class="header header-transparent change-logo">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <nav id="navigation" class="navigation navigation-landscape">
                    <div class="nav-header">
                        <a class="nav-brand static-logo" href="{{url('/')}}"><img
                                src="{{asset('assets/website/img/khamsat.png')}}" class="logo" alt=""/></a>
                        <a class="nav-brand fixed-logo" href="{{url('/')}}"><img
                                src="{{asset('assets/website/img/khamsat.png')}}" class="logo" alt=""/></a>
                        <div class="nav-toggle"></div>
                    </div>
                    <div class="nav-menus-wrapper">
                        @if(\Illuminate\Support\Facades\Auth::user())
                            <ul class="nav-menu nav-menu-social">
                            <li><a href="{{url('service/add')}}"> اضف خدمة </a></li>
                                <li><a href="{{url('dashboard')}}"> حسابي </a></li>
                            </ul>
                        @else
                            <ul class="nav-menu nav-menu-social">
                                <li>
                                    <a href="{{url('register')}}">
                                        <i class="ti-user mr-1"></i> حساب جديد
                                    </a>
                                </li>
                                <li class="add-listing dark-bg">
                                    <a href="{{url('login')}}">
                                        <i class=" fa fa-sign-in   mr-1"></i> تسجيل دخول
                                    </a>
                                </li>
                            </ul>
                        @endif

                        <ul class="nav-menu">
                            <li><a href="{{url('/')}}"> الرئيسية </a></li>

                            <li><a href="{{url('categories')}}"> الاقسام </a></li>
                            <li><a href="{{url('services')}}"> الخدمات </a></li>

                        </ul>

                    </div>
                </nav>
            </div>
        </div>
    </div>
</div>
<!-- End Navigation -->
<div class="clearfix"></div>
