@extends('website.layouts.master')
@section('title')
    {{ $blog['name'] }}  - المدونة
@endsection
@section('content')
    <section class="gray-bg text-right" dir="rtl">
        <div class="container">
            <div class="main_hero_section">
                <div>
                    <h4> {{ $blog['name'] }} </h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ url('/') }}"> الرئيسية </a></li>
                            <li class="breadcrumb-item"><a href="{{ url('/categories') }}"> المدونة  </a></li>
                            <li class="breadcrumb-item"><a href="{{ url('/category/'.$blog['category']['slug']) }}"> {{ $blog['category']['name'] }}  </a></li>
                            <li class="breadcrumb-item active" aria-current="page"> {{ $blog['name'] }} </li>
                        </ol>
                    </nav>
                </div>

            </div>
            <div class="project_details">
                <div class="row">
                    <div class="col-12">
                        <div class="project-details">
                            <img style="width:100%;height:400px;object-fit: cover;" src="{{ $blog->Image() }}" alt="">
                            <p> {!! $blog['desc'] !!} </p>
                            <span style="font-size: 14px;color: #545353;"> <i  style="font-size: 12px;margin-left: 5px;" class="bi bi-calendar-check"></i>  {{ $blog['created_at']->format('Y-m-d') }} </span>
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
    </section>
    <!-- ============================ Main Section End ================================== -->
@endsection
