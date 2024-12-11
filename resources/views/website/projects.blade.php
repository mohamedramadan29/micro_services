@extends('website.layouts.master')
@section('title')
    المشاريع
@endsection
@section('content')
    <section class="gray-bg text-right" dir="rtl">
        <div class="container">
            <div class="main_hero_section">
                <div>
                    <h4> المشاريع المعروضة </h4>
                </div>
                <div>
                    <a class="btn btn-global-button" href="{{url('my/project/add')}}"> اضف مشروعك الان <i class="fa fa-plus"></i> </a>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4 col-md-12 col-sm-12">
                    <div class="simple-sidebar sm-sidebar">
                        <div class="search-sidebar_header">
                            <h4 class="ssh_heading"> البحث  </h4>
                        </div>
                        <!-- Find New Property -->
                        <div class="sidebar-widgets collapse miz_show" id="search_open" data-parent="#search_open">
                            <form action="{{ url('services') }}" method="get">
                                @csrf
                                <div class="search-inner p-0">

                                    <div class="filter-search-box pb-0">
                                        <div class="form-group">
                                            <input type="text" name="search" class="form-control"
                                                value="{{ request()->input('search') }}" placeholder=" ابحث عن مشروع  ... ">
                                        </div>
                                    </div>
                                    <div class="filter_wraps">
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
                                    </div>
                                    <div class="form-group filter_button pt-2">
                                        <button type="submit" class="btn btn-primary btn btn-theme-2 rounded full-width">
                                            بحث
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

                    @foreach ($projects as $project)
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="ser_110">
                                    <div class="project">
                                        <div class="row">
                                            <div class="col-9">
                                                <div class="project_data">
                                                    <h5>
                                                        <a
                                                            href="{{ url('project/' . $project['id'] . '-' . $project['slug']) }}">
                                                            {{ $project['title'] }} </a>
                                                    </h5>
                                                    <p>
                                                        {{ Str::limit($project['desc'], 150, '...') }}
                                                    </p>
                                                    <div class="mb-1">
                                                        <div class="user_info">
                                                            <div>
                                                                <img
                                                                    src="{{ asset('assets/uploads/users_image/' . $project['User']['image']) }}">
                                                            </div>
                                                            <div>
                                                                <p> {{ $project['User']['name'] }} </p>
                                                                <span>
                                                                    {{ optional($project->User)->job_title }}
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
                                                            {{ $project['price'] }}
                                                            دولار </li>
                                                        <li> <i class="bi bi-journal-code"></i>
                                                            {{ $project['day_number'] }}
                                                            ايام
                                                        </li>
                                                        <li> <i class="bi bi-calendar-check"></i>
                                                            {{ $project['created_at']->diffForHumans() }}
                                                        </li>
                                                        <li> <i class="bi bi-patch-check"></i>
                                                            {{ $project['status'] }} </li>
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
                            {{ $projects->links() }}
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>
    <!-- ============================ Main Section End ================================== -->

    <!-- ============================ Call To Action Start ================================== -->
    <section class="call-to-act"
        style="background:#0b85ec url({{ asset('assets/website/img/landing-bg.png') }}) no-repeat">
        <div class="container">
            <div class="row justify-content-center">

                <div class="col-lg-7 col-md-8">
                    <div class="clt-caption text-center mb-4">
                        <h2 class="text-light"> هل أنت جاهز لبدء مشروعك الخاص ؟ </h2>
                    </div>
                    <div class="inner-flexible-box subscribe-box">
                        <div class="input-group">
                            <button class="btn btn-primary start_job"> ابدا الان</button>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ============================ Call To Action End ================================== -->
@endsection
