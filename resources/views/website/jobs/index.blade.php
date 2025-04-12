@extends('website.layouts.master')
@section('title')
    وظائفي
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


                        <div class="d-navigation">
                            <ul id="metismenu">
                                <li><a href="{{ url('dashboard') }}"><i class="ti-dashboard"></i> الملف الشخصي </a>
                                </li>
                                <li><a href="{{ url('balance') }}"><i class="bi bi-credit-card"></i> الرصيد </a></li>
                                <li><a href="{{ url('my/project/index') }}"><i class="bi bi-cast"></i> المشاريع </a></li>
                                <li><a href="{{ url('my/project/add') }}"><i class="ti-plus"></i> اضف مشروع جديد </a></li>
                                <li><a href="{{ url('my/courses') }}"> <i class="bi bi-mortarboard-fill"></i> الكورسات </a>
                                </li>
                                <li><a href="{{ url('my/course/add') }}"><i class="ti-plus"></i> اضف كورس جديد </a></li>
                                <li><a href="{{ url('service/index') }}"><i class="bi bi-database-fill-check"></i> الخدمات
                                    </a>
                                </li>
                                <li><a href="{{ url('my/course/add') }}"><i class="ti-plus"></i> اضف كورس جديد </a></li>
                                <li>
                                    <a href="{{ url('my/properties/index') }}"><i class="bi bi-building"></i> العقارات
                                    </a>
                                </li>
                                <li><a href="{{ url('my/property/add') }}"><i class="ti-plus"></i> اضف عقار جديد </a></li>
                                <li><a href="{{ url('my/property/maintain/add') }}"><i class="ti-plus"></i> اضف خدمة صيانة
                                        للعقارات </a></li>
                                <li><a href="{{ url('my/property/maintain/index') }}"><i class="ti-plus"></i> خدمات الصيانة
                                    </a></li>
                                <li><a href="{{ url('my/job/add') }}"><i class="ti-plus"></i> اضافة وظيفة جديدة
                                    </a></li>
                                <li><a href="{{ url('my/jobs') }}"><i class="ti-plus"></i> وظائفي
                                    </a></li>
                                <li><a href="{{ url('chats') }}"> <i class="bi bi-chat-dots-fill"></i> المحادثات </a>
                                </li>
                                <li><a href="{{ url('tickets') }}"><i class="bi bi-ticket"></i> تذاكري </a></li>
                                {{-- <li><a href="{{ url('reviews') }}"><i class="ti-email"></i> التقيمات </a></li> --}}
                                <li><a href="{{ url('update-account') }}"> <i class="bi bi-gear-fill"></i> تعديل الملف
                                        الشخصي
                                    </a></li>
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
                                        <li class="breadcrumb-item active" aria-current="page"> وظائفي </li>
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
                                    <div class="_dashboard_content_header d-flex justify-content-between">
                                        <div class="_dashboard__header_flex">
                                            <h4><i class="ti-lock mr-1"></i> وظائفي </h4>
                                        </div>
                                        <a href="{{ url('my/job/add') }}" class="btn btn-primary btn-sm"> اضافة وظيفة
                                            <i class="bi bi-plus"></i> </a>
                                    </div>

                                    <div class="_dashboard_content_body project_page">
                                        <div class="row">
                                            <!-- Single Item -->
                                            @if ($jobs->count() > 0)
                                                @foreach ($jobs as $job)
                                                    <div class="col-lg-12">
                                                        <div class="ser_110">
                                                            <div class="project">
                                                                <div class="row">
                                                                    <div class="col-12">
                                                                        <div class="project_data">
                                                                            <h5>
                                                                                <a
                                                                                    href="{{ url('job/' . $job['id'] . '-' . $job['slug']) }}">
                                                                                    {{ $job['title'] }}
                                                                                    @if ($job['status'] == 0)
                                                                                        <span class="badge badge-danger">
                                                                                            تحت المراجعه </span>
                                                                                    @elseif($job['status'] == 1)
                                                                                        <span class="badge badge-success">
                                                                                            مفعل </span>
                                                                                    @endif
                                                                                </a>
                                                                            </h5>
                                                                            <p>
                                                                                {{ Str::limit($job['description'], 150, '...') }}
                                                                            </p>

                                                                            <div class="mb-1">
                                                                                <div class="buttons" style="padding:10px">
                                                                                    <a href="{{ url('my/job/subscriptions/' . $job['id']) }}"
                                                                                        class="btn btn-info btn-sm">
                                                                                        المتقدمين للوظيفة <i
                                                                                            class="bi bi-people-fill"></i>
                                                                                    </a>
                                                                                    <a href="{{ url('my/job/update/' . $job['id']) }}"
                                                                                        class="btn btn-primary btn-sm">
                                                                                        تعديل <i class="fa fa-edit"></i>
                                                                                    </a>
                                                                                    <a href="{{ url('my/job/delete/' . $job['id']) }}"
                                                                                        class="btn btn-warning btn-sm"
                                                                                        onclick="return confirm('هل أنت متأكد أنك تريد حذف هذا العنصر؟')">
                                                                                        حذف <i class="fa fa-trash"></i>
                                                                                    </a>
                                                                                </div>
                                                                            </div>

                                                                        </div>
                                                                    </div>

                                                                </div>


                                                            </div>

                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="alert alert-info">
                                                    لا يوجد لديك وظائف في الوقت الحالي
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
