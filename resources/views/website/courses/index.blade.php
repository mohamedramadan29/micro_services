@extends('website.layouts.master')
@section('title')
    كورساتي
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
                <div class="col-xl-9 col-lg-8 col-md-12 col-sm-12">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <!-- Breadcrumbs -->
                            <div class="bredcrumb_wrap">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{ url('/') }}"> الرئيسية </a></li>
                                        <li class="breadcrumb-item active" aria-current="page"> كورساتي </li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">

                            <!-- Single Wrap -->
                            <div class="_dashboard_content">
                                <!-- Single Wrap -->
                                <div class="_dashboard_content">
                                    <div class="_dashboard_content_header">
                                        <div class="_dashboard__header_flex">
                                            <h4><i class="ti-lock mr-1"></i> كورساتي </h4>
                                        </div>
                                    </div>

                                    <div class="_dashboard_content_body project_page">
                                        <div class="row">
                                            <!-- Single Item -->
                                            @if ($courses->count() > 0)
                                                @foreach ($courses as $course)
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
                                                                                <div class="buttons" style="padding:10px">
                                                                                    <a href="{{ url('my/course/update/' . $course['id']) }}"
                                                                                        class="btn btn-primary btn-sm">
                                                                                        تعديل <i class="fa fa-edit"></i>
                                                                                    </a>
                                                                                    <a href="{{ url('my/course/delete/' . $course['id']) }}"
                                                                                        class="btn btn-warning btn-sm"
                                                                                        onclick="return confirm('هل أنت متأكد أنك تريد حذف هذا العنصر؟')">
                                                                                        حذف <i class="fa fa-trash"></i>
                                                                                    </a>
                                                                                </div>
                                                                            </div>

                                                                            <div class="mb-1">
                                                                                <div class="buttons" style="padding:10px">
                                                                                    <a href="{{ url('my/course/subscriptions/' . $course['id']) }}"
                                                                                        class="btn btn-primary btn-sm">
                                                                                        المشتركين <i
                                                                                            class="bi bi-people-fill"></i>
                                                                                    </a>

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
                                                                                <li> <i
                                                                                        class="bi bi-pc-display-horizontal"></i>
                                                                                    {{ $course['leason_numbers'] }}
                                                                                    محاضرة
                                                                                </li>
                                                                                <li> <i class="bi bi-calendar-check"></i>
                                                                                    {{ $course['created_at']->diffForHumans() }}
                                                                                </li>
                                                                                <li> <i class="bi bi-people-fill"></i>
                                                                                    {{ $course['current_student_num'] }}
                                                                                </li>
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                </div>


                                                            </div>

                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="alert alert-info">
                                                    لا يوجد لديك كورسات في الوقت الحالي
                                                </div>
                                            @endif
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
