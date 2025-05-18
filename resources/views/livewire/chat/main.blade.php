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

                            @include('website.layouts.dashboard-sidebar')

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
                                        <div class="dash-msg-content d-flex flex-column gap-4">
                                            @if ($service)
                                                <!-- تفاصيل الخدمة المرتبطة بالمحادثة -->
                                                <div class="d-flex flex-column gap-2">
                                                    <div class="d-flex flex-row gap-2">
                                                        <h4> {{ $service->name }} </h4>
                                                        <span class="text-success">سعر الخدمة: {{ $service->price }}
                                                            $</span>
                                                        @if ($service->user_id != Auth::id())
                                                            <button type="button" class="btn btn-primary btn-sm ms-auto"
                                                                data-bs-toggle="modal" data-bs-target="#buyServiceModal">
                                                                شراء الخدمة الان
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
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
        <!-- Modal -->
        <div class="modal fade buy_services_model" id="buyServiceModal" tabindex="-1"
            aria-labelledby="buyServiceModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header d-flex flex-row gap-2 justify-content-between align-items-center">
                        <h1 class="modal-title fs-5" id="buyServiceModalLabel">شراء الخدمة</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <ul>
                            <li> منصة نفذها تضمن حقوقك بنسبة 100% .</li>
                            <li> لا تتردد ابداً في التواصل معنا إذا
                                احتجت أي مساعدة وسنسعد بخدمتك.</li>
                            <li> يمكنك التواصل مع مقدم الخدمة إذا احتجت
                                أي استفسار قبل طلب الخدمة.</li>
                        </ul>
                    </div>
                    <div class="modal-footer">
                        <button type="button" style="background-color: #404040;" class="btn btn-secondary"
                            data-bs-dismiss="modal">رجوع</button>
                        @if (Auth::check())
                            <form method="post" action="{{ url('create_order') }}">
                                @csrf
                                <input type="hidden" name="service_id" value="{{ $service->id }}">
                                <input type="hidden" name="service_name" value="{{ $service->name }}">
                                <input type="hidden" name="service_price" value="{{ $service->price }}">
                                <input type="hidden" name="qty" value="1">
                                <input type="hidden" name="user_serv" value="{{ $service->user_id }}">
                                <button type="submit" class="btn btn-primary">شراء الخدمة
                                    الان <i class="bi bi-cart"></i></button>
                            </form>
                        @else
                            <a href="{{ url('login') }}" type="button" class="btn btn-primary">شراء الخدمة الان
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <!-- ============================ Main Section End ================================== -->
    @endsection

</div>
