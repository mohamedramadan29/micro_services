@extends('website.layouts.master')
@section('title')
    الرسائل
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
                                        <li class="breadcrumb-item active" aria-current="page"> الرسائل </li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>
                    <div class="tickets_page">
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <!-- Convershion -->
                                <div class="messages-container margin-top-0">
                                    <div class="messages-headline d-flex">
                                        <h4> الرسائل </h4>
                                    </div>

                                    <div class="tickets">
                                        @forelse ($conversations as $conversation)
                                            @php
                                                // تحديد الشخص الآخر في المحادثة
                                                $otherUser =
                                                    $conversation->sender_id == Auth::id()
                                                        ? $conversation->receiver
                                                        : $conversation->sender;

                                            @endphp
                                            <a href="{{ url('chat-main/' . $conversation['id']) }}">
                                                <div class="ticket">
                                                    <div class="ticket_logo">
                                                        <img style="width: 50px; height:50px; border-radius:50%;" src="{{ asset('assets/uploads/users_image/' . $otherUser->image) ?? asset('assets/uploads/user.png') }}"
                                                            alt="User Image">
                                                    </div>
                                                    <div>
                                                        <h4>{{ $conversation->messages->first()->body ?? 'لا توجد رسائل بعد' }}
                                                        </h4>
                                                        <div class="ticket_details">
                                                            <p>
                                                                <i class="bi bi-clock-fill"></i>
                                                                {{ $conversation->messages->first()->created_at->diffForHumans() }}
                                                            </p>
                                                            {{-- <p>{{ $otherUser->name }}</p> --}}
                                                        </div>
                                                    </div>
                                                </div>
                                                <hr>
                                            </a>
                                        @empty
                                            <p>لا توجد محادثات.</p>
                                        @endforelse


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
