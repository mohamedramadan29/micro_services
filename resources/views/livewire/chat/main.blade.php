<div>
    @section('title')
        المحادثات
    @endsection
    @section('content')
        <!-- ============================ Main Section Start ================================== -->
        <section class="gray-bg pt-4 text-right profile_page" dir="rtl">
            <div class="container-fluid">
                <div class="row m-0">
                    <div class="col-xl-3 col-lg-4 col-md-12 col-sm-12">
                        <div class="dashboard-navbar">

                            <div class="d-user-avater">
                                @if (Auth::user()->image != '')
                                    <img src="{{ asset('assets/uploads/users_image/' . Auth::user()->image) }}"
                                        class="img-fluid rounded" alt="">
                                @else
                                    <img src="{{ asset('assets/website/img/avatar.png') }}" class="img-fluid rounded"
                                        alt="">
                                @endif
                                <h4> {{ Auth::user()->user_name }} </h4>
                                <span> {{ Auth::user()->email }} </span>
                            </div>

                            <div class="d-navigation">
                                <ul id="metismenu">
                                    <li><a href="{{ url('dashboard') }}"><i class="ti-dashboard"></i> الملف الشخصي </a>
                                    </li>
                                    <li><a href="{{ url('service/index') }}"><i class="ti-user"></i> الخدمات </a></li>
                                    <li><a href="{{ url('service/add') }}"><i class="ti-plus"></i> اضف خدمة جديدة </a></li>
                                    <li><a href="{{ url('chat-main') }}"><i class="ti-email"></i> المحادثات </a></li>
                                    <li><a href="{{ url('reviews') }}"><i class="ti-email"></i> التقيمات </a></li>
                                    <li><a href="{{ url('update-account') }}"><i class="ti-email"></i> تعديل الملف الشخصي
                                        </a></li>
                                    <li><a href="{{ url('balance') }}"><i class="ti-email"></i> الرصيد </a></li>
                                    <li><a href="{{ url('logout') }}"><i class="ti-power-off"></i> تسجيل خروج </a></li>
                                </ul>
                            </div>

                        </div>
                    </div>

                    <!-- Item Wrap Start -->
                    <!-- Item Wrap Start -->
                    <div class="col-xl-9 col-lg-8 col-md-12 col-sm-12">
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <!-- Breadcrumbs -->
                                <div class="bredcrumb_wrap">
                                    <nav aria-label="breadcrumb">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="{{ url('/') }}"> الرئيسية </a></li>
                                            <li class="breadcrumb-item"><a href="{{ url('dashboard') }}"> حسابي </a></li>
                                            <li class="breadcrumb-item active" aria-current="page"> الرسائل</li>
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

                                        <!-- Message Content -->
                                        <div class="dash-msg-content">
                                            @livewire('chat.chatbox', ['conversation_id' => $conversation_id])
                                            <!-- Reply Area -->
                                            <div class="clearfix"></div>
                                            @livewire('chat.sendmessage', ['conversation_id' => $conversation_id])
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

</div>
