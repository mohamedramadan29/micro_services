@extends('website.layouts.master')
@section('title')
    الوظائف المتاحة
@endsection
@section('content')
    <section class="gray-bg text-right" dir="rtl">
        <!-- ============================ Hero Banner  Start================================== -->
        <div class="hero-banner bg-cover center"
            style="background:#00000057 url({{ asset('assets/website/img/jobbackground.jpg') }}) no-repeat;" data-overlay="7">
            <div class="container">
                <h1>الوظائف المتاحة</h1>
                <a href="{{ url('my/job/add') }}" class="btn btn-primary free_consult_button">
                    إضافة وظيفة جديدة <i class="fa fa-plus"></i> </a>
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
                                                value="{{ request()->input('search') }}" placeholder="ابحث عن وظيفة">
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

                    @foreach ($jobs as $job)
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="ser_110">
                                    <div class="project">
                                        <div class="row">
                                            <div class="col-9">
                                                <div class="project_data">
                                                    <h5>
                                                        <a href="{{ url('job/' . $job['id'] . '-' . $job['slug']) }}">
                                                            {{ $job->title }} </a>
                                                    </h5>
                                                    <p>
                                                        {{ Str::limit($job->description, 150, '...') }}
                                                    </p>
                                                    <div class="mb-1">
                                                        <div class="user_info">
                                                            <div>
                                                                <img
                                                                    src="{{ asset('assets/uploads/users_image/' . $job->User->image) }}">
                                                            </div>
                                                            <div>
                                                                <p> {{ $job->User->name }} </p>
                                                                <span>{{ $job->User->job_title }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="project_info_person">
                                                    <ul class="list-unstyled">
                                                        <li> <i class="bi bi-geo-alt-fill"></i>
                                                            <span style="color:var(--main-color)">
                                                                العنوان  </span> :
                                                            {{ $job->address }}
                                                        </li>
                                                        <li> <i class="bi bi-cash"></i>
                                                            <span style="color:var(--main-color)">
                                                                الراتب </span> :
                                                            {{ $job->salary }}
                                                        </li>
                                                        <li> <i class="bi bi-geo-alt-fill"></i>
                                                            <span style="color:var(--main-color)">
                                                                الموقع </span> :
                                                            {{ $job->city }}, {{ $job->country }}
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
                            {{ $jobs->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ============================ Main Section End ================================== -->
@endsection
