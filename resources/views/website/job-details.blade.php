@extends('website.layouts.master')
@section('title')
    {{ $job['title'] }}
@endsection
@section('content')
    <section class="gray-bg text-right" dir="rtl">
        <div class="container">
            <div class="main_hero_section">
                <div>
                    <h4> {{ $job['title'] }} </h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ url('/') }}"> الرئيسية </a></li>
                            <li class="breadcrumb-item"><a href="{{ url('/jobs') }}"> الوظائف </a></li>
                            <li class="breadcrumb-item active" aria-current="page"> {{ $job['title'] }} </li>
                        </ol>
                    </nav>
                </div>
                @if (Auth::check())
                    <div>
                        <a class="btn btn-global-button" href="{{ url('my/job/add') }}"> اضف وظيفة <i
                                class="fa fa-plus"></i> </a>
                    </div>
                @endif
            </div>
            <div class="project_details">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="project-details">
                            <h3> {{ $job['title'] }} </h3>
                            <p> {{ $job['description'] }} </p>
                        </div>
                        <div class="project-skills">
                            <h4> الخبرات المطلوبة </h4>
                            @php
                                $experiences = explode(',', $job['experience']);
                            @endphp
                            <div class="">
                                <ul>
                                    @foreach ($experiences as $experience)
                                        <li style="font-size:14px"> <i style="color: var(--main-color);"
                                                class="bi bi-check"></i> {{ $experience }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        @if (Auth::check())
                            @php
                                $userOffer = $job['offers']->firstWhere('user_id', Auth::id());
                            @endphp
                            @if (!$userOffer)
                                @if (Auth::id() != $job['user_id'])
                                    <div class="project-skills">
                                        <h4>تقديم على الوظيفة</h4>
                                        <div class="apply-form">
                                            <form action="{{ url('job/offer/store', $job['id']) }}" method="POST"
                                                enctype="multipart/form-data">
                                                @csrf
                                                <input type="hidden" name="job_id" value="{{ $job['id'] }}">
                                                <div class="form-group">
                                                    <label>وصف التقديم</label>
                                                    <textarea name="offer_description" class="form-control" rows="4" required>{{ old('offer_description') }}</textarea>
                                                </div>
                                                <div class="form-group">
                                                    <label>السيرة الذاتية (اختياري)</label>
                                                    <input type="file" name="offer_file" class="form-control">
                                                </div>
                                                <button type="submit" class="btn btn-primary">تقديم الطلب</button>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div class="project-skills">
                                    <h4>تقديم على الوظيفة</h4>
                                    <div class="apply-form">
                                        <div class="alert alert-info">
                                            تم التقديم علي الوظيفة من قبل
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="project-skills">
                                <h4>تقديم على الوظيفة</h4>
                                <div class="apply-form">
                                    <a href="{{ url('login') }}" class="btn btn-primary"> سجل دخولك الان <i
                                            class="fa fa-sign-in"></i> </a>
                                </div>
                            </div>
                        @endif

                    </div>
                    <div class="col-lg-4">
                        <div class="project-status">
                            <h3> معلومات الوظيفة </h3>
                            <ul>
                                @if ($job['salary'] != null)
                                    <li class="mb-2"> <i class="bi bi-cash" style="color:var(--main-color)"></i>
                                        <span style="color:var(--main-color)">الراتب</span> :
                                        {{ $job['salary'] }} $
                                    </li>
                                @endif
                                <li class="mb-2"> <i class="bi bi-geo-alt-fill" style="color:var(--main-color)"></i>
                                    <span style="color:var(--main-color)"> العنوان </span> :
                                    {{ $job['address'] }}
                                </li>
                                <li class="mb-2"> <i class="bi bi-geo-alt-fill" style="color:var(--main-color)"></i>
                                    <span style="color:var(--main-color)">المدينة</span> :
                                    {{ $job['city'] }}
                                </li>
                                <li class="mb-2"> <i class="bi bi-globe" style="color:var(--main-color)"></i>
                                    <span style="color:var(--main-color)">الدولة</span> :
                                    {{ $job['country'] }}
                                </li>

                                <li class="mb-2"> <i class="bi bi-person-fill" style="color:var(--main-color)"></i>
                                    <span style="color:var(--main-color)">الجنس</span> :
                                    {{ $job['sex'] }}
                                </li>
                                <li> <i class="bi bi-calendar" style="color:var(--main-color)"></i>
                                    <span style="color:var(--main-color)">
                                        تاريخ النشر </span> :
                                    {{ date('Y-m-d', strtotime($job['created_at'])) }}
                                </li>

                            </ul>

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
