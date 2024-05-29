@extends('website.layouts.master')
@section('title')
    الرسائل
@endsection
@section('content')
    <!-- ============================ Page Title Start================================== -->
    <div class="page-title bg-cover" style="background:url({{asset('assets/website/img/bn-1.jpg')}})no-repeat;" data-overlay="5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12"></div>
            </div>
        </div>
    </div>
    <!-- ============================ Page Title End ================================== -->

    <!-- ============================ Main Section Start ================================== -->
    <section class="gray-bg pt-4 text-right" dir="rtl">
        <div class="container-fluid">
            <div class="row m-0">
                <div class="col-xl-3 col-lg-4 col-md-12 col-sm-12">
                    <div class="dashboard-navbar overlio-top">

                        <div class="d-user-avater">
                            <img src="{{asset('assets/website/img/avatar.png')}}" class="img-fluid rounded" alt="">
                            <h4> Mohamed Ramadan </h4>
                            <span>mohamedramadan2930@gmail.com</span>
                        </div>

                        <div class="d-navigation">
                            <ul id="metismenu">
                                <li><a href="{{url('dashboard')}}"><i class="ti-dashboard"></i> الملف الشخصي </a>
                                </li>
                                <li><a href="{{url('service/index')}}"><i class="ti-user"></i> الخدمات </a></li>
                                <li><a href="{{url('service/add')}}"><i class="ti-plus"></i> اضف خدمة جديدة   </a></li>
                                <li><a href="{{url('user/chat')}}"><i class="ti-email"></i>  المحادثات </a></li>
                                <li><a href="{{url('user/reviews')}}"><i class="ti-email"></i> التقيمات </a></li>
                                <li><a href="{{url('user/update')}}"><i class="ti-email"></i> تعديل الملف الشخصي </a></li>
                                <li><a href="{{url('user/balance')}}"><i class="ti-email"></i> الرصيد </a></li>
                                <li><a href="{{url('logout')}}"><i class="ti-power-off"></i> تسجيل خروج </a></li>
                            </ul>
                        </div>

                    </div>
                </div>

                <!-- Item Wrap Start -->
                <div class="col-xl-9 col-lg-8 col-md-12 col-sm-12">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <!-- Breadcrumbs -->
                            <div class="bredcrumb_wrap">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="#"> الرئيسية  </a></li>
                                        <li class="breadcrumb-item active" aria-current="page"> الرسائل </li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">

                            <!-- Convershion -->
                            <div class="messages-container margin-top-0">
                                <div class="messages-container-inner">
                                    <!-- Messages -->
                                    <div class="dash-msg-inbox">
                                        <ul>
                                            <li>
                                                <a href="#">
                                                    <div class="dash-msg-avatar"><img src=" {{asset('assets/website/img/team-1.jpg')}}"
                                                                                      alt=""><span
                                                            class="_user_status online"></span></div>

                                                    <div class="message-by">
                                                        <div class="message-by-headline">
                                                            <h5>Tilly Bartlett</h5>
                                                            <span>36 min ago</span>
                                                        </div>
                                                        <p>Hello, I am a web designer with 5 year.. </p>
                                                    </div>
                                                </a>
                                            </li>

                                            <li class="active-message">
                                                <a href="#">
                                                    <div class="dash-msg-avatar"><img src="{{asset('assets/website/img/team-1.jpg')}}"
                                                                                      alt=""><span
                                                            class="_user_status offline"></span></div>

                                                    <div class="message-by">
                                                        <div class="message-by-headline">
                                                            <h5>George Howarth</h5>
                                                            <span>2 hours ago</span>
                                                        </div>
                                                        <p>Hello, I am a web designer with 5 year..</p>
                                                    </div>
                                                </a>
                                            </li>

                                            <li>
                                                <a href="#">
                                                    <div class="dash-msg-avatar"><img src="{{asset('assets/website/img/team-1.jpg')}}"
                                                                                      alt=""><span
                                                            class="_user_status busy"></span></div>

                                                    <div class="message-by">
                                                        <div class="message-by-headline">
                                                            <h5>Harriet Ball</h5>
                                                            <span>Yesterday</span>
                                                        </div>
                                                        <p>Hello, I am a web designer with 5 year..</p>
                                                    </div>
                                                </a>
                                            </li>

                                            <li>
                                                <a href="#">
                                                    <div class="dash-msg-avatar"><img src="{{asset('assets/website/img/team-1.jpg')}}"
                                                                                      alt=""><span
                                                            class="_user_status online"></span></div>

                                                    <div class="message-by">
                                                        <div class="message-by-headline">
                                                            <h5>Sienna Bruce</h5>
                                                            <span>02.01.2020</span>
                                                        </div>
                                                        <p>Hello, I am a web designer with 5 year..</p>
                                                    </div>
                                                </a>
                                            </li>

                                            <li>
                                                <a href="#">
                                                    <div class="dash-msg-avatar"><img src="{{asset('assets/website/img/team-1.jpg')}}"
                                                                                      alt=""><span
                                                            class="_user_status busy"></span></div>

                                                    <div class="message-by">
                                                        <div class="message-by-headline">
                                                            <h5>Leo Stewart</h5>
                                                            <span>03.01.2020</span>
                                                        </div>
                                                        <p>Hello, I am a web designer with 5 year..</p>
                                                    </div>
                                                </a>
                                            </li>

                                            <li>
                                                <a href="#">
                                                    <div class="dash-msg-avatar"><img src="{{asset('assets/website/img/team-1.jpg')}}"
                                                                                      alt=""><span
                                                            class="_user_status online"></span></div>

                                                    <div class="message-by">
                                                        <div class="message-by-headline">
                                                            <h5>Shaurya Preet</h5>
                                                            <span>05.01.2020</span>
                                                        </div>
                                                        <p>Hello, I am a web designer with 5 year..</p>
                                                    </div>
                                                </a>
                                            </li>

                                            <li>
                                                <a href="#">
                                                    <div class="dash-msg-avatar"><img src="{{asset('assets/website/img/team-1.jpg')}}"
                                                                                      alt=""><span
                                                            class="_user_status offline"></span></div>

                                                    <div class="message-by">
                                                        <div class="message-by-headline">
                                                            <h5>Dan Preet</h5>
                                                            <span>04.01.2020</span>
                                                        </div>
                                                        <p>Hello, I am a web designer with 5 year..</p>
                                                    </div>
                                                </a>
                                            </li>

                                            <li>
                                                <a href="#">
                                                    <div class="dash-msg-avatar"><img src="{{asset('assets/website/img/team-1.jpg')}}"
                                                                                      alt=""><span
                                                            class="_user_status online"></span></div>

                                                    <div class="message-by">
                                                        <div class="message-by-headline">
                                                            <h5>Maddison</h5>
                                                            <span>31.05.2019</span>
                                                        </div>
                                                        <p>Hello, I am a web designer with 5 year..</p>
                                                    </div>
                                                </a>
                                            </li>

                                            <li>
                                                <a href="#">
                                                    <div class="dash-msg-avatar"><img src="{{asset('assets/website/img/team-1.jpg')}}"
                                                                                      alt=""><span
                                                            class="_user_status busy"></span></div>

                                                    <div class="message-by">
                                                        <div class="message-by-headline">
                                                            <h5>Maddison</h5>
                                                            <span>27.05.2019</span>
                                                        </div>
                                                        <p>Hello, I am a web designer with 5 year..</p>
                                                    </div>
                                                </a>
                                            </li>

                                            <li>
                                                <a href="#">
                                                    <div class="dash-msg-avatar"><img src="{{asset('assets/website/img/team-1.jpg')}}"
                                                                                      alt=""><span
                                                            class="_user_status busy"></span></div>

                                                    <div class="message-by">
                                                        <div class="message-by-headline">
                                                            <h5>Eleanor Lloyd</h5>
                                                            <span>24.05.2019</span>
                                                        </div>
                                                        <p>Hello, I am a web designer with 5 year..</p>
                                                    </div>
                                                </a>
                                            </li>

                                            <li>
                                                <a href="#">
                                                    <div class="dash-msg-avatar"><img src="{{asset('assets/website/img/team-1.jpg')}}"
                                                                                      alt=""><span
                                                            class="_user_status offline"></span></div>

                                                    <div class="message-by">
                                                        <div class="message-by-headline">
                                                            <h5>Anna Curtis</h5>
                                                            <span>05.01.2020</span>
                                                        </div>
                                                        <p>Hello, I am a web designer with 5 year..</p>
                                                    </div>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <!-- Messages / End -->

                                    <!-- Message Content -->
                                    <div class="dash-msg-content">

                                        <div class="message-plunch">
                                            <div class="dash-msg-avatar"><img src=" {{asset('assets/website/img/team-3.jpg')}}" alt=""></div>
                                            <div class="dash-msg-text"><p>Hello, Contrary to popular belief, Lorem Ipsum
                                                    is not simply random text. It has roots in a piece of classical
                                                    Latin</p></div>
                                        </div>

                                        <div class="message-plunch me">
                                            <div class="dash-msg-avatar"><img src="{{asset('assets/website/img/team-5.jpg')}}" alt=""></div>
                                            <div class="dash-msg-text"><p>looked up one of the more obscure Latin words,
                                                    consectetur, from a Lorem</p></div>
                                        </div>

                                        <div class="message-plunch">
                                            <div class="dash-msg-avatar"><img src="{{asset('assets/website/img/team-3.jpg')}}" alt=""></div>
                                            <div class="dash-msg-text"><p>If you are going to use a passage of Lorem
                                                    Ipsum, you need to be sure there isn't anything embarrassing</p>
                                            </div>
                                        </div>

                                        <div class="message-plunch me">
                                            <div class="dash-msg-avatar"><img src="{{asset('assets/website/img/team-5.jpg')}}" alt=""></div>
                                            <div class="dash-msg-text"><p>please consider donating a small sum to help
                                                    pay for the hosting and bandwidth bill.</p></div>
                                        </div>

                                        <div class="message-plunch">
                                            <div class="dash-msg-avatar"><img src="{{asset('assets/website/img/team-3.jpg')}}" alt=""></div>
                                            <div class="dash-msg-text"><p>Duis aute irure dolor in reprehenderit in
                                                    voluptate velit esse cillum dolore</p></div>
                                        </div>

                                        <div class="message-plunch me">
                                            <div class="dash-msg-avatar"><img src="{{asset('assets/website/img/team-5.jpg')}}" alt=""></div>
                                            <div class="dash-msg-text"><p>numquam eius modi tempora incidunt ut labore
                                                    et dolore magnam aliquam quaerat voluptatem.</p></div>
                                        </div>

                                        <div class="message-plunch">
                                            <div class="dash-msg-avatar"><img src="{{asset('assets/website/img/team-3.jpg')}}" alt=""></div>
                                            <div class="dash-msg-text"><p>But I must explain to you how all this
                                                    mistaken idea of denouncing pleasure</p></div>
                                        </div>

                                        <!-- Reply Area -->
                                        <div class="clearfix"></div>
                                        <div class="message-reply">
                                            <textarea cols="40" rows="3" class="form-control with-light"
                                                      placeholder="رسالتك"></textarea>
                                            <button class="btn dark-2"> ارسال  </button>
                                        </div>

                                    </div>
                                    <!-- Message Content -->

                                </div>

                            </div>

                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>
    <!-- ============================ Main Section End ================================== -->
@endsection
