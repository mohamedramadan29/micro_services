@extends('website.layouts.master')
@section('title')
    تفاصيل التذكرة
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
                                        <li class="breadcrumb-item active" aria-current="page"> تفاصيل التذكرة </li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>
                    <div class="tickets_page">
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <div class="messages-container margin-top-0">
                                    <div class="messages-headline">
                                        <h4> تفاصيل التذكرة </h4>
                                    </div>

                                    <div class="messages-container-inner">
                                        <!-- Message Content -->
                                        <div class="dash-msg-content">
                                            @foreach ($messages as $message)
                                                <div
                                                    class="message-plunch {{ $message['sender_type'] == 'user' ? 'me' : '' }}">
                                                    <div class="dash-msg-avatar">
                                                        @if ($message['sender_type'] == 'user' && Auth::user()->image != '')
                                                            <img src="{{ asset('assets/uploads/users_image/' . Auth::user()->image) }}"
                                                                class="img-fluid rounded" alt="">
                                                        @else
                                                            <img src="{{ asset('assets/website/img/favicon.png') }}"
                                                                class="img-fluid rounded" alt="">
                                                        @endif
                                                    </div>

                                                    <div class="dash-msg-text">
                                                        <h6 class="user_name">
                                                            {{ $message['sender_type'] == 'user' ? Auth::user()->user_name : 'فريق دعم نفذها' }}
                                                        </h6>
                                                        <p> {{ $message['content'] }} </p>
                                                        @if ($message['file'])
                                                            <a href="{{ asset('assets/uploads/tickets/' . $message['file']) }}"
                                                                target="_blank">
                                                                <button class="btn btn-primary btn-sm mt-2">
                                                                    <i class="bi bi-file-earmark-pdf"></i> عرض الملف
                                                                </button>
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach

                                            <div class="clearfix"></div>
                                            <form method="POST" action="{{ url('message/create/' . $ticket_id) }}"
                                                enctype="multipart/form-data">
                                                @csrf
                                                <div class="message-reply">

                                                    <textarea name="message" required class="form-control with-light" placeholder=" اكتب رسالتك  "></textarea>
                                                    <div class="form-group mt-2">
                                                        <label for="file">إضافة ملف</label>
                                                        <input type="file" name="file" class="form-control"
                                                            accept="image/*">
                                                    </div>
                                                    <button type="submit" class="btn dark-2"> ارسال <i
                                                            class="bi bi-send"></i> </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

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
