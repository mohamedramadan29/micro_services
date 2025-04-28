@extends('website.layouts.master')
@section('title')
    خدمات الصيانة
@endsection
@section('content')
    <section class="gray-bg text-right" dir="rtl">
           <!-- ============================ Hero Banner  Start================================== -->
           <div class="hero-banner bg-cover center"
           style="background:#00000057 url({{ asset('assets/website/img/maintain.jpg') }}) no-repeat;"
           data-overlay="7">
           <div class="container">
               <h1> خدمات الصيانة  </h1>
               <a href="{{ url('my/property/maintain/add') }}" class="btn btn-primary free_consult_button">
                  اض خدمة صيانة  <i class="fa fa-plus"></i> </a>
                  <form class="mt-4" dir="rtl" method="get" action="{{ url('properties/maintain') }}">
                    <div class="row justify-content-center">
                        <div class="col-lg-8 col-md-10 col-sm-12">
                            <div class="banner-search style-2">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control lio-rad"
                                        placeholder="بحث عن خدمة صيانة  " value="{{ request()->input('search') }}">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn bt-round btn--2"> بحث <i
                                                class="ti-search"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
           </div>
       </div>
       <br>
       <!-- ============================ Hero Banner End ================================== -->
        <div class="container">
            {{-- <div class="main_hero_section">
                <div>
                    <h4> خدمات الصيانة </h4>
                </div>
                <div>
                    <a class="btn btn-global-button" href="{{ url('my/property/maintain/add') }}"> اضافة خدمة صيانة
                        <i class="fa fa-plus"></i> </a>
                </div>
            </div> --}}
            <div class="row">
                <div class="col-lg-4 col-md-12 col-sm-12">
                    <div class="simple-sidebar sm-sidebar">
                        <div class="search-sidebar_header">
                            <h4 class="ssh_heading"> البحث </h4>
                        </div>
                        <!-- Find New Property -->
                        <div class="sidebar-widgets collapse miz_show" id="search_open" data-parent="#search_open">
                            <form action="{{ url('properties/maintain') }}" method="get">
                                @csrf
                                <div class="search-inner p-0">

                                    <div class="filter-search-box pb-0">
                                        <div class="form-group">
                                            <input type="text" name="search" class="form-control"
                                                value="{{ request()->input('search') }}"
                                                placeholder="  ابحث عن خدمة صيانة  ">
                                        </div>
                                    </div>
                                    {{-- <div class="filter_wraps">
                                        <!-- Job categories Search -->
                                        <div class="single_search_boxed">
                                            <div class="widget-boxed-header">
                                                <h4>
                                                    <a href="#categories" data-toggle="collapse" aria-expanded="true"
                                                        role="button"> حدد القسم </a>
                                                </h4>
                                            </div>

                                        </div>
                                    </div> --}}
                                    <div class="form-group filter_button pt-2">
                                        <button type="submit" class="btn btn-primary btn btn-theme-2 rounded full-width">
                                            {{ __('courses.search') }}
                                        </button>
                                    </div>
                                </div>


                            </form>


                        </div>


                    </div>
                    <!-- Sidebar End -->

                </div>

                <!-- Item Wrap Start -->
                <div class="col-lg-8 col-md-12 col-sm-12 project_page" style="margin-top: 30px">

                    @foreach ($properity_maintains as $properity_maintain)
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="ser_110">
                                    <div class="project">
                                        <div class="row">
                                            <div class="col-9">
                                                <div class="project_data">
                                                    <h5>
                                                        <a
                                                            href="{{ url('properties/maintain/' . $properity_maintain['id'] . '-' . $properity_maintain['slug']) }}">
                                                            {{ $properity_maintain['title'] }} </a>
                                                    </h5>
                                                    <p>
                                                        {{ Str::limit($properity_maintain['description'], 150, '...') }}
                                                    </p>
                                                    <div class="mb-1">
                                                        <div class="user_info">
                                                            <div>
                                                                <img
                                                                    src="{{ asset('assets/uploads/users_image/' . $properity_maintain['User']['image']) }}">
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
                                            </div>
                                            <div class="col-3">
                                                <div class="project_info_person">
                                                    <ul class="list-unstyled">
                                                        <li> <i class="bi bi-house-door-fill"></i>
                                                            <span style="color:var(--main-color)">
                                                                نوع العقار </span> :
                                                            {{ $properity_maintain['category'] }}
                                                        </li>
                                                        <li> <i class="bi bi-file-earmark-check-fill"></i>
                                                            <span style="color:var(--main-color)">
                                                                نوع العقد </span> :
                                                            {{ $properity_maintain['contract_type'] }}
                                                        </li>
                                                        <li> <i class="bi bi-geo-alt-fill"></i>
                                                            <span style="color:var(--main-color)">
                                                                الموقع </span> :
                                                            {{ $properity_maintain['location'] }}
                                                        </li>
                                                        <li> <i class="bi bi-database-fill"></i>
                                                            <span style="color:var(--main-color)">
                                                                نوع
                                                                الخدمة </span> :
                                                            {{ $properity_maintain['service_type'] }}
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>


                                    </div>

                                </div>
                            </div>
                        </div>
                    @endforeach



                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            {{ $properity_maintains->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ============================ Main Section End ================================== -->
@endsection
