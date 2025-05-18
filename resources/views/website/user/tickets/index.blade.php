@extends('website.layouts.master')
@section('title')
    تذاكري
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
                                        <li class="breadcrumb-item active" aria-current="page"> تذاكري </li>
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
                                        <h4> تذاكري </h4>
                                        <a href="{{ url('ticket/create') }}" class="message-action"> تذكرة جديدة </a>
                                    </div>

                                    <div class="tickets">
                                        @forelse ($tickets as $ticket)
                                            <a href="{{ url('ticket/' . $ticket['id']) }}">
                                                <div class="ticket">
                                                    <div class="ticket_logo">
                                                        <i class="bi bi-ticket"></i>
                                                    </div>
                                                    <div>
                                                        <h4> {{ $ticket['title'] }} </h4>
                                                        <div class="ticket_details">
                                                            <p>
                                                                <i class="bi bi-ticket-fill"></i>
                                                                {{ $ticket['id'] }}
                                                            </p>
                                                            <p>
                                                                <i class="bi bi-clock-fill"></i>
                                                                {{ $ticket->created_at->diffForHumans() }}
                                                            </p>
                                                            <p>
                                                                <i class="bi bi-check-circle-fill"></i>
                                                                @if ($ticket['re-send'] == 0)
                                                                    لا يتوفر رد
                                                                @elseif($ticket['re-send'] == 1)
                                                                    يتوفر رد
                                                                @endif
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <hr>
                                            </a>
                                            @empty
                                            <div class="alert alert-info">
                                                لا يوجد تذاكر بعد
                                            </div>
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
