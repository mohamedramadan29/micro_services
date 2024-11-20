@extends('website.layouts.master')
@section('title')
    مشاريعي
@endsection
@section('content')
    <!-- ============================ Page Title Start================================== -->
    <div class="page-title bg-cover" style="background:url({{ asset('assets/website/img/bn-1.jpg') }})no-repeat;"
        data-overlay="5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12"></div>
            </div>
        </div>
    </div>
    <!-- ============================ Page Title End ================================== -->
    @if (Session::has('Success_message'))
        @php
            toastify()->success(\Illuminate\Support\Facades\Session::get('Success_message'));
        @endphp
    @endif
    @if ($errors->any())
        @foreach ($errors->all() as $error)
            @php
                toastify()->error($error);
            @endphp
        @endforeach
    @endif
    <!-- ============================ Main Section Start ================================== -->
    <section class="gray-bg pt-4 text-right" dir="rtl">
        <div class="container-fluid">
            <div class="row m-0">

                <div class="col-xl-3 col-lg-4 col-md-12 col-sm-12">
                    <div class="dashboard-navbar overlio-top">

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

                        <div class="d-navigation">
                            <ul id="metismenu">
                                <li><a href="{{ url('dashboard') }}"><i class="ti-dashboard"></i> الملف الشخصي </a>
                                </li>
                                <li><a href="{{ url('project/index') }}"><i class="ti-user"></i> المشاريع </a></li>
                                <li><a href="{{ url('project/add') }}"><i class="ti-plus"></i> اضف مشروع جديد </a></li>
                                <li><a href="{{ url('service/index') }}"><i class="ti-user"></i> الخدمات </a></li>
                                <li><a href="{{ url('service/add') }}"><i class="ti-plus"></i> اضف خدمة جديدة </a></li>
                                <li><a href="{{ url('chat-main') }}"><i class="ti-email"></i> المحادثات </a></li>
                                <li><a href="{{ url('reviews') }}"><i class="ti-email"></i> التقيمات </a></li>
                                <li><a href="{{ url('update-account') }}"><i class="ti-email"></i> تعديل الملف الشخصي
                                    </a></li>
                                <li><a href="{{ url('balance') }}"><i class="ti-email"></i> الرصيد </a></li>
                                <li><a href="{{ url('logout') }}"><i class="ti-power-off"></i> تسجيل خروج </a></li>
                            </ul>
                        </div>

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
                                        <li class="breadcrumb-item active" aria-current="page"> مشاريعي </li>
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
                                            <h4><i class="ti-lock mr-1"></i> مشاريعي </h4>
                                        </div>
                                    </div>

                                    <div class="_dashboard_content_body project_page">
                                        <div class="row">
                                            <!-- Single Item -->
                                            @if ($projects->count() > 0)
                                                @foreach ($projects as $project)
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
                                                                            @if ($project['approved'] == 0)
                                                                                <div class="mb-1">
                                                                                    <div class="buttons"
                                                                                        style="padding:10px">
                                                                                        <a href="{{ url('project/update/' . $project['id']) }}"
                                                                                            class="btn btn-primary btn-sm">
                                                                                            تعديل <i class="fa fa-edit"></i>
                                                                                        </a>
                                                                                        <a href="{{ url('project/delete/' . $project['id']) }}"
                                                                                            class="btn btn-warning btn-sm"
                                                                                            onclick="return confirm('هل أنت متأكد أنك تريد حذف هذا العنصر؟')">
                                                                                            حذف <i class="fa fa-trash"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                            @endif
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
                                                                                    {{ $project['created_at'] }}
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
                                                @endforeach
                                            @else
                                                <div class="alert alert-info">
                                                    لا يوجد لديك مشاريع في الوقت الحالي
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
