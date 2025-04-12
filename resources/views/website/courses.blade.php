@extends('website.layouts.master')
@section('title')
    {{ __('courses.courses') }}
@endsection
@section('content')
    <section class="gray-bg text-right" dir="rtl">
        <!-- ============================ Hero Banner  Start================================== -->
        <div class="hero-banner bg-cover center"
            style="background:#00000057 url({{ asset('assets/website/img/courses_background.jpg') }}) no-repeat;"
            data-overlay="7">
            <div class="container">
                <h1> {{ __('courses.courses_h1') }} </h1>
                <a href="{{ url('my/course/add') }}" class="btn btn-primary free_consult_button">
                    {{ __('courses.add_course') }} <i class="fa fa-plus"></i> </a>
            </div>
        </div>
        <!-- ============================ Hero Banner End ================================== -->

        <div class="container">
            {{-- <div class="main_hero_section">
                <div>
                    <h4> {{ __('courses.courses_h1') }} </h4>
                </div>
                <div>
                    <a class="btn btn-global-button" href="{{ url('my/course/add') }}"> {{ __('courses.add_course') }} <i
                            class="fa fa-plus"></i> </a>
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
                            <form action="{{ url('courses') }}" method="get">
                                @csrf
                                <div class="search-inner p-0">

                                    <div class="filter-search-box pb-0">
                                        <div class="form-group">
                                            <input type="text" name="search" class="form-control"
                                                value="{{ request()->input('search') }}" placeholder="  ابحث عن كورس  ">
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
                                            <div class="widget-boxed-body collapse show" id="categories"
                                                data-parent="#categories" dir="rtl">
                                                <div class="side-list no-border">
                                                    <!-- Single Filter Card -->
                                                    <div class="single_filter_card">
                                                        <div class="card-body pt-0">
                                                            <div class="inner_widget_link">
                                                                <ul class="no-ul-list filter-list">
                                                                    @foreach ($categories as $category)
                                                                        <li>
                                                                            <label for="a1"
                                                                                class="checkbox-custom-label">
                                                                                {{ $category['name'] }}
                                                                            </label>
                                                                            <ul class="no-ul-list filter-list"
                                                                                style="padding-right: 25px">
                                                                                @foreach ($category['subCategories'] as $subCat)
                                                                                    <li>
                                                                                        <input id="{{ $subCat['id'] }}"
                                                                                            value="{{ $subCat['id'] }}"
                                                                                            class="checkbox-custom"
                                                                                            name="cat_ids[]" type="checkbox"
                                                                                            {{ in_array($subCat['id'], request()->input('cat_ids', [])) ? 'checked' : '' }}>
                                                                                        <label for="{{ $subCat['id'] }}"
                                                                                            class="checkbox-custom-label">
                                                                                            {{ $subCat['name'] }}
                                                                                        </label>
                                                                                    </li>
                                                                                @endforeach
                                                                            </ul>
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
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

                    @foreach ($courses as $course)
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="ser_110">
                                    <div class="project">
                                        <div class="row">
                                            <div class="col-9">
                                                <div class="project_data">
                                                    <h5>
                                                        <a
                                                            href="{{ url('course/' . $course['id'] . '-' . $course['slug']) }}">
                                                            {{ $course['title'] }} </a>
                                                    </h5>
                                                    <p>
                                                        {{ Str::limit($course['desc'], 150, '...') }}
                                                    </p>
                                                    <div class="mb-1">
                                                        <div class="user_info">
                                                            <div>
                                                                <img
                                                                    src="{{ asset('assets/uploads/users_image/' . $course['User']['image']) }}">
                                                            </div>
                                                            <div>
                                                                <p> {{ $course['User']['name'] }} </p>
                                                                <span>
                                                                    {{ optional($course->User)->job_title }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="project_info_person">
                                                    <ul class="list-unstyled">
                                                        <li> <i class="bi bi-currency-dollar"></i>
                                                            {{ $course['price'] }}
                                                            دولار </li>
                                                        <li> <i class="bi bi-pc-display-horizontal"></i>
                                                            {{ $course['leason_numbers'] }}
                                                            محاضرة
                                                        </li>
                                                        <li> <i class="bi bi-calendar-check"></i>
                                                            {{ $course['created_at']->diffForHumans() }}
                                                        </li>
                                                        <li> <i class="bi bi-people-fill"></i>
                                                            {{ $course['current_student_num'] }} </li>
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
                            {{ $courses->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ============================ Main Section End ================================== -->
@endsection
