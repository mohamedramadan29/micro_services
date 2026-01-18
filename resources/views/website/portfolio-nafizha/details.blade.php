@extends('website.layouts.master')
@section('title')
{{ $work['title'] }}
@endsection
@section('content')
<section class="gray-bg text-right" dir="rtl">
    <div class="container">
        <div class="main_hero_section">
            <div>
                <h4> {{ $work['title'] }} </h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}"> الرئيسية </a></li>
                        <li class="breadcrumb-item"><a href="{{ url('/portfolios') }}"> الاعمال </a></li>
                        <li class="breadcrumb-item active" aria-current="page"> {{ $work['title'] }} </li>
                    </ol>
                </nav>
            </div>
            <div>
                <a class="btn btn-global-button" href="https://wa.me/+963997610723"> طلب عمل مشابهه <i
                        class="fa fa-plus"></i> </a>
            </div>
        </div>
        <div class="project_details">
            <div class="row">
                <div class="col-lg-8">
                    <div class="project-details">
                        <img style="width:100%" src=" {{ asset('assets/uploads/portfolios/' . $work['image']) }}"
                            alt="{{ $work['title'] }}">
                    </div>
                    @if($work->more_images && count($work->more_images) > 0)
                    @foreach ($work->more_images as $image)
                    <div class="project-details">
                        <img src="{{ asset('assets/uploads/portfolios/' . trim($image)) }}" alt="{{ $work->title }}"
                            class="img-fluid">
                    </div>
                    @endforeach
                    @endif
                    <div class="project-details">
                        <h4> تفاصيل العمل </h4>
                        <p>
                            {!! nl2br(e($work->description)) !!}
                        </p>
                        <a target="_blank" href="{{ $work->link }}" class="btn btn-primary"> رابط العمل  </a>
                    </div>
                    <div class="project-skills">
                        <h4> المهارات </h4>
                        <div class="skills">
                            @foreach($work->sub_categories as $sub)
                            <span class="skill-tag">{{ $sub->name }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="project-status">
                        <h3>بطاقة العمل </h3>
                        <ul>
                            <li><span>تاريخ النشر:</span> {{ $work->created_at->diffForHumans() }} </li>
                            <li><span> القســم : </span> {{ $work->Category->name }} </li>
                        </ul>
                        <div class="project_owner">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6> الادارة  </h6>


                                <a href="https://wa.me/+963997610723" class="btn btn-primary btn-sm"
                                    style="height: 30px">
                                    تواصل <i class="bi bi-send"></i> </a>


                            </div>

                        </div>
                        <hr>
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
