@extends('website.layouts.master')
@section('title')
    {{ $properity_maintain['title'] }}
@endsection
@section('content')
    <section class="gray-bg text-right" dir="rtl">
        <div class="container">
            <div class="main_hero_section">
                <div>
                    <h4> {{ $properity_maintain['title'] }} </h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#"> الرئيسية </a></li>
                            <li class="breadcrumb-item"><a href="#"> خدمات الصيانة </a></li>
                            <li class="breadcrumb-item active" aria-current="page"> {{ $properity_maintain['title'] }} </li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a class="btn btn-global-button" href="{{ url('my/property/maintain/add') }}"> اضف خدمة صيانة <i
                            class="fa fa-plus"></i> </a>
                </div>
            </div>
            <div class="project_details">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="project-details">
                            <h3> {{ $properity_maintain['title'] }} </h3>
                            <p> {{ $properity_maintain['description'] }} </p>
                        </div>
                        <div class="project-skills">
                            <h4> الخدمات المتاحة </h4>
                            <div class="skills">
                                @php
                                    $skills = explode(',', $properity_maintain['service_type']);
                                @endphp
                                @foreach ($skills as $skill)
                                    <span> {{ $skill }} </span>
                                @endforeach
                            </div>
                        </div>
                        <div class="project-skills">
                            <h4> الموقع والمساحة </h4>
                            <div class="">
                                <ul>
                                    <li class="mb-2"> <i class="bi bi-house-door-fill"
                                            style="color:var(--main-color)"></i>
                                        <span style="color:var(--main-color)">
                                            الموقع </span> :
                                        {{ $properity_maintain['location'] }}
                                    </li>
                                    <li class="mb-2"> <i class="bi bi-file-earmark-check-fill"
                                            style="color:var(--main-color)"></i>
                                        <span style="color:var(--main-color)">
                                            الدولة </span> :
                                        {{ $properity_maintain['country'] }}

                                    </li>
                                    <li class="mb-2"> <i class="bi bi-geo-alt-fill" style="color:var(--main-color)"></i>
                                        <span style="color:var(--main-color)">
                                            المدينة </span> :
                                        {{ $properity_maintain['city'] }}
                                    </li>
                                    <li class="mb-2"> <i class="bi bi-database-fill" style="color:var(--main-color)"></i>
                                        <span style="color:var(--main-color)">
                                            المساحة </span> :
                                        {{ $properity_maintain['area'] }} <span class="badge badge-danger"> م2 </span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="project-status">
                            <h3> تفاصيل الخدمة </h3>
                            <ul>
                                <li> <i class="bi bi-house-door-fill" style="color:var(--main-color)"></i>
                                    <span style="color:var(--main-color)">
                                        نوع العقار </span> :
                                    {{ $properity_maintain['category'] }}
                                </li>
                                <li> <i class="bi bi-file-earmark-check-fill" style="color:var(--main-color)"></i>
                                    <span style="color:var(--main-color)">
                                        نوع العقد </span> :
                                    {{ $properity_maintain['contract_type'] }}

                                </li>
                                <li> <i class="bi bi-geo-alt-fill" style="color:var(--main-color)"></i>
                                    <span style="color:var(--main-color)">
                                        الموقع </span> :
                                    {{ $properity_maintain['location'] }}
                                </li>
                                <li> <i class="bi bi-database-fill" style="color:var(--main-color)"></i>
                                    <span style="color:var(--main-color)">
                                        نوع
                                        الخدمة </span> :
                                    {{ $properity_maintain['service_type'] }}
                                </li>
                            </ul>
                            <div class="project_owner">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6> صاحب الخدمة </h6>
                                    @if (Auth::check())
                                        @if ($properity_maintain['user_id'] == Auth::user()->id)
                                            <form action="{{ url('conversation/start/property-maintain') }}"
                                                method="post">
                                                @csrf
                                                <input type="hidden" name="property_maintain_id"
                                                    value="{{ $properity_maintain['id'] }}">
                                                <input type="hidden" name="sender_id" value="{{ Auth::id() }}">
                                                <input type="hidden" name="receiver_id"
                                                    value="{{ $properity_maintain['user_id'] }}">
                                                {{-- <button type="submit" class="btn btn-primary btn-sm" style="height: 30px">
                                                    تواصل <i class="bi bi-send"></i> </button> --}}
                                            </form>
                                        @endif
                                    @endif

                                </div>
                                <div class="user_info">
                                    <div>
                                        @if ($properity_maintain['User']['image'] != '')
                                            <img
                                                src="{{ asset('assets/uploads/users_image/' . $properity_maintain['User']['image']) }}">
                                        @else
                                            <img src="{{ asset('assets/website/img/favicon.png') }}"
                                                class="img-fluid rounded" alt="">
                                        @endif
                                    </div>
                                    <div>
                                        <p> {{ $properity_maintain['User']['name'] }} </p>
                                        <span>
                                            {{ optional($properity_maintain->User)->job_title }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="project-status">
                            <h3> شارك </h3>
                            <!-- AddToAny BEGIN -->
                            <div class="sharing">
                                <div class="a2a_kit a2a_kit_size_32 a2a_default_style">
                                    <a class="a2a_dd" href="https://www.addtoany.com/share"></a>
                                    <a class="a2a_button_facebook"></a>
                                    <a class="a2a_button_whatsapp"></a>
                                    <a class="a2a_button_telegram"></a>
                                    <a class="a2a_button_linkedin"></a>
                                    <a class="a2a_button_threads"></a>
                                    <a class="a2a_button_x"></a>
                                    <a class="a2a_button_twitter"></a>
                                    <a class="a2a_button_snapchat"></a>
                                </div>
                                <script defer src="https://static.addtoany.com/menu/page.js"></script>
                                <!-- AddToAny END -->
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
    <!-- ============================ Main Section End ================================== -->
@endsection
